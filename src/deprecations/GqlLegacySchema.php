<?php
namespace verbb\navigation\deprecations;

use Craft;
use craft\helpers\Gql as CraftGqlHelper;
use craft\models\GqlSchema;

class GqlLegacySchema
{
    // Static Methods
    // =========================================================================

    public static function canSchema(string $component, string $action = 'read', ?GqlSchema $schema = null): bool
    {
        if (CraftGqlHelper::canSchema($component, $action, $schema)) {
            return true;
        }

        $legacyComponent = self::_legacyComponentName($component);

        if ($legacyComponent !== null && CraftGqlHelper::canSchema($legacyComponent, $action, $schema)) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.gql.schema.' . $component,
                sprintf(
                    'GraphQL schema scope `%s` has been deprecated. Use `%s` instead.',
                    $component,
                    $legacyComponent,
                ),
            );

            return true;
        }

        return false;
    }

    public static function canQueryNavigation(): bool
    {
        $allowedEntities = CraftGqlHelper::extractAllowedEntitiesFromSchema();

        if (isset($allowedEntities['navigationMenus'])) {
            return true;
        }

        if (isset($allowedEntities['navigationNavs'])) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.gql.schema.navigationNavs',
                'GraphQL schema scope `navigationNavs` has been deprecated. Use `navigationMenus` instead.',
            );

            return true;
        }

        return false;
    }

    public static function allowedMenuUidPairs(array $pairs): array
    {
        if (!empty($pairs['navigationMenus'])) {
            return ['navigationMenus' => $pairs['navigationMenus']];
        }

        if (!empty($pairs['navigationNavs'])) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.gql.schema.navigationNavs',
                'GraphQL schema scope `navigationNavs` has been deprecated. Use `navigationMenus` instead.',
            );

            return ['navigationMenus' => $pairs['navigationNavs']];
        }

        return ['navigationMenus' => []];
    }


    // Private Methods
    // =========================================================================

    private static function _legacyComponentName(string $component): ?string
    {
        if ($component === 'navigationNavs.all') {
            return 'navigationMenus.all';
        }

        if (str_starts_with($component, 'navigationNavs.')) {
            return 'navigationMenus.' . substr($component, strlen('navigationNavs.'));
        }

        return null;
    }
}
