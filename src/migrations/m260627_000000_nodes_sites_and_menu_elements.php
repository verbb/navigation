<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Menu;
use verbb\navigation\helpers\NodeTypeHelper;
use verbb\navigation\helpers\ProjectConfigData;

use Craft;
use craft\db\Migration;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\StringHelper;

class m260627_000000_nodes_sites_and_menu_elements extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        // Per-site node link settings
        if (!$this->db->tableExists('{{%navigation_nodes_sites}}')) {
            $this->createTable('{{%navigation_nodes_sites}}', [
                'id' => $this->primaryKey(),
                'nodeId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->notNull(),
                'linkedElementSiteId' => $this->integer()->null(),
                'url' => $this->text()->null(),
                'urlSuffix' => $this->string(255)->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, '{{%navigation_nodes_sites}}', ['nodeId', 'siteId'], true);
            $this->createIndex(null, '{{%navigation_nodes_sites}}', ['siteId'], false);
            $this->addForeignKey(null, '{{%navigation_nodes_sites}}', ['nodeId'], '{{%elements}}', ['id'], 'CASCADE', null);
            $this->addForeignKey(null, '{{%navigation_nodes_sites}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        }

        $this->_migrateNodeSiteRows();
        $this->_migrateNodeTypeClasses();
        $this->_migrateMenuTypePermissions();
        $this->_migrateMenuTypePermissionsProjectConfig();
        $this->_clearNodeSlugHack();
        $this->_migrateMenuElements();

        $menuTable = null;

        if ($this->db->tableExists('{{%navigation_menus}}')) {
            $menuTable = '{{%navigation_menus}}';
        } elseif ($this->db->tableExists('{{%navigation_navs}}')) {
            $menuTable = '{{%navigation_navs}}';
        }

        if ($menuTable && !$this->db->columnExists($menuTable, 'menuFieldLayoutId')) {
            $this->addColumn($menuTable, 'menuFieldLayoutId', $this->integer()->null()->after('fieldLayoutId'));
            $this->addForeignKey(null, $menuTable, ['menuFieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);
        }

        return true;
    }

    public function safeDown(): bool
    {
        $menuTable = null;

        if ($this->db->tableExists('{{%navigation_menus}}')) {
            $menuTable = '{{%navigation_menus}}';
        } elseif ($this->db->tableExists('{{%navigation_navs}}')) {
            $menuTable = '{{%navigation_navs}}';
        }

        if ($menuTable && $this->db->columnExists($menuTable, 'menuFieldLayoutId')) {
            $this->dropForeignKey(null, $menuTable, ['menuFieldLayoutId']);
            $this->dropColumn($menuTable, 'menuFieldLayoutId');
        }

        $this->dropTableIfExists('{{%navigation_nodes_sites}}');

        return true;
    }


    // Private Methods
    // =========================================================================

    private function _migrateNodeSiteRows(): void
    {
        if (!$this->db->tableExists('{{%navigation_nodes_sites}}')) {
            return;
        }

        $existing = (new \craft\db\Query())
            ->from(['{{%navigation_nodes_sites}}'])
            ->exists($this->db);

        if ($existing) {
            return;
        }

        $nodeSiteRows = (new \craft\db\Query())
            ->select([
                'es.elementId AS nodeId',
                'es.siteId AS siteId',
                'es.slug AS slug',
                'n.url AS url',
                'n.urlSuffix AS urlSuffix',
            ])
            ->from(['es' => '{{%elements_sites}}'])
            ->innerJoin(['n' => '{{%navigation_nodes}}'], '[[n.id]] = [[es.elementId]]')
            ->innerJoin(['e' => '{{%elements}}'], '[[e.id]] = [[n.id]]')
            ->all($this->db);

        $now = Db::prepareDateForDb(new \DateTime());

        foreach ($nodeSiteRows as $row) {
            $linkedElementSiteId = $row['siteId'];

            if ($row['slug'] !== null && $row['slug'] !== '' && ctype_digit((string)$row['slug'])) {
                $linkedElementSiteId = (int)$row['slug'];
            }

            $this->insert('{{%navigation_nodes_sites}}', [
                'nodeId' => $row['nodeId'],
                'siteId' => $row['siteId'],
                'linkedElementSiteId' => $linkedElementSiteId,
                'url' => $row['url'],
                'urlSuffix' => $row['urlSuffix'],
                'dateCreated' => $now,
                'dateUpdated' => $now,
                'uid' => StringHelper::UUID(),
            ]);
        }
    }

    private function _migrateNodeTypeClasses(): void
    {
        foreach (NodeTypeHelper::legacyElementTypeMap() as $legacy => $nodeType) {
            $this->update('{{%navigation_nodes}}', ['type' => $nodeType], ['type' => $legacy]);
        }

        foreach (NodeTypeHelper::legacyV3NodeTypeClassMap() as $legacy => $nodeType) {
            $this->update('{{%navigation_nodes}}', ['type' => $nodeType], ['type' => $legacy]);
        }
    }

    private function _migrateMenuTypePermissions(): void
    {
        $table = null;

        if ($this->db->tableExists('{{%navigation_menus}}')) {
            $table = '{{%navigation_menus}}';
        } elseif ($this->db->tableExists('{{%navigation_navs}}')) {
            $table = '{{%navigation_navs}}';
        }

        if (!$table || !$this->db->columnExists($table, 'permissions')) {
            return;
        }

        $rows = (new \craft\db\Query())
            ->select(['id', 'permissions'])
            ->from([$table])
            ->all($this->db);

        foreach ($rows as $row) {
            if (empty($row['permissions'])) {
                continue;
            }

            $permissions = Json::decode($row['permissions']);

            if (!is_array($permissions)) {
                continue;
            }

            $this->update($table, [
                'permissions' => Json::encode(NodeTypeHelper::resolvePermissionsTypeKeys($permissions)),
            ], ['id' => $row['id']], [], false);
        }
    }

    private function _migrateMenuTypePermissionsProjectConfig(): void
    {
        if (!ProjectConfigData::canUpdateProjectConfig()) {
            return;
        }

        $projectConfig = Craft::$app->getProjectConfig();

        foreach (['navigation.menus', 'navigation.navs'] as $path) {
            $menus = $projectConfig->get($path);

            if (!$menus || !is_array($menus)) {
                continue;
            }

            $changed = false;

            foreach ($menus as $uid => $menu) {
                if (empty($menu['permissions']) || !is_array($menu['permissions'])) {
                    continue;
                }

                $resolved = NodeTypeHelper::resolvePermissionsTypeKeys($menu['permissions']);

                if ($resolved !== $menu['permissions']) {
                    $menus[$uid]['permissions'] = $resolved;
                    $changed = true;
                }
            }

            if ($changed) {
                $projectConfig->set($path, $menus, 'Migrate menu node type permission keys');
            }
        }
    }

    private function _clearNodeSlugHack(): void
    {
        $nodeIds = (new \craft\db\Query())
            ->select('id')
            ->from(['{{%navigation_nodes}}'])
            ->column($this->db);

        if (!$nodeIds) {
            return;
        }

        Db::update('{{%elements_sites}}', ['slug' => null], [
            'and',
            ['elementId' => $nodeIds],
            '[[slug]] REGEXP \'^[0-9]+$\'',
        ]);
    }

    private function _migrateMenuElements(): void
    {
        $tables = $this->_resolveMenuTables();

        if (!$tables) {
            return;
        }

        $navs = (new \craft\db\Query())
            ->select(['id', 'uid', 'name', 'dateCreated', 'dateUpdated'])
            ->from([$tables['menu']])
            ->where(['dateDeleted' => null])
            ->all($this->db);

        if ($navs === []) {
            return;
        }

        foreach ($navs as $nav) {
            $exists = (new \craft\db\Query())
                ->from(['{{%elements}}'])
                ->where(['id' => $nav['id']])
                ->exists($this->db);

            if (!$exists) {
                $this->insert('{{%elements}}', [
                    'id' => $nav['id'],
                    'canonicalId' => $nav['id'],
                    'draftId' => null,
                    'revisionId' => null,
                    'fieldLayoutId' => null,
                    'type' => Menu::class,
                    'enabled' => true,
                    'archived' => false,
                    'dateCreated' => $nav['dateCreated'],
                    'dateUpdated' => $nav['dateUpdated'],
                    'dateDeleted' => null,
                    'deletedWithOwner' => null,
                    'uid' => $nav['uid'],
                ]);
            }
            // Never reclaim an existing elements.id (entries/users/nodes/…). Colliding menus
            // are remapped via `php craft navigation/menus/fix-menu-element-collisions`.

            if (!$tables['sites']) {
                continue;
            }

            $siteRows = (new \craft\db\Query())
                ->select(['siteId', 'enabled'])
                ->from([$tables['sites']])
                ->where([$tables['sitesMenuIdColumn'] => $nav['id']])
                ->all($this->db);

            foreach ($siteRows as $siteRow) {
                $siteRowExists = (new \craft\db\Query())
                    ->from(['{{%elements_sites}}'])
                    ->where(['elementId' => $nav['id'], 'siteId' => $siteRow['siteId']])
                    ->exists($this->db);

                if ($siteRowExists) {
                    continue;
                }

                $this->insert('{{%elements_sites}}', [
                    'elementId' => $nav['id'],
                    'siteId' => $siteRow['siteId'],
                    'slug' => null,
                    'uri' => null,
                    'enabled' => (bool)$siteRow['enabled'],
                    'dateCreated' => $nav['dateCreated'],
                    'dateUpdated' => $nav['dateUpdated'],
                    'uid' => StringHelper::UUID(),
                ]);
            }
        }
    }

    private function _resolveMenuTables(): ?array
    {
        if ($this->db->tableExists('{{%navigation_menus}}')) {
            $sitesTable = '{{%navigation_menus_sites}}';
            $hasSites = $this->db->tableExists($sitesTable);

            return [
                'menu' => '{{%navigation_menus}}',
                'sites' => $hasSites ? $sitesTable : null,
                'sitesMenuIdColumn' => ($hasSites && $this->db->columnExists($sitesTable, 'menuId')) ? 'menuId' : 'navId',
            ];
        }

        if ($this->db->tableExists('{{%navigation_navs}}')) {
            $sitesTable = '{{%navigation_navs_sites}}';
            $hasSites = $this->db->tableExists($sitesTable);

            return [
                'menu' => '{{%navigation_navs}}',
                'sites' => $hasSites ? $sitesTable : null,
                'sitesMenuIdColumn' => 'navId',
            ];
        }

        return null;
    }
}
