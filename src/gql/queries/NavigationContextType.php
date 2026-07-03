<?php
namespace verbb\navigation\gql\queries;

use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\models\NavigationContext;

use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class NavigationContextType
{
    // Static Methods
    // =========================================================================

    public static function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity('NavigationContext')) {
            return $type;
        }

        return GqlEntityRegistry::createEntity('NavigationContext', new ObjectType([
            'name' => 'NavigationContext',
            'fields' => [
                'menuHandle' => Type::nonNull(Type::string()),
                'current' => NodeInterface::getType(),
                'currentNodes' => Type::listOf(NodeInterface::getType()),
                'activeNodes' => Type::listOf(NodeInterface::getType()),
                'parent' => NodeInterface::getType(),
                'ancestors' => Type::listOf(NodeInterface::getType()),
                'siblings' => Type::listOf(NodeInterface::getType()),
                'children' => Type::listOf(NodeInterface::getType()),
                'branch' => Type::listOf(NodeInterface::getType()),
            ],
            'resolveField' => function(NavigationContext $context, $args, $ctx, $info) {
                return match ($info->fieldName) {
                    'menuHandle' => $context->menuHandle,
                    'current' => $context->current(),
                    'currentNodes' => $context->currentNodes(),
                    'activeNodes' => $context->activeNodes(),
                    'parent' => $context->parent(),
                    'ancestors' => $context->ancestors(),
                    'siblings' => $context->siblings(),
                    'children' => $context->children(),
                    'branch' => $context->branch(),
                    default => null,
                };
            },
        ]));
    }
}
