<?php
namespace verbb\navigation\gql\resolvers;

use verbb\navigation\elements\Node;

use craft\gql\base\ElementResolver;

use GraphQL\Type\Definition\ResolveInfo;

class NodeResolver extends ElementResolver
{
    // Public Methods
    // =========================================================================

    public static function prepareQuery($source, array $arguments, $fieldName = null)
    {
        if ($source === null) {
            $query = Node::find();
        } else {
            $query = $source->$fieldName;
        }

        if (is_array($query)) {
            return $query;
        }

        foreach ($arguments as $key => $value) {
            $query->$key($value);
        }

        return $query;
    }
}
