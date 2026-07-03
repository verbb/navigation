<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\helpers\PluginMigrationHelper;

use Craft;
use craft\db\Query;

class MigrateFromOlivemenus extends BasePluginMigrator
{
    // Static Methods
    // =========================================================================

    public static function sourceLabel(): string
    {
        return 'Olivemenus';
    }

    public static function pluginHandle(): string
    {
        return 'olivemenus';
    }

    public static function sourceTable(): string
    {
        return 'olivemenus';
    }

    public static function getMenus(): array
    {
        if (!PluginMigrationHelper::tableExists('olivemenus')) {
            return [];
        }

        $menus = (new Query())
            ->select(['handle', 'name'])
            ->from(['{{%olivemenus}}'])
            ->orderBy(['id' => SORT_ASC])
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
        if (!PluginMigrationHelper::tableExists('olivemenus')) {
            $this->error('Olivemenus tables not found. Is the plugin installed?');

            return false;
        }

        $menus = (new Query())
            ->select(['*'])
            ->from(['{{%olivemenus}}'])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        if ($menus === []) {
            $this->info('No Olivemenus menus found to migrate.');

            return true;
        }

        $handleFilter = $this->handlesFilter();

        if ($handleFilter === null) {
            $this->info('Found ' . count($menus) . ' Olivemenus menu(s) to migrate.');
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
        $siteId = !empty($menu['site_id'])
            ? (int)$menu['site_id']
            : (int)Craft::$app->getSites()->getPrimarySite()->id;

        $site = Craft::$app->getSites()->getSiteById($siteId);

        if (!empty($menu['class_parent'])) {
            $this->warning('Olivemenus class_parent values are not migrated.', 1);
        }

        $menuData = [
            'name' => $menu['name'],
            'handle' => $menu['handle'],
            'instructions' => null,
            'propagationMethod' => 'none',
            'defaultPlacement' => 'end',
            'siteSettings' => PluginMigrationHelper::exportSiteSettings(
                PluginMigrationHelper::singleSiteEnabled($siteId),
            ),
        ];

        $items = (new Query())
            ->select(['*'])
            ->from(['{{%olivemenus_items}}'])
            ->where(['menu_id' => $menu['id']])
            ->orderBy(['item_order' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $nodes = PluginMigrationHelper::buildNestedTreeFromParentId(
            $items,
            function(array $row): array {
                $type = !empty($row['entry_id'])
                    ? \verbb\navigation\nodetypes\Entry::class
                    : \verbb\navigation\nodetypes\Custom::class;

                [$linkedElementUid, $linkedElementType] = PluginMigrationHelper::resolveLinkedElement(
                    !empty($row['entry_id']) ? (int)$row['entry_id'] : null,
                    \craft\elements\Entry::class,
                );

                $node = [
                    'title' => $row['name'] ?? '',
                    'type' => $type,
                    'classes' => $row['class'] ?? null,
                    'newWindow' => ($row['target'] ?? '') === '_blank',
                    'customAttributes' => PluginMigrationHelper::parseCustomAttributes($row['data_json'] ?? null),
                    'data' => [],
                    'enabled' => true,
                    'enabledForSite' => true,
                ];

                if ($linkedElementUid && $linkedElementType) {
                    $node['linkedElementUid'] = $linkedElementUid;
                    $node['linkedElementType'] = $linkedElementType;
                } elseif (!empty($row['custom_url'])) {
                    $node['url'] = $row['custom_url'];
                }

                return PluginMigrationHelper::finalizeNode($node, fn(string $warning) => $this->warning($warning, 1));
            },
            'id',
            'parent_id',
            0,
        );

        return PluginMigrationHelper::baseExportPayload(
            $menuData,
            $nodes,
            $site?->handle,
        );
    }
}
