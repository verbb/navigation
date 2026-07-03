<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;

use craft\events\DefineElementHtmlEvent;

class BuilderUi
{
    // Public Methods
    // =========================================================================

    public static function onDefineElementChipHtml(DefineElementHtmlEvent $event): void
    {
        if ($event->context !== 'index') {
            return;
        }

        $element = $event->element;

        if (!$element instanceof Node) {
            return;
        }

        // Menu builder rows use double-click / the Edit button for the slide-out, not title links.
        $event->html = self::_stripLabelLinks($event->html);

        if ($element->getIsPendingDelete() && Navigation::$plugin->getBuildSessions()->isStagingEnabled()) {
            $event->html = self::_stripElementEditingAttributes($event->html);
        }

        $indicator = $element->getBuilderPendingStatusIndicatorHtml();

        if (!$indicator) {
            return;
        }

        $replaced = preg_replace(
            '/<span class="(?:status[^"]*|icon)[^"]*"[^>]*><\/span>/',
            $indicator,
            $event->html,
            1,
        );

        if (is_string($replaced) && $replaced !== $event->html) {
            $event->html = $replaced;
        } else {
            $event->html = $indicator . $event->html;
        }
    }


    // Private Methods
    // =========================================================================

    /**
     * Craft table indexes hyperlink titles when `data-cp-url` is present. The builder
     * should not navigate away on title click — editing is via slide-out instead.
     */
    private static function _stripLabelLinks(string $html): string
    {
        return preg_replace(
            '/<a\b[^>]*\blabel-link\b[^>]*>(.*?)<\/a>/s',
            '$1',
            $html,
        ) ?? $html;
    }

    /**
     * Prevent Craft from binding double-click / taphold editors on staged-delete rows.
     */
    private static function _stripElementEditingAttributes(string $html): string
    {
        return preg_replace(
            '/\sdata-(?:editable|savable|cp-url)(?:=(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?/',
            '',
            $html,
        ) ?? $html;
    }
}
