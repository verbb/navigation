<?php
namespace verbb\navigation\base;

use verbb\navigation\Navigation;
use verbb\navigation\services\Breadcrumbs;
use verbb\navigation\services\Elements;
use verbb\navigation\services\Navs;
use verbb\navigation\services\Nodes;
use verbb\navigation\services\NodeTypes;

use Craft;
use craft\log\FileTarget;

use yii\log\Logger;

use verbb\base\BaseHelper;

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

    public function getNavs()
    {
        return $this->get('navs');
    }

    public function getNodes()
    {
        return $this->get('nodes');
    }

    public function getNodeTypes()
    {
        return $this->get('nodeTypes');
    }

    public static function log($message)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_INFO, 'navigation');
    }

    public static function error($message)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, 'navigation');
    }


    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'breadcrumbs' => Breadcrumbs::class,
            'elements' => Elements::class,
            'navs' => Navs::class,
            'nodes' => Nodes::class,
            'nodeTypes' => NodeTypes::class,
        ]);

        BaseHelper::registerModule();
    }

    private function _setLogging()
    {
        BaseHelper::setFileLogging('navigation');
    }

}