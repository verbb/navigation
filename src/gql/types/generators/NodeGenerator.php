<?php
namespace verbb\navigation\gql\types\generators;

use verbb\navigation\elements\Node;
use verbb\navigation\gql\arguments\NodeArguments;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\types\NodeType;

use Craft;
use craft\gql\base\GeneratorInterface;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;

class NodeGenerator implements GeneratorInterface
{
    // Public Methods
    // =========================================================================

    public static function generateTypes($context = null): array
    {
        $gqlTypes = [];
        $typeName = Node::gqlTypeNameByContext(null);

        $contentFields = Craft::$app->getFields()->getLayoutByType(Node::class)->getFields();
        $contentFieldGqlTypes = [];

        /** @var Field $contentField */
        foreach ($contentFields as $contentField) {
            $contentFieldGqlTypes[$contentField->handle] = $contentField->getContentGqlType();
        }

        $nodeFields = array_merge(NodeInterface::getFieldDefinitions(), $contentFieldGqlTypes);

        // Generate a type for each entry type
        $gqlTypes[$typeName] = GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new NodeType([
            'name' => $typeName,
            'fields' => function() use ($nodeFields) {
                return $nodeFields;
            }
        ]));

        return $gqlTypes;
    }
}
