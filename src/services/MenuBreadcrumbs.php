<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;

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

        $trail = array_reverse($current->getAncestors()->all());
        $trail[] = $current;

        $breadcrumbs = [];

        foreach ($trail as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $title = (string)$node->title;
            $url = $node->getUrl();
            $isCurrent = $node->getCurrent();

            $breadcrumbs[] = [
                'title' => $title,
                'url' => $url,
                'node' => $node,
                'current' => $isCurrent,
                'link' => $url ? Html::tag('a', $title, ['href' => $url]) : Html::tag('span', $title),
            ];
        }

        return $breadcrumbs;
    }
}
