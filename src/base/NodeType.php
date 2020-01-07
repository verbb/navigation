<?php
namespace verbb\navigation\base;

use craft\base\Component;

abstract class NodeType extends Component implements NodeTypeInterface
{
    // Static
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Node Type');
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return true;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function hasClasses(): bool
    {
        return false;
    }
}