<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\helpers\PluginMigrationHelper;

use Craft;
use craft\db\Query;
use craft\helpers\Json;

class MigrateFromTkaNavigation extends BasePluginMigrator
{
    // Static Methods
    // =========================================================================

    public static function sourceLabel(): string
    {
        return 'tka navigation';
    }

    public static function pluginHandle(): string
    {
        return 'tka-navigation';
    }

    public static function sourceTable(): string
    {
        return 'tka_navigations';
    }

    public static function getMenus(): array
    {
        return array_map(static fn(array $nav) => [
            'handle' => $nav['handle'],
            'label' => $nav['title'] ?? $nav['handle'],
        ], self::_findNavigations());
    }


    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!PluginMigrationHelper::tableExists('tka_navigations')) {
            $this->error('tka navigation tables not found. Install the plugin and run its migrations first.');

            return false;
        }

        $navs = self::_findNavigations();

        if ($navs === []) {
            $this->info('No tka navigation menus found to migrate.');

            return true;
        }

        $handleFilter = $this->handlesFilter();

        if ($handleFilter === null) {
            $this->info('Found ' . count($navs) . ' tka navigation menu(s) to migrate.');
        }

        $ok = true;

        foreach ($navs as $nav) {
            if (!$this->matchesHandle($nav['handle'], $handleFilter)) {
                continue;
            }

            $label = $nav['title'] ?? $nav['handle'];

            $payload = $this->_buildPayload($nav);

            if (!$this->importExportPayload($payload, $label)) {
                $ok = false;
            }
        }

        return $ok;
    }


    // Private Methods
    // =========================================================================

    private function _buildPayload(array $nav): array
    {
        $siteRows = (new Query())
            ->select(['*'])
            ->from(['{{%tka_navigations_sites}}'])
            ->where(['elementId' => $nav['id']])
            ->orderBy(['siteId' => SORT_ASC])
            ->all();

        if ($siteRows === []) {
            $this->warning('No site trees found for tka navigation “' . $nav['handle'] . '”.', 1);

            return PluginMigrationHelper::baseExportPayload([
                'name' => $nav['title'] ?? $nav['handle'],
                'handle' => $nav['handle'],
                'propagationMethod' => 'all',
                'defaultPlacement' => 'end',
                'siteSettings' => PluginMigrationHelper::exportSiteSettings(PluginMigrationHelper::allSitesEnabled()),
            ], []);
        }

        $primarySiteRow = $siteRows[0];
        $primarySite = Craft::$app->getSites()->getSiteById((int)$primarySiteRow['siteId']);

        if (count($siteRows) > 1) {
            $this->warning(
                'tka navigation stores per-site trees — only the first site tree is migrated; review other sites manually.',
                1,
            );
        }

        $nodesJson = $primarySiteRow['nodes'] ?? '[]';
        $nodesData = is_string($nodesJson) ? (Json::decodeIfJson($nodesJson) ?? []) : ($nodesJson ?? []);

        if (!is_array($nodesData)) {
            $nodesData = [];
        }

        $menuData = [
            'name' => $nav['title'] ?? $nav['handle'],
            'handle' => $nav['handle'],
            'instructions' => null,
            'propagationMethod' => 'all',
            'defaultPlacement' => 'end',
            'siteSettings' => PluginMigrationHelper::exportSiteSettings(PluginMigrationHelper::allSitesEnabled()),
        ];

        return PluginMigrationHelper::baseExportPayload(
            $menuData,
            $this->_buildNodeTree($nodesData),
            $primarySite?->handle,
        );
    }

    private function _buildNodeTree(array $nodes): array
    {
        $branch = [];

        foreach ($nodes as $node) {
            $branch[] = $this->_mapNode($node);
        }

        return $branch;
    }

    private function _mapNode(array $node): array
    {
        $type = PluginMigrationHelper::mapTkaNodeType($node['type'] ?? null);
        $children = !empty($node['children']) && is_array($node['children'])
            ? $this->_buildNodeTree($node['children'])
            : [];

        [$linkedElementUid, $linkedElementType] = PluginMigrationHelper::resolveLinkedElement(
            !empty($node['entryId']) ? (int)$node['entryId'] : null,
            \craft\elements\Entry::class,
        );

        $label = $node['customLabel'] ?? '';

        $mapped = [
            'title' => is_string($label) && $label !== '' ? $label : 'Untitled',
            'type' => $type,
            'classes' => $node['cssClass'] ?? null,
            'newWindow' => !empty($node['newTab']),
            'customAttributes' => [],
            'data' => [],
            'enabled' => true,
            'enabledForSite' => true,
            'children' => $children,
        ];

        if (($node['type'] ?? '') === 'anchor') {
            $anchor = $node['anchor'] ?? '';
            $mapped['urlSuffix'] = $anchor !== '' && !str_starts_with((string)$anchor, '#')
                ? '#' . $anchor
                : (string)$anchor;
        }

        if ($linkedElementUid && $linkedElementType) {
            $mapped['linkedElementUid'] = $linkedElementUid;
            $mapped['linkedElementType'] = $linkedElementType;
        } elseif (!empty($node['url'])) {
            $mapped['url'] = $node['url'];
        }

        return PluginMigrationHelper::finalizeNode($mapped, fn(string $warning) => $this->warning($warning, 1));
    }

    /**
     * tka navigation elements are localized — one row per site in `elements_sites`.
     * Query navigations once, then resolve titles from the primary site.
     */
    private static function _findNavigations(): array
    {
        if (!PluginMigrationHelper::tableExists('tka_navigations')) {
            return [];
        }

        $rows = (new Query())
            ->select(['n.id', 'n.handle'])
            ->from(['n' => '{{%tka_navigations}}'])
            ->innerJoin(['e' => '{{%elements}}'], '[[e.id]] = [[n.id]]')
            ->where(['e.dateDeleted' => null])
            ->orderBy(['n.id' => SORT_ASC])
            ->all();

        if ($rows === []) {
            return [];
        }

        $primarySiteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
        $titlesByElementId = (new Query())
            ->select(['elementId', 'title'])
            ->from(['{{%elements_sites}}'])
            ->where([
                'elementId' => array_column($rows, 'id'),
                'siteId' => $primarySiteId,
            ])
            ->indexBy('elementId')
            ->all();

        $navs = [];

        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $title = $titlesByElementId[$id]['title'] ?? null;

            $navs[] = [
                'id' => $id,
                'handle' => (string)$row['handle'],
                'title' => is_string($title) && $title !== '' ? $title : null,
            ];
        }

        return $navs;
    }
}
