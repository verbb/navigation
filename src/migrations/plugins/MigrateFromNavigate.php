<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\helpers\PluginMigrationHelper;

use Craft;
use craft\db\Query;

class MigrateFromNavigate extends BasePluginMigrator
{
    // Static Methods
    // =========================================================================

    public static function sourceLabel(): string
    {
        return 'Navigate';
    }

    public static function pluginHandle(): string
    {
        return 'navigate';
    }

    public static function sourceTable(): string
    {
        return 'navigate_navigations';
    }

    public static function getMenus(): array
    {
        if (!PluginMigrationHelper::tableExists('navigate_navigations')) {
            return [];
        }

        $navs = (new Query())
            ->select(['handle', 'title'])
            ->from(['{{%navigate_navigations}}'])
            ->where(['dateDeleted' => null])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return array_map(static fn(array $nav) => [
            'handle' => (string)$nav['handle'],
            'label' => (string)$nav['title'],
        ], $navs);
    }


    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!PluginMigrationHelper::tableExists('navigate_navigations')) {
            $this->error('Navigate tables not found. Is the plugin installed?');

            return false;
        }

        $navs = (new Query())
            ->select(['*'])
            ->from(['{{%navigate_navigations}}'])
            ->where(['dateDeleted' => null])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if ($navs === []) {
            $this->info('No Navigate menus found to migrate.');

            return true;
        }

        $handleFilter = $this->handlesFilter();

        if ($handleFilter === null) {
            $this->info('Found ' . count($navs) . ' Navigate menu(s) to migrate.');
        }

        $ok = true;

        foreach ($navs as $nav) {
            if (!$this->matchesHandle($nav['handle'], $handleFilter)) {
                continue;
            }

            $payload = $this->_buildPayload($nav);

            if (!$this->importExportPayload($payload, $nav['title'])) {
                $ok = false;
            }
        }

        return $ok;
    }


    // Private Methods
    // =========================================================================

    private function _buildPayload(array $nav): array
    {
        $siteId = $this->_resolvePrimarySiteId((int)$nav['id']);
        $site = Craft::$app->getSites()->getSiteById($siteId);
        $otherSiteCount = (new Query())
            ->select(['siteId'])
            ->from(['{{%navigate_nodes}}'])
            ->where(['navId' => $nav['id']])
            ->andWhere(['!=', 'siteId', $siteId])
            ->distinct()
            ->count();

        if ($otherSiteCount > 0) {
            $this->warning(
                "Navigate menu “{$nav['handle']}” has nodes in other sites — only the primary site ({$site?->handle}) tree is migrated.",
                1,
            );
        }

        $menuData = [
            'name' => $nav['title'],
            'handle' => $nav['handle'],
            'instructions' => null,
            'propagationMethod' => 'all',
            'maxLevels' => !empty($nav['levels']) ? (int)$nav['levels'] : null,
            'defaultPlacement' => 'end',
            'siteSettings' => PluginMigrationHelper::exportSiteSettings(PluginMigrationHelper::allSitesEnabled()),
        ];

        $nodes = $this->_buildNodeTree((int)$nav['id'], $siteId);

        return PluginMigrationHelper::baseExportPayload(
            $menuData,
            $nodes,
            $site?->handle,
        );
    }

    private function _resolvePrimarySiteId(int $navId): int
    {
        $siteId = (new Query())
            ->select(['siteId'])
            ->from(['{{%navigate_nodes}}'])
            ->where(['navId' => $navId])
            ->orderBy(['siteId' => SORT_ASC])
            ->scalar();

        return (int)($siteId ?: Craft::$app->getSites()->getPrimarySite()->id);
    }

    private function _buildNodeTree(int $navId, int $siteId): array
    {
        $rows = (new Query())
            ->select(['*'])
            ->from(['{{%navigate_nodes}}'])
            ->where([
                'navId' => $navId,
                'siteId' => $siteId,
            ])
            ->orderBy(['order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if ($rows === []) {
            return [];
        }

        $normalized = array_map(static function(array $row): array {
            $parent = (int)($row['parent'] ?? 0);

            return [
                'id' => (int)$row['id'],
                'parentId' => $parent > 0 ? $parent : null,
                'row' => $row,
            ];
        }, $rows);

        return PluginMigrationHelper::buildNestedTree($normalized, function(array $item): array {
            $row = $item['row'];
            $type = PluginMigrationHelper::resolveNavigateNodeType($row['type'] ?? null, $row['elementType'] ?? null);

            if (!$type) {
                $this->warning("Unknown Navigate node type “{$row['type']}”; using Custom URL.", 1);
                $type = \verbb\navigation\nodetypes\Custom::class;
            }

            [$linkedElementUid, $linkedElementType] = PluginMigrationHelper::resolveLinkedElement(
                !empty($row['elementId']) ? (int)$row['elementId'] : null,
                !empty($row['elementType']) ? (string)$row['elementType'] : null,
            );

            $node = [
                'title' => $row['name'] ?? '',
                'type' => $type,
                'classes' => $row['classes'] ?? null,
                'newWindow' => (bool)($row['blank'] ?? false),
                'customAttributes' => [],
                'data' => [],
                'enabled' => (bool)($row['enabled'] ?? true),
                'enabledForSite' => (bool)($row['enabled'] ?? true),
            ];

            if ($linkedElementUid && $linkedElementType) {
                $node['linkedElementUid'] = $linkedElementUid;
                $node['linkedElementType'] = $linkedElementType;
            } elseif (!empty($row['url'])) {
                $node['url'] = $row['url'];
            }

            return PluginMigrationHelper::finalizeNode($node, fn(string $warning) => $this->warning($warning, 1));
        });
    }
}
