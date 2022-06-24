<?php
namespace verbb\navigation\gql\interfaces;

use verbb\navigation\elements\Node;
use verbb\navigation\gql\arguments\NodeArguments;
use verbb\navigation\gql\interfaces\NodeInterface as NodeInterfaceLocal;
use verbb\navigation\gql\types\CustomAttributeType;
use verbb\navigation\gql\types\generators\CustomAttributeGenerator;
use verbb\navigation\gql\types\generators\NodeGenerator;

use craft\gql\base\InterfaceType as BaseInterfaceType;
use craft\gql\interfaces\Element;
use craft\gql\interfaces\Structure;
use craft\gql\types\DateTime;
use craft\gql\TypeLoader;
use craft\gql\TypeManager;
use craft\gql\GqlEntityRegistry;
use craft\helpers\Gql;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

class NodeInterface extends Structure
{
    // Public Methods
    // =========================================================================

    public static function getTypeGenerator(): string
    {
        return NodeGenerator::class;
    }

    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::getName(), new InterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by all nodes.',
            'resolveType' => function(Node $value) {
                return $value->getGqlTypeName();
            },
        ]));

        NodeGenerator::generateTypes();

        return $type;
    }

    public static function getName(): string
    {
        return 'NodeInterface';
    }

    public static function getFieldDefinitions(): array
    {
        return TypeManager::prepareFieldDefinitions(array_merge(parent::getFieldDefinitions(), [
            'elementId' => [
                'name' => 'elementId',
                'type' => Type::int(),
                'description' => 'The ID of the element this node is linked to.',
            ],
            'navId' => [
                'name' => 'navId',
                'type' => Type::int(),
                'description' => 'The ID of the navigation this node belongs to.',
            ],
            'navHandle' => [
                'name' => 'navHandle',
                'type' => Type::string(),
                'description' => 'The handle of the navigation this node belongs to.',
                'resolve' => function($node) {
                    return $node->nav->handle;
                },
            ],
            'navName' => [
                'name' => 'navName',
                'type' => Type::string(),
                'description' => 'The name of the navigation this node belongs to.',
                'resolve' => function($node) {
                    return $node->nav->name;
                },
            ],
            'type' => [
                'name' => 'type',
                'type' => Type::string(),
                'description' => 'The type of node this is.',
            ],
            'classes' => [
                'name' => 'classes',
                'type' => Type::string(),
                'description' => 'Any additional classes for the node.',
            ],
            'urlSuffix' => [
                'name' => 'urlSuffix',
                'type' => Type::string(),
                'description' => 'The URL for this navigation item.',
            ],
            'customAttributes' => [
                'name' => 'customAttributes',
                'type' => Type::listOf(CustomAttributeGenerator::generateType()),
                'description' => 'Any additional custom attributes for the node.',
            ],
            'data' => [
                'name' => 'data',
                'type' => Type::string(),
                'description' => 'Any additional data for the node.',
            ],
            'newWindow' => [
                'name' => 'newWindow',
                'type' => Type::string(),
                'description' => 'Whether this node should open in a new window.',
            ],
            'url' => [
                'name' => 'url',
                'type' => Type::string(),
                'description' => 'The node’s full URL',
            ],
            'nodeUri' => [
                'name' => 'nodeUri',
                'type' => Type::string(),
                'description' => 'The node’s URI',
            ],
            'children' => [
                'name' => 'children',
                'args' => NodeArguments::getArguments(),
                'type' => Type::listOf(NodeInterfaceLocal::getType()),
                'description' => 'The node’s children. Accepts the same arguments as the `nodes` query.',
            ],
            'parent' => [
                'name' => 'parent',
                'type' => NodeInterfaceLocal::getType(),
                'description' => 'The node’s parent.',
            ],
            'element' => [
                'name' => 'element',
                'type' => Element::getType(),
                'description' => 'The element the node links to.',
            ],
        ]), self::getName());
    }
}
