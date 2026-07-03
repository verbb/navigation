<?php
namespace verbb\navigation\gql\interfaces;

use verbb\navigation\deprecations\GqlDeprecatedFields;
use verbb\navigation\elements\Node;
use verbb\navigation\gql\arguments\NodeArguments;
use verbb\navigation\gql\interfaces\NodeInterface as NodeInterfaceLocal;
use verbb\navigation\gql\resolvers\NodeChildrenResolver;
use verbb\navigation\gql\types\generators\CustomAttributeGenerator;
use verbb\navigation\gql\types\generators\NodeGenerator;
use verbb\navigation\gql\types\ProjectedNodeType;
use verbb\navigation\helpers\Gql as GqlHelper;
use verbb\navigation\models\ProjectedNode;

use Craft;
use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\Element;
use craft\gql\interfaces\Structure;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

class NodeInterface extends Structure
{
    // Static Methods
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
            'resolveType' => function(mixed $value) {
                if ($value instanceof ProjectedNode) {
                    return ProjectedNodeType::getType();
                }

                if ($value instanceof Node) {
                    return $value->getGqlTypeName();
                }

                return null;
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
        return Craft::$app->getGql()->prepareFieldDefinitions(array_merge(
            parent::getFieldDefinitions(),
            [
                'isProjected' => [
                    'name' => 'isProjected',
                    'type' => Type::nonNull(Type::boolean()),
                    'description' => 'Whether this node is projected at read time rather than stored in the menu structure.',
                    'resolve' => fn(mixed $node) => $node instanceof ProjectedNode,
                ],
                'elementId' => [
                    'name' => 'elementId',
                    'type' => Type::int(),
                    'description' => 'The ID of the element this node is linked to.',
                    'resolve' => fn(mixed $node) => $node instanceof ProjectedNode
                        ? $node->elementId
                        : ($node instanceof Node ? $node->elementId : null),
                ],
                'menuId' => [
                    'name' => 'menuId',
                    'type' => Type::int(),
                    'description' => 'The ID of the menu this node belongs to.',
                    'resolve' => fn(mixed $node) => $node instanceof Node ? $node->menuId : ($node instanceof ProjectedNode ? $node->parent?->menuId : null),
                ],
                'menuHandle' => [
                    'name' => 'menuHandle',
                    'type' => Type::string(),
                    'description' => 'The handle of the menu this node belongs to.',
                    'resolve' => fn(mixed $node) => $node instanceof Node
                        ? $node->getMenu()?->getMenuHandle()
                        : ($node instanceof ProjectedNode ? $node->parent?->getMenu()?->getMenuHandle() : null),
                ],
                'menuName' => [
                    'name' => 'menuName',
                    'type' => Type::string(),
                    'description' => 'The name of the menu this node belongs to.',
                    'resolve' => fn(mixed $node) => $node instanceof Node
                        ? $node->getMenu()?->title
                        : ($node instanceof ProjectedNode ? $node->parent?->getMenu()?->title : null),
                ],
                'type' => [
                    'name' => 'type',
                    'type' => Type::string(),
                    'description' => 'The type of node this is.',
                    'resolve' => fn(mixed $node) => $node instanceof Node ? $node->type : null,
                ],
                'typeLabel' => [
                    'name' => 'typeLabel',
                    'type' => Type::string(),
                    'description' => 'The display name for the type of node this is.',
                    'resolve' => fn(mixed $node) => $node instanceof Node
                        ? $node->nodeType()?->getTypeLabel()
                        : ($node instanceof ProjectedNode ? $node->parent?->nodeType()?->getTypeLabel() : null),
                ],
                'classes' => [
                    'name' => 'classes',
                    'type' => Type::string(),
                    'description' => 'Any additional classes for the node.',
                ],
                'urlSuffix' => [
                    'name' => 'urlSuffix',
                    'type' => Type::string(),
                    'description' => 'The URL for this node.',
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
                    'resolve' => fn(mixed $node) => NodeChildrenResolver::resolve($node),
                ],
                'parent' => [
                    'name' => 'parent',
                    'type' => NodeInterfaceLocal::getType(),
                    'description' => 'The node’s parent.',
                    'resolve' => fn(mixed $node) => $node instanceof Node
                        ? $node->getParent()
                        : ($node instanceof ProjectedNode ? $node->parent : null),
                ],
                'element' => [
                    'name' => 'element',
                    'type' => Element::getType(),
                    'description' => 'The element the node links to.',
                    'resolve' => function(mixed $node) {
                        if ($node instanceof ProjectedNode) {
                            if (GqlHelper::canQueryProjectedNodeElement($node)) {
                                return $node->getElement();
                            }

                            return null;
                        }

                        if ($node instanceof Node && GqlHelper::canQueryNodeElement($node)) {
                            return $node->getElement();
                        }

                        return null;
                    },
                ],
            ],
            GqlDeprecatedFields::nodeInterfaceFields(),
        ), self::getName());
    }
}
