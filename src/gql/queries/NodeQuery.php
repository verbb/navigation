<?php
namespace verbb\navigation\gql\queries;

use verbb\navigation\gql\arguments\NodeArguments;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\resolvers\NodeResolver;
use verbb\navigation\helpers\Gql as GqlHelper;

use craft\gql\base\Query;

use GraphQL\Type\Definition\Type;

class NodeQuery extends Query
{
    // Public Methods
    // =========================================================================

    public static function getQueries($checkToken = true): array
    {
        if ($checkToken && !GqlHelper::canQueryNavigation()) {
            return [];
        }

        return [
            'nodes' => [
                'type' => Type::listOf(NodeInterface::getType()),
                'args' => NodeArguments::getArguments(),
                'resolve' => NodeResolver::class . '::resolve',
                'description' => 'This query is used to query for nodes.',
            ],
            'node' => [
                'type' => NodeInterface::getType(),
                'args' => NodeArguments::getArguments(),
                'resolve' => NodeResolver::class . '::resolveOne',
                'description' => 'This query is used to query for a single node.',
            ],
        ];
    }
}
