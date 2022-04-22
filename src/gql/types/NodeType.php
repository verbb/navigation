<?php
namespace verbb\navigation\gql\types;

use verbb\navigation\gql\interfaces\NodeInterface;

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


    // Protected Methods
    // =========================================================================

    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $fieldName = $resolveInfo->fieldName;
        
        return match ($fieldName) {
            'navHandle' => $source->getNav()->handle,
            default => parent::resolve($source, $arguments, $context, $resolveInfo),
        };
    }
}
