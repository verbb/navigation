<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\models\ProjectedNode;

use craft\base\Component;
use craft\helpers\Html;

class MenuBreadcrumbs extends Component
{
    // Public Methods
    // =========================================================================

    public function getBreadcrumbs(string $menuHandle): array
    {
        $context = Navigation::$plugin->getContextResolver()->resolve($menuHandle);
        $current = $context->current();

        if (!$current) {
            return [];
        }

        // Reuse context ancestors so Dynamic projected pages sit in the trail after their parent node.
        $trail = $context->ancestors();
        $trail[] = $current;

        $breadcrumbs = [];

        foreach ($trail as $node) {
            $title = trim((string)$node) !== '' ? (string)$node : (string)($node->title ?? '');
            $url = $node->getUrl();
            $isCurrent = $node->getCurrent();

            $breadcrumbs[] = [
                'title' => $title,
                'url' => $url,
                'node' => $node,
                'current' => $isCurrent,
                'isProjected' => $node instanceof ProjectedNode,
                'link' => $url ? Html::tag('a', $title, ['href' => $url]) : Html::tag('span', $title),
            ];
        }

        return $breadcrumbs;
    }
}
