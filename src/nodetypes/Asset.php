<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\ElementNodeType;

use Craft;
use craft\elements\Asset as AssetElement;

class Asset extends ElementNodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('site', AssetElement::pluralDisplayName());
    }

    public static function getElementType(): string
    {
        return AssetElement::class;
    }

    public static function getColor(): string
    {
        return '#e12d39';
    }
}
