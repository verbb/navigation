<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node;

use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\helpers\Db;
use craft\helpers\StringHelper;

/**
 * One-off repair for beta installs hit by m260627 reclaiming foreign element ids as Menu.
 *
 * Not a schema migration — opt-in via `php craft navigation/menus/fix-menu-element-collisions`.
 * Fresh installs and post-GA upgrades never need this; forward `_syncMenuElement` never reclaims.
 */
class MenuElementCollisionRepair
{
    // Static Methods
    // =========================================================================

    /**
     * @return array{restoredForeign: int, remappedMenus: int, backfilledMenus: int, restoredNodeTitles: int}
     */
    public static function run(): array
    {
        $db = Craft::$app->getDb();

        if (!$db->tableExists('{{%navigation_menus}}')) {
            return [
                'restoredForeign' => 0,
                'remappedMenus' => 0,
                'backfilledMenus' => 0,
                'restoredNodeTitles' => 0,
            ];
        }

        $restoredForeign = self::_restoreForeignElementsMistypedAsMenu();
        $remappedMenus = self::_remapCollidingMenusOntoExclusiveElements();
        $backfilledMenus = self::_backfillMissingMenuElements();
        $restoredNodeTitles = self::_restoreLinkedNodeTitlesFromEntries();

        return [
            'restoredForeign' => $restoredForeign,
            'remappedMenus' => $remappedMenus,
            'backfilledMenus' => $backfilledMenus,
            'restoredNodeTitles' => $restoredNodeTitles,
        ];
    }


    // Private Methods
    // =========================================================================

    private static function _restoreForeignElementsMistypedAsMenu(): int
    {
        $ids = (new Query())
            ->select(['id'])
            ->from([Table::ELEMENTS])
            ->where(['type' => Menu::class])
            ->column();

        $count = 0;

        foreach ($ids as $id) {
            $id = (int)$id;
            $foreignType = self::_foreignElementType($id);

            if ($foreignType === null) {
                continue;
            }

            Db::update(Table::ELEMENTS, [
                'type' => $foreignType,
                'fieldLayoutId' => self::_fieldLayoutIdForForeignElement($id, $foreignType),
            ], ['id' => $id], updateTimestamp: false);

            if ($foreignType === Entry::class || $foreignType === Node::class) {
                self::_restoreTitlesFromRevisions($id);
            }

            $count++;
        }

        return $count;
    }

    private static function _remapCollidingMenusOntoExclusiveElements(): int
    {
        $menus = (new Query())
            ->select(['id', 'uid', 'name', 'menuFieldLayoutId', 'dateCreated', 'dateUpdated', 'dateDeleted'])
            ->from(['{{%navigation_menus}}'])
            ->all();

        $count = 0;

        foreach ($menus as $menu) {
            if (self::_menuOwnsExclusiveElement((int)$menu['id'])) {
                continue;
            }

            self::_remapMenuPrimaryKey($menu);
            $count++;
        }

        return $count;
    }

    private static function _backfillMissingMenuElements(): int
    {
        $menus = (new Query())
            ->select(['id', 'uid', 'menuFieldLayoutId', 'dateCreated', 'dateUpdated'])
            ->from(['{{%navigation_menus}}'])
            ->where(['dateDeleted' => null])
            ->all();

        $count = 0;

        foreach ($menus as $menu) {
            $id = (int)$menu['id'];

            if (self::_menuOwnsExclusiveElement($id)) {
                self::_ensureMenuElementSites($id, $menu);
                continue;
            }

            if (!(new Query())->from([Table::ELEMENTS])->where(['id' => $id])->exists()) {
                self::_insertMenuElementRow($menu);
                self::_ensureMenuElementSites($id, $menu);
                $count++;
            }
        }

        return $count;
    }

    private static function _remapMenuPrimaryKey(array $menu): void
    {
        $db = Craft::$app->getDb();
        $oldId = (int)$menu['id'];
        $now = $menu['dateUpdated'] ?: $menu['dateCreated'];

        $db->createCommand()->insert(Table::ELEMENTS, [
            'canonicalId' => null,
            'draftId' => null,
            'revisionId' => null,
            'fieldLayoutId' => $menu['menuFieldLayoutId'] ?: null,
            'type' => Menu::class,
            'enabled' => true,
            'archived' => false,
            'dateCreated' => $menu['dateCreated'] ?: $now,
            'dateUpdated' => $now,
            'dateDeleted' => null,
            'deletedWithOwner' => null,
            'uid' => $menu['uid'],
        ])->execute();

        $newId = (int)$db->getLastInsertID(Table::ELEMENTS);

        Db::update(Table::ELEMENTS, ['canonicalId' => $newId], ['id' => $newId], updateTimestamp: false);

        $row = (new Query())
            ->from(['{{%navigation_menus}}'])
            ->where(['id' => $oldId])
            ->one();

        if (!$row) {
            $db->createCommand()->delete(Table::ELEMENTS, ['id' => $newId])->execute();
            return;
        }

        $row['id'] = $newId;
        $db->createCommand()->insert('{{%navigation_menus}}', $row)->execute();

        Db::update('{{%navigation_nodes}}', ['menuId' => $newId], ['menuId' => $oldId], updateTimestamp: false);

        if ($db->tableExists('{{%navigation_menus_sites}}')) {
            Db::update('{{%navigation_menus_sites}}', ['menuId' => $newId], ['menuId' => $oldId], updateTimestamp: false);
        }

        if ($db->tableExists('{{%navigation_build_sessions}}')) {
            Db::update('{{%navigation_build_sessions}}', ['menuId' => $newId], ['menuId' => $oldId], updateTimestamp: false);
        }

        $db->createCommand()->delete('{{%navigation_menus}}', ['id' => $oldId])->execute();

        self::_ensureMenuElementSites($newId, $menu);
    }

    private static function _insertMenuElementRow(array $menu): void
    {
        $id = (int)$menu['id'];

        Craft::$app->getDb()->createCommand()->insert(Table::ELEMENTS, [
            'id' => $id,
            'canonicalId' => $id,
            'draftId' => null,
            'revisionId' => null,
            'fieldLayoutId' => $menu['menuFieldLayoutId'] ?: null,
            'type' => Menu::class,
            'enabled' => true,
            'archived' => false,
            'dateCreated' => $menu['dateCreated'],
            'dateUpdated' => $menu['dateUpdated'],
            'dateDeleted' => null,
            'deletedWithOwner' => null,
            'uid' => $menu['uid'],
        ])->execute();
    }

    private static function _ensureMenuElementSites(int $menuId, array $menu): void
    {
        $db = Craft::$app->getDb();

        if (!$db->tableExists('{{%navigation_menus_sites}}')) {
            return;
        }

        $siteRows = (new Query())
            ->select(['siteId', 'enabled'])
            ->from(['{{%navigation_menus_sites}}'])
            ->where(['menuId' => $menuId])
            ->all();

        foreach ($siteRows as $siteRow) {
            $exists = (new Query())
                ->from([Table::ELEMENTS_SITES])
                ->where(['elementId' => $menuId, 'siteId' => $siteRow['siteId']])
                ->exists();

            if ($exists) {
                continue;
            }

            $db->createCommand()->insert(Table::ELEMENTS_SITES, [
                'elementId' => $menuId,
                'siteId' => $siteRow['siteId'],
                'slug' => null,
                'uri' => null,
                'enabled' => (bool)$siteRow['enabled'],
                'dateCreated' => $menu['dateCreated'] ?? Db::prepareDateForDb(new \DateTime()),
                'dateUpdated' => $menu['dateUpdated'] ?? Db::prepareDateForDb(new \DateTime()),
                'uid' => StringHelper::UUID(),
            ])->execute();
        }
    }

    private static function _restoreTitlesFromRevisions(int $canonicalId): void
    {
        $siteIds = (new Query())
            ->select(['siteId'])
            ->from([Table::ELEMENTS_SITES])
            ->where(['elementId' => $canonicalId])
            ->column();

        foreach ($siteIds as $siteId) {
            $siteId = (int)$siteId;

            $currentTitle = (new Query())
                ->select(['title'])
                ->from([Table::ELEMENTS_SITES])
                ->where(['elementId' => $canonicalId, 'siteId' => $siteId])
                ->scalar();

            if ($currentTitle !== null && $currentTitle !== '') {
                continue;
            }

            $revisionTitle = (new Query())
                ->select(['es.title'])
                ->from(['e' => Table::ELEMENTS])
                ->innerJoin(['es' => Table::ELEMENTS_SITES], '[[es.elementId]] = [[e.id]] AND [[es.siteId]] = :siteId', [
                    'siteId' => $siteId,
                ])
                ->where([
                    'and',
                    ['e.canonicalId' => $canonicalId],
                    ['not', ['e.revisionId' => null]],
                    ['not', ['es.title' => null]],
                    ['not', ['es.title' => '']],
                ])
                ->orderBy(['e.dateCreated' => SORT_DESC])
                ->scalar();

            if ($revisionTitle === null || $revisionTitle === false || $revisionTitle === '') {
                continue;
            }

            Db::update(Table::ELEMENTS_SITES, [
                'title' => $revisionTitle,
            ], [
                'elementId' => $canonicalId,
                'siteId' => $siteId,
            ], updateTimestamp: false);
        }
    }

    private static function _restoreLinkedNodeTitlesFromEntries(): int
    {
        if (!Craft::$app->getDb()->tableExists('{{%navigation_nodes}}')) {
            return 0;
        }

        $rows = (new Query())
            ->select([
                'nodeEsId' => 'node_es.id',
                'entryTitle' => 'entry_es.title',
            ])
            ->from(['node_es' => Table::ELEMENTS_SITES])
            ->innerJoin(['n' => '{{%navigation_nodes}}'], '[[n.id]] = [[node_es.elementId]]')
            ->innerJoin(['entry_es' => Table::ELEMENTS_SITES], [
                'and',
                '[[entry_es.elementId]] = [[n.elementId]]',
                '[[entry_es.siteId]] = [[node_es.siteId]]',
            ])
            ->where([
                'and',
                ['not', ['n.elementId' => null]],
                [
                    'or',
                    ['node_es.title' => null],
                    ['node_es.title' => ''],
                ],
                ['not', ['entry_es.title' => null]],
                ['not', ['entry_es.title' => '']],
            ])
            ->all();

        foreach ($rows as $row) {
            Db::update(Table::ELEMENTS_SITES, [
                'title' => $row['entryTitle'],
            ], ['id' => $row['nodeEsId']], updateTimestamp: false);
        }

        return count($rows);
    }

    private static function _menuOwnsExclusiveElement(int $id): bool
    {
        $type = (new Query())
            ->select(['type'])
            ->from([Table::ELEMENTS])
            ->where(['id' => $id])
            ->scalar();

        if ($type !== Menu::class) {
            return false;
        }

        return self::_foreignElementType($id) === null;
    }

    private static function _foreignElementType(int $id): ?string
    {
        if (self::_rowExists('{{%entries}}', $id)) {
            return Entry::class;
        }

        if (self::_rowExists('{{%users}}', $id)) {
            return User::class;
        }

        if (self::_rowExists('{{%categories}}', $id)) {
            return Category::class;
        }

        if (self::_rowExists('{{%assets}}', $id)) {
            return Asset::class;
        }

        if (self::_rowExists('{{%navigation_nodes}}', $id)) {
            return Node::class;
        }

        if (Craft::$app->getDb()->tableExists('{{%commerce_products}}') && self::_rowExists('{{%commerce_products}}', $id)) {
            return 'craft\\commerce\\elements\\Product';
        }

        return null;
    }

    private static function _fieldLayoutIdForForeignElement(int $id, string $type): ?int
    {
        if ($type === Entry::class) {
            $layoutId = (new Query())
                ->select(['et.fieldLayoutId'])
                ->from(['en' => '{{%entries}}'])
                ->innerJoin(['et' => '{{%entrytypes}}'], '[[et.id]] = [[en.typeId]]')
                ->where(['en.id' => $id])
                ->scalar();

            return $layoutId !== null && $layoutId !== false ? (int)$layoutId : null;
        }

        if ($type === Node::class) {
            $layoutId = (new Query())
                ->select(['m.fieldLayoutId'])
                ->from(['n' => '{{%navigation_nodes}}'])
                ->innerJoin(['m' => '{{%navigation_menus}}'], '[[m.id]] = [[n.menuId]]')
                ->where(['n.id' => $id])
                ->scalar();

            return $layoutId !== null && $layoutId !== false ? (int)$layoutId : null;
        }

        return null;
    }

    private static function _rowExists(string $table, int $id): bool
    {
        if (!Craft::$app->getDb()->tableExists($table)) {
            return false;
        }

        return (new Query())
            ->from([$table])
            ->where(['id' => $id])
            ->exists();
    }
}
