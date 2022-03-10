<?php
namespace verbb\navigation\gql\types;

use Craft;
use craft\gql\base\ObjectType;

use GraphQL\Type\Definition\Type;

class CustomAttributeType extends ObjectType
{
    // Static Methods
    // =========================================================================

    public static function prepareRowFieldDefinition(string $typeName): array
    {
        $contentFields = [
            'attribute' => Type::string(),
            'value' => Type::string(),
        ];

        return Craft::$app->getGql()->prepareFieldDefinitions($contentFields, $typeName);
    }
}
