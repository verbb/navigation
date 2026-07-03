<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\DeprecationHelper;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\NavigationContext;

use Craft;
use craft\base\Component;

class ContextResolver extends Component
{
    // Public Methods
    // =========================================================================

    public function resolve(string $menuHandle, mixed $criteria = null): NavigationContext
    {
        $query = NodeElement::find()->menuHandle($menuHandle);

        if ($criteria) {
            if (is_string($criteria)) {
                $criteria = ['menuHandle' => $criteria];
            } elseif (is_array($criteria)) {
                $criteria = DeprecationHelper::normalizeNodeQueryCriteria($criteria);
            }

            Craft::configure($query, $criteria);
        }

        $query->withNodeHierarchy(true);
        $nodes = $query->all();

        $context = new NavigationContext([
            'menuHandle' => $menuHandle,
            'nodes' => $nodes,
            'current' => Navigation::$plugin->getActiveMatcher()->findActiveNode($nodes, false),
            'currentNodes' => Navigation::$plugin->getActiveMatcher()->findCurrentNodes($nodes),
            'activeNodes' => Navigation::$plugin->getActiveMatcher()->findActiveNodes($nodes),
        ]);

        return $context;
    }
}
