<?php
namespace verbb\navigation\deprecations;

use verbb\navigation\events\MenuEvent;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\services\Menus;

use Craft;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;

use yii\base\Event;

class LegacyBootstrap
{
    // Public Methods
    // =========================================================================

    public static function init(): void
    {
        self::_registerClassAliases();
        self::_registerCpRoutes();
    }

    // Private Methods
    // =========================================================================

    private static function _registerClassAliases(): void
    {
        // Deprecated in 4.0.0
        if (!class_exists('verbb\\navigation\\models\\Nav', false)) {
            class_alias(MenuSettings::class, 'verbb\\navigation\\models\\Nav');
        }

        // Deprecated in 4.0.0
        if (!class_exists('verbb\\navigation\\services\\Navs', false)) {
            class_alias(Menus::class, 'verbb\\navigation\\services\\Navs');
        }

        // Deprecated in 4.0.0
        if (!class_exists('verbb\\navigation\\events\\NavEvent', false)) {
            class_alias(MenuEvent::class, 'verbb\\navigation\\events\\NavEvent');
        }
    }

    private static function _registerCpRoutes(): void
    {
        // Deprecated in 4.0.0
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'navigation/navs' => 'navigation/menus/index',
                'navigation/navs/new' => 'navigation/menus/edit-menu',
                'navigation/navs/edit/<navId:\d+>' => 'navigation/menus/edit-menu',
                'navigation/navs/build/<navId:\d+>' => 'navigation/menus/build-menu',
            ]);
        });
    }
}
