<?php
namespace verbb\navigation\helpers;

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
}