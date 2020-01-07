<?php
namespace verbb\navigation\gql\types;

use verbb\navigation\gql\interfaces\NodeInterface;

use craft\gql\base\ObjectType;
use craft\gql\interfaces\Element as ElementInterface;

use GraphQL\Type\Definition\ResolveInfo;

class NodeType extends ObjectType
{
    // Public Methods
    // =========================================================================

    public function __construct(array $config)
    {
        $config['interfaces'] = [
            NodeInterface::getType(),
            ElementInterface::getType(),
        ];

        parent::__construct($config);
    }

    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        $fieldName = $resolveInfo->fieldName;

        return $source->$fieldName;
    }
}
