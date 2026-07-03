<?php
namespace verbb\navigation\gql\types;

use verbb\navigation\deprecations\GqlDeprecatedFields;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\gql\interfaces\NodeInterface;
use verbb\navigation\gql\resolvers\NodeChildrenResolver;
use verbb\navigation\models\ProjectedNode;

use GraphQL\Type\Definition\ResolveInfo;

class NodeType extends \craft\gql\types\elements\Element
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

        if ($fieldName === 'menuHandle') {
            return $source->getMenu()?->getMenuHandle();
        }

        if ($fieldName === 'navHandle') {
            return GqlDeprecatedFields::resolveNavHandleField($source);
        }

        if ($fieldName === 'children') {
            return NodeChildrenResolver::resolve($source);
        }

        if ($fieldName === 'parent') {
            if ($source instanceof ProjectedNode) {
                return $source->parent;
            }

            if ($source instanceof NodeElement) {
                return $source->getParent();
            }

            return null;
        }

        if ($fieldName === 'isProjected') {
            return $source instanceof ProjectedNode;
        }

        return parent::resolve($source, $arguments, $context, $resolveInfo);
    }
}
