<?php
namespace verbb\navigation\gql\types\generators;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\types\NodeType;
use verbb\navigation\helpers\Gql as NavigationGqlHelper;

use Craft;
use craft\gql\base\Generator;
use craft\gql\base\GeneratorInterface;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\helpers\Gql as CraftGqlHelper;

class NodeGenerator extends Generator implements GeneratorInterface, SingleGeneratorInterface
{
    // Static Methods
    // =========================================================================

    public static function generateTypes(mixed $context = null): array
    {
        $navs = Navigation::$plugin->getMenus()->getAllMenus();
        $gqlTypes = [];

        foreach ($navs as $nav) {
            $requiredContexts = Node::gqlScopesByContext($nav);

            if (!CraftGqlHelper::isSchemaAwareOf($requiredContexts)) {
                if (!NavigationGqlHelper::canSchema('navigationMenus.all') && !NavigationGqlHelper::canSchema('navigationNavs.all')) {
                    continue;
                }
            }

            $type = static::generateType($nav);
            $gqlTypes[$type->name] = $type;
        }

        return $gqlTypes;
    }

    public static function generateType(mixed $context): mixed
    {
        $typeName = Node::gqlTypeNameByContext($context);

        if ($createdType = GqlEntityRegistry::getEntity($typeName)) {
            return $createdType;
        }

        $contentFieldGqlTypes = self::getContentFields($context);
        $navFields = Craft::$app->getGql()->prepareFieldDefinitions(array_merge(NodeInterface::getFieldDefinitions(), $contentFieldGqlTypes), $typeName);

        return GqlEntityRegistry::createEntity($typeName, new NodeType([
            'name' => $typeName,
            'fields' => function() use ($navFields) {
                return $navFields;
            },
        ]));
    }
}
