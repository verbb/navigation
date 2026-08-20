<?php
namespace verbb\navigation\base;

use craft\base\ElementInterface;

abstract class DynamicSource implements DynamicSourceProvider
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        /** @var class-string<ElementInterface> $elementType */
        $elementType = static::elementType();

        return $elementType::pluralDisplayName();
    }
}
