<?php
namespace verbb\navigation\base;

use verbb\navigation\elements\Node;

use craft\base\ElementInterface;
use craft\elements\User;

abstract class DynamicSource implements DynamicSourceProvider
{
    // Static Methods
    // =========================================================================

    /** Extensions can restrict source selection without changing the provider interface. */
    public static function canAuthorNode(Node $node, User $user): bool
    {
        return true;
    }

    public static function displayName(): string
    {
        /** @var class-string<ElementInterface> $elementType */
        $elementType = static::elementType();

        return $elementType::pluralDisplayName();
    }
}
