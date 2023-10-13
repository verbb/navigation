<?php
namespace verbb\navigation\base;

use verbb\navigation\Navigation;
use verbb\navigation\services\Breadcrumbs;
use verbb\navigation\services\Elements;
use verbb\navigation\services\Navs;
use verbb\navigation\services\Nodes;
use verbb\navigation\services\NodeTypes;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;

trait PluginTrait
{
    // Properties
    // =========================================================================

    public static ?Navigation $plugin = null;


    // Traits
    // =========================================================================

    use LogTrait;
    

    // Static Methods
    // =========================================================================

    public static function config(): array
    {
        Plugin::bootstrapPlugin('navigation');

        return [
            'components' => [
                'breadcrumbs' => Breadcrumbs::class,
                'elements' => Elements::class,
                'navs' => Navs::class,
                'nodes' => Nodes::class,
                'nodeTypes' => NodeTypes::class,
            ],
        ];
    }


    // Public Methods
    // =========================================================================

    public function getBreadcrumbs(): Breadcrumbs
    {
        return $this->get('breadcrumbs');
    }

    public function getElements(): Elements
    {
        return $this->get('elements');
    }

    public function getNavs(): Navs
    {
        return $this->get('navs');
    }

    public function getNodes(): Nodes
    {
        return $this->get('nodes');
    }

    public function getNodeTypes(): NodeTypes
    {
        return $this->get('nodeTypes');
    }

}