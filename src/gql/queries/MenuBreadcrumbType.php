<?php
namespace verbb\navigation\gql\queries;

use verbb\navigation\gql\interfaces\NodeInterface;

use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class MenuBreadcrumbType
{
    // Static Methods
    // =========================================================================

    public static function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity('NavigationMenuBreadcrumb')) {
            return $type;
        }

        return GqlEntityRegistry::createEntity('NavigationMenuBreadcrumb', new ObjectType([
            'name' => 'NavigationMenuBreadcrumb',
            'fields' => [
                'title' => Type::nonNull(Type::string()),
                'url' => Type::string(),
                'current' => Type::nonNull(Type::boolean()),
                'node' => NodeInterface::getType(),
            ],
        ]));
    }
}
