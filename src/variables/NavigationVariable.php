<?php
namespace verbb\navigation\variables;

use verbb\navigation\Navigation;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Node as NodeElement;

use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\web\View;

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

    public function getActiveNode($criteria = null, $includeChildren = false)
    {
        $nodes = $this->nodes($criteria)->all();

        foreach ($nodes as $node) {
            if ($node->getActive($includeChildren)) {
                return $node;
            }
        }

        return null;
    }

    public function nodes($criteria = null): ElementQueryInterface
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

    public function render($criteria = null, array $options = []): void
    {
        $nodes = $this->nodes($criteria)->all();

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        echo Craft::$app->view->renderTemplate('navigation/_special/render', [
            'nodes' => $nodes,
            'options' => $options,
        ]);

        Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
    }

    public function breadcrumbs(): array
    {
        return Navigation::$plugin->getBreadcrumbs()->getBreadcrumbs();
    }

    public function tree($criteria = null): array
    {
        $nodes = $this->nodes($criteria)->level(1)->all();

        $nodeTree = [];

        Navigation::$plugin->getNavs()->buildNavTree($nodes, $nodeTree);

        return $nodeTree;
    }

    public function getNavById($id)
    {
        return Navigation::$plugin->getNavs()->getNavById($id);
    }

    public function getNavByHandle($handle)
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
