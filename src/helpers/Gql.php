<?php
namespace verbb\navigation\helpers;

use verbb\navigation\deprecations\GqlLegacySchema;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\ProjectedNode;

use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\Gql as GqlHelper;
use craft\models\GqlSchema;

class Gql extends GqlHelper
{
    // Public Methods
    // =========================================================================

    public static function canSchema(string $component, string $action = 'read', ?GqlSchema $schema = null): bool
    {
        return GqlLegacySchema::canSchema($component, $action, $schema);
    }

    public static function canQueryNavigation(): bool
    {
        return GqlLegacySchema::canQueryNavigation();
    }

    public static function allowedMenuUidPairs(array $pairs): array
    {
        return GqlLegacySchema::allowedMenuUidPairs($pairs);
    }

    /**
     * Whether the active GraphQL schema may read a specific menu (by settings or UID).
     */
    public static function canQueryMenu(MenuSettings|string|null $menuOrUid, ?GqlSchema $schema = null): bool
    {
        if ($menuOrUid === null) {
            return false;
        }

        if (self::canSchema('navigationMenus.all', 'read', $schema) || self::canSchema('navigationNavs.all', 'read', $schema)) {
            return true;
        }

        $uid = is_string($menuOrUid) ? $menuOrUid : $menuOrUid->uid;

        if (!$uid) {
            return false;
        }

        return self::canSchema('navigationMenus.' . $uid, 'read', $schema)
            || self::canSchema('navigationNavs.' . $uid, 'read', $schema);
    }

    public static function canQueryNodeElement(Node $node): bool
    {
        return self::canQueryLinkedElement($node->getElement());
    }

    public static function canQueryProjectedNodeElement(ProjectedNode $node): bool
    {
        return self::canQueryLinkedElement($node->getElement());
    }

    /**
     * Schema-aware linked element check (section / category group / volume / product type).
     * Broad canQueryEntries/Categories/Assets alone is insufficient when a schema grants
     * only a subset of those entities.
     */
    public static function canQueryLinkedElement(?ElementInterface $element, ?GqlSchema $schema = null): bool
    {
        if ($element === null) {
            return true;
        }

        if ($element instanceof Entry) {
            $section = $element->getSection();

            // Nested entries may lack a section — fall back to any-entries grant.
            if (!$section?->uid) {
                return self::canQueryEntries($schema);
            }

            return self::isSchemaAwareOf('sections.' . $section->uid, $schema);
        }

        if ($element instanceof Category) {
            $group = $element->getGroup();

            if (!$group?->uid) {
                return self::canQueryCategories($schema);
            }

            return self::isSchemaAwareOf('categorygroups.' . $group->uid, $schema);
        }

        if ($element instanceof Asset) {
            $volume = $element->getVolume();

            if (!$volume?->uid) {
                return self::canQueryAssets($schema);
            }

            return self::isSchemaAwareOf('volumes.' . $volume->uid, $schema);
        }

        // Optional Commerce — productTypes.{uid} mirrors Craft Commerce GQL scopes.
        if (is_a($element, 'craft\\commerce\\elements\\Product', false)) {
            $type = method_exists($element, 'getType') ? $element->getType() : null;

            if (!$type || empty($type->uid)) {
                return false;
            }

            return self::isSchemaAwareOf('productTypes.' . $type->uid, $schema);
        }

        return true;
    }
}
