<?php
namespace verbb\navigation\helpers;

use verbb\navigation\deprecations\GqlLegacySchema;
use verbb\navigation\elements\Node;
use verbb\navigation\models\ProjectedNode;

use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\Gql as GqlHelper;
use craft\models\GqlSchema;

class Gql extends GqlHelper
{
    // Public Methods
    // =========================================================================

    public static function canSchema(string $component, string $action = 'read', ?GqlSchema $schema = null): bool
    {
        return GqlLegacySchema::canSchema($component, $action, $schema);
    }

    public static function canQueryNavigation(): bool
    {
        return GqlLegacySchema::canQueryNavigation();
    }

    public static function allowedMenuUidPairs(array $pairs): array
    {
        return GqlLegacySchema::allowedMenuUidPairs($pairs);
    }

    public static function canQueryNodeElement(Node $node): bool
    {
        if ($element = $node->getElement()) {
            if ($element instanceof Entry) {
                return self::canQueryEntries();
            } else if ($element instanceof Category) {
                return self::canQueryCategories();
            } else if ($element instanceof Asset) {
                return self::canQueryAssets();
            }
        }

        return true;
    }

    public static function canQueryProjectedNodeElement(ProjectedNode $node): bool
    {
        if ($element = $node->getElement()) {
            if ($element instanceof Entry) {
                return self::canQueryEntries();
            } else if ($element instanceof Category) {
                return self::canQueryCategories();
            } else if ($element instanceof Asset) {
                return self::canQueryAssets();
            }
        }

        return true;
    }
}
