<?php
namespace verbb\navigation\deprecations;

use Craft;

class DeprecationHelper
{
    // Static Methods
    // =========================================================================

    public static function normalizeNodeQueryCriteria(array $criteria): array
    {
        if (array_key_exists('navHandle', $criteria)) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.nodeQuery.navHandle',
                'The `navHandle` query param has been deprecated. Use `menuHandle` instead.',
            );

            $criteria['menuHandle'] = $criteria['navHandle'];
            unset($criteria['navHandle']);
        }

        if (array_key_exists('nav', $criteria) && !array_key_exists('menuHandle', $criteria)) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.nodeQuery.nav',
                'The `nav` query param has been deprecated. Use `menuHandle` or `menu()` instead.',
            );

            $criteria['menuHandle'] = $criteria['nav'];
            unset($criteria['nav']);
        }

        if (array_key_exists('navId', $criteria) && !array_key_exists('menuId', $criteria)) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.nodeQuery.navId',
                'The `navId` query param has been deprecated. Use `menuId` instead.',
            );

            $criteria['menuId'] = $criteria['navId'];
            unset($criteria['navId']);
        }

        return $criteria;
    }

    public static function normalizeGqlNodeArguments(array $args): array
    {
        if (!empty($args['navHandle']) && empty($args['menuHandle'])) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.gql.nodeArguments.navHandle',
                'GraphQL argument `navHandle` has been deprecated. Use `menuHandle` instead.',
            );

            $args['menuHandle'] = $args['navHandle'];
            unset($args['navHandle']);
        }

        if (!empty($args['nav']) && empty($args['menuHandle'])) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.gql.nodeArguments.nav',
                'GraphQL argument `nav` has been deprecated. Use `menuHandle` instead.',
            );

            $args['menuHandle'] = is_array($args['nav']) ? reset($args['nav']) : $args['nav'];
            unset($args['nav']);
        }

        if (isset($args['navId']) && !isset($args['menuId'])) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'navigation.gql.nodeArguments.navId',
                'GraphQL argument `navId` has been deprecated. Use `menuId` instead.',
            );

            $args['menuId'] = $args['navId'];
            unset($args['navId']);
        }

        return $args;
    }

    public static function resolveMenuHandleArg(
        array $args,
        string $method,
        string $canonicalKey = 'menuHandle',
        string $legacyKey = 'navHandle',
    ): ?string {
        if (!empty($args[$legacyKey]) && empty($args[$canonicalKey])) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                $method,
                sprintf('GraphQL argument `%s` has been deprecated. Use `%s` instead.', $legacyKey, $canonicalKey),
            );

            return (string)$args[$legacyKey];
        }

        if (!empty($args[$canonicalKey])) {
            return (string)$args[$canonicalKey];
        }

        return null;
    }

    /**
     * Resolves legacy CP / console route params (`navId`) to `menuId`.
     */
    public static function resolveMenuIdParam(?int $menuId, ?int $navId, string $context): ?int
    {
        if ($menuId !== null) {
            return $menuId;
        }

        if ($navId !== null) {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                $context,
                'The `navId` param has been deprecated. Use `menuId` instead.',
            );

            return $navId;
        }

        return null;
    }
}
