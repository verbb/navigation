<?php
namespace verbb\navigation\base;

use verbb\navigation\Navigation;
use verbb\navigation\services\Breadcrumbs;
use verbb\navigation\services\Elements;
use verbb\navigation\services\Navs;
use verbb\navigation\services\Nodes;

use Craft;
use craft\log\FileTarget;

use yii\log\Logger;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    public static $plugin;


    // Public Methods
    // =========================================================================

    public function getBreadcrumbs()
    {
        return $this->get('breadcrumbs');
    }

    public function getElements()
    {
        return $this->get('elements');
    }

    public function getNodes()
    {
        return $this->get('nodes');
    }

    public function getNavs()
    {
        return $this->get('navs');
    }

    private function _setPluginComponents()
    {
        $this->setComponents([
            'breadcrumbs' => Breadcrumbs::class,
            'elements' => Elements::class,
            'navs' => Navs::class,
            'nodes' => Nodes::class,
        ]);
    }

    private function _setLogging()
    {
        Craft::getLogger()->dispatcher->targets[] = new FileTarget([
            'logFile' => Craft::getAlias('@storage/logs/navigation.log'),
            'categories' => ['navigation'],
        ]);
    }

    public static function log($message)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_INFO, 'navigation');
    }

    public static function error($message)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, 'navigation');
    }

}