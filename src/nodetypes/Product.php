<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\ElementNodeType;

use Craft;
use craft\commerce\elements\Product as ProductElement;

class Product extends ElementNodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('site', ProductElement::pluralDisplayName());
    }

    public static function getElementType(): string
    {
        return ProductElement::class;
    }

    public static function getColor(): string
    {
        return '#7c3aed';
    }
}
