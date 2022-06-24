<?php
namespace verbb\navigation\gql\arguments;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

use craft\gql\base\StructureElementArguments;
use craft\gql\types\QueryArgument;

use GraphQL\Type\Definition\Type;

class NodeArguments extends StructureElementArguments
{
    // Public Methods
    // =========================================================================

    public static function getArguments(): array
    {
        return array_merge(parent::getArguments(), self::getContentArguments(), [
            'nav' => [
                'name' => 'nav',
                'type' => Type::listOf(Type::string()),
                'description' => 'Narrows the query results based on the navigation the node belongs to.',
            ],
            'navHandle' => [
                'name' => 'navHandle',
                'type' => Type::string(),
                'description' => 'Narrows the query results based on the provided navigation handle.',
            ],
            'navId' => [
                'name' => 'navId',
                'type' => Type::int(),
                'description' => 'Narrows the query results based on the provided navigation ID.',
            ],
            'type' => [
                'name' => 'type',
                'type' => Type::listOf(Type::string()),
                'description' => 'Narrows the query results based on the node’s type.',
            ],
        ]);
    }

    public static function getContentArguments(): array
    {
        $navFieldArguments = static::buildContentArguments(Navigation::$plugin->getNavs()->getAllNavs(), Node::class);

        return array_merge(parent::getContentArguments(), $navFieldArguments);
    }
}
