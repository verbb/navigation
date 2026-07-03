<?php
namespace verbb\navigation\base;

use verbb\navigation\migrations\plugins\BasePluginMigrator;
use verbb\navigation\Navigation;
use verbb\navigation\deprecations\PluginTraitDeprecations;
use verbb\navigation\helpers\Plugin as NavigationPluginHelper;
use verbb\navigation\services\ActiveMatcher;
use verbb\navigation\services\Breadcrumbs;
use verbb\navigation\services\BuilderState;
use verbb\navigation\services\BuildSessions;
use verbb\navigation\services\ContextResolver;
use verbb\navigation\services\DynamicSources;
use verbb\navigation\services\Elements;
use verbb\navigation\services\MenuBreadcrumbs;
use verbb\navigation\services\Menus;
use verbb\navigation\services\Migrations;
use verbb\navigation\services\NavigationCache;
use verbb\navigation\services\NodeRead;
use verbb\navigation\services\Nodes;
use verbb\navigation\services\NodeSites;
use verbb\navigation\services\NodeTypes;

use Craft;

use verbb\base\LogTrait;
use verbb\base\helpers\Plugin;

trait PluginTrait
{
    // Static Methods
    // =========================================================================

    public static function config(): array
    {
        Plugin::bootstrapPlugin('navigation');

        return [
            'components' => [
                'activeMatcher' => ActiveMatcher::class,
                'builderState' => BuilderState::class,
                'buildSessions' => BuildSessions::class,
                'breadcrumbs' => Breadcrumbs::class,
                'contextResolver' => ContextResolver::class,
                'elements' => Elements::class,
                'menuBreadcrumbs' => MenuBreadcrumbs::class,
                'navigationCache' => NavigationCache::class,
                'dynamicSources' => DynamicSources::class,
                'menus' => Menus::class,
                'migrations' => Migrations::class,
                'nodeRead' => NodeRead::class,
                'nodeSites' => NodeSites::class,
                'nodes' => Nodes::class,
                'nodeTypes' => NodeTypes::class,
            ],
        ];
    }


    // Traits
    // =========================================================================

    use LogTrait;
    use PluginTraitDeprecations;


    // Properties
    // =========================================================================

    public static ?Navigation $plugin = null;


    // Public Methods
    // =========================================================================

    public function getActiveMatcher(): ActiveMatcher
    {
        return $this->get('activeMatcher');
    }

    public function getBreadcrumbs(): Breadcrumbs
    {
        return $this->get('breadcrumbs');
    }

    public function registerCpAssets(): void
    {
        NavigationPluginHelper::registerCpAssets();
    }

    public function registerCpBuilderAssets(): void
    {
        NavigationPluginHelper::registerCpBuilderAssets();
    }

    public function getBuildSessions(): BuildSessions
    {
        return $this->get('buildSessions');
    }

    public function getBuilderState(): BuilderState
    {
        return $this->get('builderState');
    }

    public function getContextResolver(): ContextResolver
    {
        return $this->get('contextResolver');
    }

    public function getMenuBreadcrumbs(): MenuBreadcrumbs
    {
        return $this->get('menuBreadcrumbs');
    }

    public function getElements(): Elements
    {
        return $this->get('elements');
    }

    public function getNavigationCache(): NavigationCache
    {
        return $this->get('navigationCache');
    }

    public function getDynamicSources(): DynamicSources
    {
        return $this->get('dynamicSources');
    }

    public function getMenus(): Menus
    {
        return $this->get('menus');
    }

    public function getMigrations(): Migrations
    {
        return $this->get('migrations');
    }

    public function createMigrator(string $class, array $config = []): BasePluginMigrator
    {
        /* @var BasePluginMigrator $migrator */
        $migrator = Craft::createObject(array_merge(['class' => $class], $config));

        return $migrator;
    }

    public function getNodeRead(): NodeRead
    {
        return $this->get('nodeRead');
    }

    public function getNodeSites(): NodeSites
    {
        return $this->get('nodeSites');
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
