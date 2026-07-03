<?php
namespace verbb\navigation\gql\resolvers;

use verbb\navigation\deprecations\DeprecationHelper;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\Gql as GqlHelper;

use craft\elements\db\ElementQuery;
use craft\elements\ElementCollection;
use craft\gql\base\ElementResolver;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;

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
                return ElementCollection::empty();
            }

            $query = Node::find()->menuHandle($source->$fieldName);
        }

        if (!$query instanceof ElementQuery) {
            return $query;
        }

        $arguments = DeprecationHelper::normalizeGqlNodeArguments($arguments);

        $withLinkedElements = (bool)ArrayHelper::remove($arguments, 'withLinkedElements');
        $withNodeHierarchy = ArrayHelper::remove($arguments, 'withNodeHierarchy');
        $withMenu = (bool)ArrayHelper::remove($arguments, 'withMenu');
        $withProjectedChildren = ArrayHelper::remove($arguments, 'withProjectedChildren');

        foreach ($arguments as $key => $value) {
            $query->$key($value);
        }

        if ($withLinkedElements) {
            $query->withLinkedElements();
        }

        if ($withNodeHierarchy !== null) {
            $query->withNodeHierarchy((bool)$withNodeHierarchy);
        }

        if ($withMenu) {
            $query->withMenu();
        }

        if ($withProjectedChildren !== null) {
            $query->withProjectedChildren((bool)$withProjectedChildren);
        }

        $pairs = GqlHelper::extractAllowedEntitiesFromSchema('read');

        if (!GqlHelper::canQueryNavigation()) {
            return ElementCollection::empty();
        }

        if (!GqlHelper::canSchema('navigationMenus.all') && !GqlHelper::canSchema('navigationNavs.all')) {
            $menuPairs = GqlHelper::allowedMenuUidPairs($pairs);
            $query->andWhere(['in', 'menuId', array_values(Db::idsByUids('{{%navigation_menus}}', $menuPairs['navigationMenus']))]);
        }

        return $query;
    }
}
