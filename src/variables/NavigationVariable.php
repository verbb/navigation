<?php
namespace verbb\navigation\variables;

use verbb\navigation\Navigation;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\Nav;

use Craft;
use craft\helpers\Template;
use craft\web\View;

use Twig\Markup;

class NavigationVariable
{
    // Public Methods
    // =========================================================================

    public function getPluginName(): string
    {
        return Navigation::$plugin->getPluginName();
    }

    public function getRegisteredElements(): array
    {
        return Navigation::$plugin->getElements()->getRegisteredElements();
    }

    public function getRegisteredNodeTypes(): array
    {
        return Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes();
    }

    public function getActiveNode($criteria = null, $includeChildren = false): ?NodeElement
    {
        $nodes = $this->nodes($criteria)->all();

        foreach ($nodes as $node) {
            if ($node->getActive($includeChildren)) {
                return $node;
            }
        }

        return null;
    }

    public function nodes($criteria = null): NodeQuery
    {
        $query = NodeElement::find();

        if ($criteria) {
            if (is_string($criteria)) {
                $criteria = ['handle' => $criteria];
            }

            Craft::configure($query, $criteria);
        }

        return $query;
    }

    public function render($criteria = null, array $options = []): Markup
    {
        $query = $this->nodes($criteria);

        // Add eager-loading in by default. Generate a map for `children.children.children.etc`
        $eagerLoadingMap = [];

        for ($i = 1; $i < 8; $i++) { 
            $eagerLoadingMap[] = rtrim(str_repeat('children.', $i), '.');
        }

        $query->with($eagerLoadingMap);

        $nodes = $query->all();

        $template = Craft::$app->getView()->renderTemplate('navigation/_special/render', [
            'nodes' => $nodes,
            'options' => $options,
        ], View::TEMPLATE_MODE_CP);

        return Template::raw($template);
    }

    public function breadcrumbs(array $options = []): array
    {
        return Navigation::$plugin->getBreadcrumbs()->getBreadcrumbs($options);
    }

    public function tree($criteria = null): array
    {
        $nodes = $this->nodes($criteria)->level(1)->all();

        $nodeTree = [];

        Navigation::$plugin->getNavs()->buildNavTree($nodes, $nodeTree);

        return $nodeTree;
    }

    public function getNavById($id): ?Nav
    {
        return Navigation::$plugin->getNavs()->getNavById($id);
    }

    public function getNavByHandle($handle): ?Nav
    {
        return Navigation::$plugin->getNavs()->getNavByHandle($handle);
    }

    public function getAllNavs(): array
    {
        return Navigation::$plugin->getNavs()->getAllNavs();
    }

    public function getBuilderTabs($nav): array
    {
        return Navigation::$plugin->getNavs()->getBuilderTabs($nav);
    }

}
