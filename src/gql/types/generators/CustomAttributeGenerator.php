<?php
namespace verbb\navigation\gql\types\generators;

use verbb\navigation\gql\types\CustomAttributeType;

use craft\gql\base\GeneratorInterface;
use craft\gql\base\ObjectType;
use craft\gql\base\SingleGeneratorInterface;
use craft\gql\GqlEntityRegistry;

class CustomAttributeGenerator implements GeneratorInterface, SingleGeneratorInterface
{
    // Static Methods
    // =========================================================================

    public static function generateTypes($context = null): array
    {
        return [static::generateType($context)];
    }

    public static function getName($context = null): string
    {
        return 'NodeCustomAttribute';
    }

    public static function generateType($context = null): ObjectType
    {
        $typeName = self::getName($context);
        $contentFields = CustomAttributeType::prepareRowFieldDefinition($typeName);

        return GqlEntityRegistry::getEntity($typeName) ?: GqlEntityRegistry::createEntity($typeName, new CustomAttributeType([
            'name' => $typeName,
            'fields' => function() use ($contentFields) {
                return $contentFields;
            },
        ]));
    }
}
