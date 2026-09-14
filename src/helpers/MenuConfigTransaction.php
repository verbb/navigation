<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;

use Craft;

use Throwable;

/** Keep deferred project configuration in sync with rolled-back menu content writes. */
class MenuConfigTransaction
{
    // Static Methods
    // =========================================================================

    public static function run(callable $callback): mixed
    {
        $config = Craft::$app->getProjectConfig();
        $before = $config->get('navigation');
        $timestamp = $config->get('dateModified');
        $writeYaml = $config->writeYamlAutomatically;
        $config->writeYamlAutomatically = false;

        try {
            return Craft::$app->getDb()->transaction($callback);
        } catch (Throwable $e) {
            $muteEvents = $config->muteEvents;
            $config->muteEvents = true;
            try {
                $config->set('navigation', $before, null, false);
                $config->set('dateModified', $timestamp, null, false);
            } finally {
                $config->muteEvents = $muteEvents;
            }
            Navigation::$plugin->getMenus()->resetCache();
            throw $e;
        } finally {
            $config->writeYamlAutomatically = $writeYaml;
        }
    }
}
