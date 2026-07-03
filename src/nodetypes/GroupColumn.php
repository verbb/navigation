<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\NodeType;

use Craft;

class GroupColumn extends NodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Group / Column');
    }

    public static function hasUrl(): bool
    {
        return false;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function getColor(): string
    {
        return '#64748b';
    }

    public static function getTag(): string
    {
        return 'span';
    }
}
