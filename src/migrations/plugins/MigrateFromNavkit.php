<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\nodetypes\Custom;

use Craft;
use craft\db\Query;
use craft\helpers\Json;

class MigrateFromNavkit extends BasePluginMigrator
{
    // Static Methods
    // =========================================================================

    public static function sourceLabel(): string
    {
        return 'Navkit';
    }

    public static function pluginHandle(): string
    {
        return 'navkit';
    }

    public static function sourceTable(): string
    {
        return 'navkit_menus';
    }

    public static function getMenus(): array
    {
        if (!PluginMigrationHelper::tableExists('navkit_menus')) {
            return [];
        }

        $menus = (new Query())
            ->select(['handle', 'name'])
            ->from(['{{%navkit_menus}}'])
            ->orderBy(['name' => SORT_ASC, 'id' => SORT_ASC])
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
        if (!PluginMigrationHelper::tableExists('navkit_menus')) {
            $this->error('Navkit tables not found. Is the plugin installed?');

            return false;
        }

        $menus = (new Query())
            ->select(['*'])
            ->from(['{{%navkit_menus}}'])
            ->orderBy(['name' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if ($menus === []) {
            $this->info('No Navkit menus found to migrate.');

            return true;
        }

        $handleFilter = $this->handlesFilter();

        if ($handleFilter === null) {
            $this->info('Found ' . count($menus) . ' Navkit menu(s) to migrate.');
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
        $settings = Json::decodeIfJson($menu['settings'] ?? null) ?? [];

        $menuData = [
            'name' => $menu['name'],
            'handle' => $menu['handle'],
            'propagationMethod' => $settings['propagationMethod'] ?? 'all',
            'maxLevels' => $settings['maxLevels'] ?? null,
            'siteSettings' => $this->_exportNavkitSiteSettings($settings['siteSettings'] ?? []),
        ];

        $nodes = $this->_buildNodeTree((int)$menu['id'], (int)$menu['structureId']);

        return PluginMigrationHelper::baseExportPayload($menuData, $nodes);
    }

    private function _exportNavkitSiteSettings(array $siteSettings): array
    {
        if ($siteSettings === []) {
            return PluginMigrationHelper::exportSiteSettings(PluginMigrationHelper::allSitesEnabled());
        }

        $enabledSites = [];

        foreach ($siteSettings as $siteUid => $settings) {
            $site = Craft::$app->getSites()->getSiteByUid((string)$siteUid);

            if (!$site) {
                continue;
            }

            $enabledSites[] = [
                'siteId' => (int)$site->id,
                'enabled' => (bool)($settings['enabledByDefault'] ?? true),
            ];
        }

        if ($enabledSites === []) {
            return PluginMigrationHelper::exportSiteSettings(PluginMigrationHelper::allSitesEnabled());
        }

        return PluginMigrationHelper::exportSiteSettings($enabledSites);
    }

    private function _buildNodeTree(int $menuId, int $structureId): array
    {
        $primarySiteId = (int)Craft::$app->getSites()->getPrimarySite()->id;

        $rows = (new Query())
            ->select([
                'n.id',
                'n.type',
                'n.url',
                'n.linkedElementId',
                'n.linkedSiteId',
                'n.target',
                'n.classes',
                'n.rel',
                'n.data',
                'es.title',
                'e.enabled',
                'se.level',
                'se.lft',
                'se.rgt',
            ])
            ->from(['n' => '{{%navkit_nodes}}'])
            ->innerJoin(['e' => '{{%elements}}'], '[[e.id]] = [[n.id]]')
            ->innerJoin(['es' => '{{%elements_sites}}'], [
                'and',
                '[[es.elementId]] = [[n.id]]',
                ['es.siteId' => $primarySiteId],
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

        $flat = PluginMigrationHelper::attachStructureParents($rows);

        return PluginMigrationHelper::buildNestedTree($flat, function(array $row) use ($primarySiteId): array {
            $type = PluginMigrationHelper::mapNavkitNodeType($row['type'] ?? null);

            if (!$type) {
                $this->warning("Unknown Navkit link type “{$row['type']}”; using Custom URL.", 1);
                $type = Custom::class;
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

            $customAttributes = [];

            if (!empty($row['rel'])) {
                $customAttributes[] = [
                    'attribute' => 'rel',
                    'value' => (string)$row['rel'],
                ];
            }

            $node = [
                'title' => $row['title'] ?? '',
                'type' => $type,
                'classes' => $row['classes'] ?? null,
                'newWindow' => ($row['target'] ?? null) === '_blank',
                'customAttributes' => $customAttributes,
                'data' => is_array($data) ? $data : [],
                'enabled' => (bool)($row['enabled'] ?? true),
                'enabledForSite' => true,
            ];

            if ($linkedElementUid && $linkedElementType) {
                $node['linkedElementUid'] = $linkedElementUid;
                $node['linkedElementType'] = $linkedElementType;

                if (!empty($row['linkedSiteId'])) {
                    $linkedSite = Craft::$app->getSites()->getSiteById((int)$row['linkedSiteId']);

                    if ($linkedSite) {
                        $node['linkedElementSiteHandle'] = $linkedSite->handle;
                    }
                }
            } elseif (!empty($row['url'])) {
                $node['url'] = $row['url'];
            }

            $fieldValues = PluginMigrationHelper::loadNavkitNodeFieldValues((int)$row['id'], $primarySiteId);

            if ($fieldValues !== []) {
                $node['fieldValues'] = $fieldValues;
            }

            return PluginMigrationHelper::finalizeNode($node, fn(string $warning) => $this->warning($warning, 1));
        });
    }
}
