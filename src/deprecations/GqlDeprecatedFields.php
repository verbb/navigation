<?php
namespace verbb\navigation\deprecations;

use verbb\navigation\elements\Node;

use Craft;
use GraphQL\Type\Definition\Type;

class GqlDeprecatedFields
{
    // Static Methods
    // =========================================================================

    public static function nodeInterfaceFields(): array
    {
        return [
            'navId' => [
                'name' => 'navId',
                'type' => Type::int(),
                'description' => 'Deprecated. Use `menuId` instead.',
                'resolve' => function(Node $node) {
                    // Deprecated in 4.0.0
                    Craft::$app->getDeprecator()->log(
                        'navigation.gql.nodeInterface.navId',
                        'GraphQL field `navId` has been deprecated. Use `menuId` instead.',
                    );

                    return $node->menuId;
                },
            ],
            'navHandle' => [
                'name' => 'navHandle',
                'type' => Type::string(),
                'description' => 'Deprecated. Use `menuHandle` instead.',
                'resolve' => function(Node $node) {
                    // Deprecated in 4.0.0
                    Craft::$app->getDeprecator()->log(
                        'navigation.gql.nodeInterface.navHandle',
                        'GraphQL field `navHandle` has been deprecated. Use `menuHandle` instead.',
                    );

                    return $node->getMenu()?->getMenuHandle();
                },
            ],
            'navName' => [
                'name' => 'navName',
                'type' => Type::string(),
                'description' => 'Deprecated. Use `menuName` instead.',
                'resolve' => function(Node $node) {
                    // Deprecated in 4.0.0
                    Craft::$app->getDeprecator()->log(
                        'navigation.gql.nodeInterface.navName',
                        'GraphQL field `navName` has been deprecated. Use `menuName` instead.',
                    );

                    return $node->getMenu()?->title;
                },
            ],
        ];
    }

    public static function nodeArguments(): array
    {
        return [
            'nav' => [
                'name' => 'nav',
                'type' => Type::listOf(Type::string()),
                'description' => 'Deprecated. Use `menuHandle` instead.',
            ],
            'navHandle' => [
                'name' => 'navHandle',
                'type' => Type::string(),
                'description' => 'Deprecated. Use `menuHandle` instead.',
            ],
            'navId' => [
                'name' => 'navId',
                'type' => Type::int(),
                'description' => 'Deprecated. Use `menuId` instead.',
            ],
        ];
    }

    public static function resolveNavHandleField(mixed $source): ?string
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(
            'navigation.gql.nodeType.navHandle',
            'GraphQL field `navHandle` has been deprecated. Use `menuHandle` instead.',
        );

        return $source->getMenu()?->getMenuHandle();
    }
}
