<?php
namespace verbb\navigation\nodetypes;

use Craft;

use verbb\navigation\base\NodeType;

class PassiveType extends NodeType
{
    // Static
    // =========================================================================

    public static function displayName(): string
    {
        return 'Passive';
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return false;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function hasClasses(): bool
    {
        return true;
    }

    public static function hasAttributes(): bool
    {
        return true;
    }
}
