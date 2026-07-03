<?php
namespace verbb\navigation\gql\resolvers;

use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\ProjectedNode;

class NodeChildrenResolver
{
    // Static Methods
    // =========================================================================

    public static function resolve(mixed $source): array
    {
        if ($source instanceof NodeElement) {
            return $source->getChildren()->all();
        }

        if ($source instanceof ProjectedNode) {
            return $source->getChildren();
        }

        return [];
    }
}
