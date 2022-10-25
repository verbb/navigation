<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\Gql as GqlHelper;

class Gql extends GqlHelper
{
    // Public Methods
    // =========================================================================

    public static function canQueryNavigation(): bool
    {
        $allowedEntities = self::extractAllowedEntitiesFromSchema();

        return isset($allowedEntities['navigationNavs']);
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
}