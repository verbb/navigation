<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\ElementNodeType;

use Craft;
use craft\elements\Category as CategoryElement;

class Category extends ElementNodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('site', CategoryElement::pluralDisplayName());
    }

    public static function getElementType(): string
    {
        return CategoryElement::class;
    }

    public static function getColor(): string
    {
        return '#1BB311';
    }
}
