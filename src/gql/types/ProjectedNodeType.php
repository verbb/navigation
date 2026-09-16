<?php
namespace verbb\navigation\gql\types;

use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\resolvers\NodeChildrenResolver;
use verbb\navigation\gql\types\generators\CustomAttributeGenerator;
use verbb\navigation\helpers\Gql as GqlHelper;
use verbb\navigation\models\ProjectedNode;
use verbb\navigation\nodetypes\Dynamic;

use Craft;
use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\interfaces\Element;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ProjectedNodeType implements GeneratorInterface
{
    // Static Methods
    // =========================================================================

    public static function getTypeGenerator(): string
    {
        return self::class;
    }

    public static function generateTypes(mixed $context = null): array
    {
        return GqlHelper::canQueryNavigation() ? [self::getName() => self::getType()] : [];
    }

    public static function getType(): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::getName())) {
            return $type;
        }

        return GqlEntityRegistry::createEntity(self::getName(), new ObjectType([
            'name' => self::getName(),
            'interfaces' => [
                NodeInterface::getType(),
            ],
            'fields' => function() {
                $fields = NodeInterface::getFieldDefinitions();

                // Projections have no stored-element metadata. Keep nullable interface
                // fields present without reading content from the linked element.
                foreach ($fields as $name => &$field) {
                    $resolve = $field['resolve'] ?? null;
                    $field['resolve'] = in_array($name, ['navId', 'navHandle', 'navName'], true) && $resolve
                        ? fn(ProjectedNode $node) => $node->parent ? $resolve($node->parent) : null
                        : fn() => null;
                }
                unset($field);

                foreach (['uid', 'siteId', 'uri'] as $name) {
                    $fields[$name]['resolve'] = fn(ProjectedNode $node) => $node->$name;
                }
                $fields['siteHandle']['resolve'] = fn(ProjectedNode $node) => Craft::$app->getSites()->getSiteById($node->siteId)?->handle;
                $fields['language']['resolve'] = fn(ProjectedNode $node) => Craft::$app->getSites()->getSiteById($node->siteId)?->language;

                $projectedFields = [
                    'id' => [
                        'name' => 'id',
                        'type' => Type::nonNull(Type::id()),
                        'description' => 'The projected node’s synthetic ID.',
                        'resolve' => fn(ProjectedNode $node) => $node->id,
                    ],
                    'title' => [
                        'name' => 'title',
                        'type' => Type::nonNull(Type::string()),
                        'resolve' => fn(ProjectedNode $node) => $node->title,
                    ],
                    'url' => [
                        'name' => 'url',
                        'type' => Type::string(),
                        'resolve' => fn(ProjectedNode $node) => $node->getUrl(),
                    ],
                    'nodeUri' => [
                        'name' => 'nodeUri',
                        'type' => Type::string(),
                        'resolve' => fn(ProjectedNode $node) => $node->uri,
                    ],
                    'level' => [
                        'name' => 'level',
                        'type' => Type::int(),
                        'resolve' => fn(ProjectedNode $node) => $node->level,
                    ],
                    'lft' => [
                        'name' => 'lft',
                        'type' => Type::int(),
                        'resolve' => fn(ProjectedNode $node) => $node->lft,
                    ],
                    'rgt' => [
                        'name' => 'rgt',
                        'type' => Type::int(),
                        'resolve' => fn(ProjectedNode $node) => $node->rgt,
                    ],
                    'isProjected' => [
                        'name' => 'isProjected',
                        'type' => Type::nonNull(Type::boolean()),
                        'resolve' => fn() => true,
                    ],
                    'elementId' => [
                        'name' => 'elementId',
                        'type' => Type::int(),
                        'resolve' => fn(ProjectedNode $node) => $node->elementId,
                    ],
                    'menuId' => [
                        'name' => 'menuId',
                        'type' => Type::int(),
                        'resolve' => fn(ProjectedNode $node) => $node->parent?->menuId,
                    ],
                    'menuHandle' => [
                        'name' => 'menuHandle',
                        'type' => Type::string(),
                        'resolve' => fn(ProjectedNode $node) => $node->parent?->getMenu()?->getMenuHandle(),
                    ],
                    'menuName' => [
                        'name' => 'menuName',
                        'type' => Type::string(),
                        'resolve' => fn(ProjectedNode $node) => $node->parent?->getMenu()?->title,
                    ],
                    'type' => [
                        'name' => 'type',
                        'type' => Type::string(),
                        'resolve' => fn() => Dynamic::class,
                    ],
                    'typeLabel' => [
                        'name' => 'typeLabel',
                        'type' => Type::string(),
                        'resolve' => fn(ProjectedNode $node) => $node->parent?->nodeType()?->getTypeLabel() ?? Dynamic::displayName(),
                    ],
                    'classes' => [
                        'name' => 'classes',
                        'type' => Type::string(),
                        'resolve' => fn() => null,
                    ],
                    'urlSuffix' => [
                        'name' => 'urlSuffix',
                        'type' => Type::string(),
                        'resolve' => fn() => null,
                    ],
                    'customAttributes' => [
                        'name' => 'customAttributes',
                        'type' => Type::listOf(CustomAttributeGenerator::generateType()),
                        'resolve' => fn() => [],
                    ],
                    'data' => [
                        'name' => 'data',
                        'type' => Type::string(),
                        'resolve' => fn() => null,
                    ],
                    'newWindow' => [
                        'name' => 'newWindow',
                        'type' => Type::string(),
                        'resolve' => fn() => null,
                    ],
                    'children' => [
                        'name' => 'children',
                        'type' => Type::listOf(NodeInterface::getType()),
                        'resolve' => fn(ProjectedNode $node) => NodeChildrenResolver::resolve($node),
                    ],
                    'parent' => [
                        'name' => 'parent',
                        'type' => NodeInterface::getType(),
                        'resolve' => fn(ProjectedNode $node) => $node->parent,
                    ],
                    'element' => [
                        'name' => 'element',
                        'type' => Element::getType(),
                        'resolve' => function(ProjectedNode $node) {
                            if (GqlHelper::canQueryProjectedNodeElement($node)) {
                                return $node->getElement();
                            }

                            return null;
                        },
                    ],
                ];

                foreach ($projectedFields as $name => $field) {
                    $fields[$name] = array_merge($fields[$name] ?? [], $field);
                }

                return Craft::$app->getGql()->prepareFieldDefinitions($fields, self::getName());
            },
        ]));
    }

    public static function getName(): string
    {
        return 'ProjectedNavigationNode';
    }
}
