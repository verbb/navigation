<?php
namespace verbb\navigation\gql\arguments;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\GqlDeprecatedFields;
use verbb\navigation\elements\Node;

use Craft;
use craft\gql\base\StructureElementArguments;

use GraphQL\Type\Definition\Type;

class NodeArguments extends StructureElementArguments
{
    // Static Methods
    // =========================================================================

    public static function getArguments(): array
    {
        return array_merge(parent::getArguments(), self::getContentArguments(), [
            'menuHandle' => [
                'name' => 'menuHandle',
                'type' => Type::string(),
                'description' => 'Narrows the query results based on the provided menu handle.',
            ],
            'menuId' => [
                'name' => 'menuId',
                'type' => Type::int(),
                'description' => 'Narrows the query results based on the provided menu ID.',
            ],
            'type' => [
                'name' => 'type',
                'type' => Type::listOf(Type::string()),
                'description' => 'Narrows the query results based on the node’s type.',
            ],
            'withLinkedElements' => [
                'name' => 'withLinkedElements',
                'type' => Type::boolean(),
                'description' => 'Batch-hydrates linked Craft elements after the query executes.',
            ],
            'withNodeHierarchy' => [
                'name' => 'withNodeHierarchy',
                'type' => Type::boolean(),
                'description' => 'Wires parent/child relationships in memory after the query executes. Omit for smart auto on nav-scoped reads.',
            ],
            'withMenu' => [
                'name' => 'withMenu',
                'type' => Type::boolean(),
                'description' => 'Batch-hydrates the parent Menu element (including custom fields) after the query executes.',
            ],
            'withProjectedChildren' => [
                'name' => 'withProjectedChildren',
                'type' => Type::boolean(),
                'description' => 'Includes read-time Dynamic projections in hierarchy output. Pass false to skip projected children.',
            ],
        ], GqlDeprecatedFields::nodeArguments());
    }

    public static function getContentArguments(): array
    {
        $navFieldArguments = Craft::$app->getGql()->getContentArguments(Navigation::$plugin->getMenus()->getAllMenus(), Node::class);

        return array_merge(parent::getContentArguments(), $navFieldArguments);
    }
}
