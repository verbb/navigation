<?php
namespace verbb\navigation\gql\resolvers;

use verbb\navigation\elements\Node;
use verbb\navigation\helpers\Gql as GqlHelper;

use craft\gql\base\ElementResolver;
use craft\helpers\Db;

class NodeResolver extends ElementResolver
{
    // Static Methods
    // =========================================================================

    public static function prepareQuery(mixed $source, array $arguments, $fieldName = null): mixed
    {
        if ($source === null) {
            $query = Node::find();
        } else {
            $query = Node::find()->navHandle($source->$fieldName);
        }

        if (is_array($query)) {
            return $query;
        }

        foreach ($arguments as $key => $value) {
            $query->$key($value);
        }

        $pairs = GqlHelper::extractAllowedEntitiesFromSchema('read');

        if (!GqlHelper::canQueryNavigation()) {
            return [];
        }

        if (!GqlHelper::canSchema('navigationNavs.all')) {
            $query->andWhere(['in', 'navId', array_values(Db::idsByUids('{{%navigation_navs}}', $pairs['navigationNavs']))]);
        }

        return $query;
    }
}
