<?php
namespace verbb\navigation\gql\types;

use verbb\navigation\gql\interfaces\NodeInterface;

use craft\gql\base\ObjectType;
use craft\gql\interfaces\Element as ElementInterface;
use craft\gql\types\elements\Element;

use GraphQL\Type\Definition\ResolveInfo;

class NodeType extends Element
{
    // Public Methods
    // =========================================================================

    public function __construct(array $config)
    {
        $config['interfaces'] = [
            NodeInterface::getType(),
        ];

        parent::__construct($config);
    }

    protected function resolve($source, $arguments, $context, ResolveInfo $resolveInfo)
    {
        $fieldName = $resolveInfo->fieldName;

        switch ($fieldName) {
            case 'navHandle':
                return $source->getNav()->handle;
        }

        return parent::resolve($source, $arguments, $context, $resolveInfo);
    }
}
