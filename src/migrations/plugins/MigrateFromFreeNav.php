<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\helpers\PluginMigrationHelper;

use Craft;
use craft\db\Query;
use craft\helpers\Json;

class MigrateFromFreeNav extends BasePluginMigrator
{
    // Static Methods
    // =========================================================================

    public static function sourceLabel(): string
    {
        return 'FreeNav';
    }

    public static function pluginHandle(): string
    {
        return 'free-nav';
    }

    public static function sourceTable(): string
    {
        return 'freenav_menus';
    }

    public static function getMenus(): array
    {
        if (!PluginMigrationHelper::tableExists('freenav_menus')) {
            return [];
        }

        $menus = (new Query())
            ->select(['handle', 'name'])
            ->from(['{{%freenav_menus}}'])
            ->where(['dateDeleted' => null])
            ->orderBy(['sortOrder' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        return array_map(static fn(array $menu) => [
            'handle' => (string)$menu['handle'],
            'label' => (string)$menu['name'],
        ], $menus);
    }


    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!PluginMigrationHelper::tableExists('freenav_menus')) {
            $this->error('FreeNav tables not found. Is the plugin installed?');

            return false;
        }

        $menus = (new Query())
            ->select(['*'])
            ->from(['{{%freenav_menus}}'])
            ->where(['dateDeleted' => null])
            ->orderBy(['sortOrder' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if ($menus === []) {
            $this->info('No FreeNav menus found to migrate.');

            return true;
        }

        $handleFilter = $this->handlesFilter();

        if ($handleFilter === null) {
            $this->info('Found ' . count($menus) . ' FreeNav menu(s) to migrate.');
        }

        $ok = true;

        foreach ($menus as $menu) {
            if (!$this->matchesHandle($menu['handle'], $handleFilter)) {
                continue;
            }

            $payload = $this->_buildPayload($menu);

            if (!$this->importExportPayload($payload, $menu['name'])) {
                $ok = false;
            }
        }

        return $ok;
    }


    // Private Methods
    // =========================================================================

    private function _buildPayload(array $menu): array
    {
        $siteSettings = (new Query())
            ->select(['*'])
            ->from(['{{%freenav_menu_sites}}'])
            ->where(['menuId' => $menu['id']])
            ->all();

        if ($siteSettings === []) {
            $enabledSites = PluginMigrationHelper::allSitesEnabled();
        } else {
            $enabledSites = [];

            foreach ($siteSettings as $row) {
                $enabledSites[] = [
                    'siteId' => (int)$row['siteId'],
                    'enabled' => (bool)$row['enabled'],
                ];
            }
        }

        $menuData = [
            'name' => $menu['name'],
            'handle' => $menu['handle'],
            'instructions' => $menu['instructions'] ?? null,
            'propagationMethod' => $menu['propagationMethod'] ?? 'all',
            'maxNodes' => $menu['maxNodes'] ?? null,
            'maxLevels' => $menu['maxLevels'] ?? null,
            'defaultPlacement' => $menu['defaultPlacement'] ?? 'end',
            'siteSettings' => PluginMigrationHelper::exportSiteSettings($enabledSites),
        ];

        $nodes = $this->_buildNodeTree((int)$menu['id'], (int)$menu['structureId']);

        return PluginMigrationHelper::baseExportPayload($menuData, $nodes);
    }

    private function _buildNodeTree(int $menuId, int $structureId): array
    {
        $rows = (new Query())
            ->select([
                'n.id',
                'n.parentId',
                'n.linkedElementId',
                'n.nodeType',
                'n.url',
                'n.classes',
                'n.urlSuffix',
                'n.customAttributes',
                'n.data',
                'n.newWindow',
                'n.icon',
                'n.badge',
                'n.visibilityRules',
                'es.title',
                'e.enabled',
                'se.level',
                'se.lft',
            ])
            ->from(['n' => '{{%freenav_nodes}}'])
            ->innerJoin(['e' => '{{%elements}}'], '[[e.id]] = [[n.id]]')
            ->innerJoin(['es' => '{{%elements_sites}}'], [
                'and',
                '[[es.elementId]] = [[n.id]]',
                ['es.siteId' => Craft::$app->getSites()->getPrimarySite()->id],
            ])
            ->leftJoin(['se' => '{{%structureelements}}'], [
                'and',
                '[[se.elementId]] = [[n.id]]',
                ['se.structureId' => $structureId],
            ])
            ->where([
                'n.menuId' => $menuId,
                'e.dateDeleted' => null,
            ])
            ->orderBy(['se.lft' => SORT_ASC, 'n.id' => SORT_ASC])
            ->all();

        if ($rows === []) {
            return [];
        }

        $deduped = [];

        foreach ($rows as $row) {
            $deduped[(int)$row['id']] = $row;
        }

        $flat = array_values($deduped);

        foreach ($flat as $row) {
            if (!empty($row['icon']) || !empty($row['badge'])) {
                $this->warning('FreeNav icon/badge fields are not migrated — map them to node custom fields manually.', 1);
                break;
            }
        }

        return PluginMigrationHelper::buildNestedTree($flat, function(array $row): array {
            $type = PluginMigrationHelper::mapFreeNavNodeType($row['nodeType'] ?? null);

            if (!$type) {
                $this->warning("Unknown FreeNav node type “{$row['nodeType']}”; using Custom URL.", 1);
                $type = \verbb\navigation\nodetypes\Custom::class;
            }

            if (!empty($row['visibilityRules'])) {
                $this->warning('FreeNav visibility rules are stored in node data but not enforced by Navigation.', 1);
            }

            [$linkedElementUid, $linkedElementType] = PluginMigrationHelper::resolveLinkedElement(
                !empty($row['linkedElementId']) ? (int)$row['linkedElementId'] : null,
            );

            $data = [];

            if (!empty($row['data'])) {
                try {
                    $data = Json::decodeIfJson($row['data']) ?? [];
                } catch (\Throwable) {
                    $data = [];
                }
            }

            if (!empty($row['visibilityRules'])) {
                $data['migratedFreeNavVisibilityRules'] = $row['visibilityRules'];
            }

            $node = [
                'title' => $row['title'] ?? '',
                'type' => $type,
                'classes' => $row['classes'] ?? null,
                'urlSuffix' => $row['urlSuffix'] ?? null,
                'newWindow' => (bool)($row['newWindow'] ?? false),
                'customAttributes' => PluginMigrationHelper::parseCustomAttributes($row['customAttributes'] ?? null),
                'data' => is_array($data) ? $data : [],
                'enabled' => (bool)($row['enabled'] ?? true),
                'enabledForSite' => true,
            ];

            if ($linkedElementUid && $linkedElementType) {
                $node['linkedElementUid'] = $linkedElementUid;
                $node['linkedElementType'] = $linkedElementType;
            } elseif (!empty($row['url'])) {
                $node['url'] = $row['url'];
            }

            return PluginMigrationHelper::finalizeNode($node, fn(string $warning) => $this->warning($warning, 1));
        }, 'id', 'parentId');
    }
}
