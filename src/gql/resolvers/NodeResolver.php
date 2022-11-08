<?php
namespace verbb\navigation\gql\resolvers;

use verbb\navigation\elements\Node;
use verbb\navigation\helpers\Gql as GqlHelper;

use craft\elements\db\ElementQuery;
use craft\gql\base\ElementResolver;
use craft\helpers\Db;

use Illuminate\Support\Collection;

class NodeResolver extends ElementResolver
{
    // Static Methods
    // =========================================================================

    public static function prepareQuery(mixed $source, array $arguments, ?string $fieldName = null): mixed
    {
        if ($source === null) {
            $query = Node::find();
        } else {
            // Protect against empty fields
            if (!$source->$fieldName) {
                return Collection::empty();
            }

            $query = Node::find()->navHandle($source->$fieldName);
        }

        if (!$query instanceof ElementQuery) {
            return $query;
        }

        foreach ($arguments as $key => $value) {
            $query->$key($value);
        }

        $pairs = GqlHelper::extractAllowedEntitiesFromSchema('read');

        if (!GqlHelper::canQueryNavigation()) {
            return Collection::empty();
        }

        if (!GqlHelper::canSchema('navigationNavs.all')) {
            $query->andWhere(['in', 'navId', array_values(Db::idsByUids('{{%navigation_navs}}', $pairs['navigationNavs']))]);
        }

        return $query;
    }
}
