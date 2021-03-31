<?php
namespace verbb\navigation\gql\types\generators;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\gql\arguments\NodeArguments;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\types\NodeType;
use verbb\navigation\helpers\Gql as NavigationGqlHelper;

use Craft;
use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;
use craft\gql\TypeManager;
use craft\helpers\Gql as GqlHelper;

class NodeGenerator implements GeneratorInterface
{
    // Public Methods
    // =========================================================================

    public static function generateTypes($context = null): array
    {
        $navs = Navigation::$plugin->getNavs()->getAllNavs();
        $gqlTypes = [];

        foreach ($navs as $nav) {
            $typeName = Node::gqlTypeNameByContext($nav);
            $requiredContexts = Node::gqlScopesByContext($nav);

            if (!NavigationGqlHelper::isSchemaAwareOf($requiredContexts)) {
                if (!GqlHelper::canSchema('navigationNavs.all')) {
                    continue;
                }
            }

            $contentFields = $nav->getFields();
            $contentFieldGqlTypes = [];

            foreach ($contentFields as $contentField) {
                $contentFieldGqlTypes[$contentField->handle] = $contentField->getContentGqlType();
            }

            $navFields = TypeManager::prepareFieldDefinitions(array_merge(NodeInterface::getFieldDefinitions(), $contentFieldGqlTypes), $typeName);

            // Generate a type for each entry type
            $gqlTypes[$typeName] = GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new NodeType([
                'name' => $typeName,
                'fields' => function() use ($navFields) {
                    return $navFields;
                }
            ]));
        }

        return $gqlTypes;
    }
}
