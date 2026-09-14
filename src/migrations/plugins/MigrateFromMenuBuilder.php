<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\nodetypes\Asset;
use verbb\navigation\nodetypes\Category;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\nodetypes\Passive;

use Craft;
use craft\db\Query;
use craft\helpers\Json;

use Throwable;

class MigrateFromMenuBuilder extends BasePluginMigrator
{
    // Static Methods
    // =========================================================================

    public static function sourceLabel(): string
    {
        return 'MenuBuilder';
    }

    public static function pluginHandle(): string
    {
        return 'menu-builder';
    }

    public static function sourceTable(): string
    {
        return 'menubuilder_groups';
    }

    public static function getMenus(): array
    {
        if (!PluginMigrationHelper::tableExists('menubuilder_groups')) {
            return [];
        }

        $menus = (new Query())
            ->select(['handle', 'name'])
            ->from(['{{%menubuilder_groups}}'])
            ->orderBy(['sortOrder' => SORT_ASC, 'name' => SORT_ASC, 'id' => SORT_ASC])
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
        if (
            !PluginMigrationHelper::tableExists('menubuilder_groups') ||
            !PluginMigrationHelper::tableExists('menubuilder_items')
        ) {
            $this->error('MenuBuilder tables not found. Is the plugin installed?');

            return false;
        }

        $menus = (new Query())
            ->select(['*'])
            ->from(['{{%menubuilder_groups}}'])
            ->orderBy(['sortOrder' => SORT_ASC, 'name' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if ($menus === []) {
            $this->info('No MenuBuilder menus found to migrate.');

            return true;
        }

        $handleFilter = $this->handlesFilter();

        if ($handleFilter === null) {
            $this->info('Found ' . count($menus) . ' MenuBuilder menu(s) to migrate.');
        }

        $ok = true;

        foreach ($menus as $menu) {
            if (!$this->matchesHandle((string)$menu['handle'], $handleFilter)) {
                continue;
            }

            $payload = $this->_buildPayload($menu);

            if (!$this->importExportPayload($payload, (string)$menu['name'])) {
                $ok = false;
            }
        }

        return $ok;
    }


    // Private Methods
    // =========================================================================

    private function _buildPayload(array $menu): array
    {
        $settings = $this->_decodeBag($menu['settings'] ?? null);
        $siteIds = array_values(array_filter(
            array_map('intval', is_array($settings['siteIds'] ?? null) ? $settings['siteIds'] : []),
            static fn(int $siteId): bool => $siteId > 0,
        ));
        $menuEnabled = (bool)($menu['enabled'] ?? true);
        $siteSettings = [];
        $sourceSiteHandle = null;

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $enabled = $menuEnabled && ($siteIds === [] || in_array((int)$site->id, $siteIds, true));
            $siteSettings[] = [
                'siteId' => (int)$site->id,
                'enabled' => $enabled,
            ];

            if ($enabled && $sourceSiteHandle === null) {
                $sourceSiteHandle = $site->handle;
            }
        }

        $unusedSettings = $settings;
        unset($unusedSettings['siteIds']);

        if (
            !empty($menu['cssClass']) ||
            $this->_decodeBag($menu['htmlAttributes'] ?? null) !== [] ||
            $unusedSettings !== []
        ) {
            $this->warning('MenuBuilder menu presentation settings are not migrated; recreate them in Navigation templates.', 1);
        }

        if (!empty($menu['fieldLayoutId'])) {
            $this->warning('MenuBuilder item field layouts and custom field values are not migrated; configure matching Navigation node fields manually.', 1);
        }

        $menuData = [
            'name' => $menu['name'],
            'handle' => $menu['handle'],
            'instructions' => $menu['description'] ?? null,
            'propagationMethod' => 'all',
            'maxLevels' => !empty($menu['maxDepth']) ? (int)$menu['maxDepth'] : null,
            'siteSettings' => PluginMigrationHelper::exportSiteSettings($siteSettings),
        ];

        $nodes = $this->_buildNodeTree((int)$menu['id']);

        return PluginMigrationHelper::baseExportPayload($menuData, $nodes, $sourceSiteHandle);
    }

    private function _buildNodeTree(int $menuId): array
    {
        $rows = (new Query())
            ->select(['*'])
            ->from(['{{%menubuilder_items}}'])
            ->where(['groupId' => $menuId])
            ->orderBy(['sortOrder' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        if ($rows === []) {
            return [];
        }

        $this->_warnAboutUnsupportedNodeFeatures($rows);

        return PluginMigrationHelper::buildNestedTree($rows, function(array $row): array {
            $sourceType = strtolower(trim((string)($row['type'] ?? '')));
            $clickable = (bool)($row['clickable'] ?? true);

            if (!in_array($sourceType, ['entry', 'category', 'asset', 'url', 'anchor', 'nonclickable', 'separator', 'dynamic'], true)) {
                $this->warning("Unknown MenuBuilder item type “{$row['type']}”; using Custom URL.", 1);
            }

            $type = $this->_nodeType($sourceType, $clickable);
            $metadata = $this->_decodeBag($row['metadata'] ?? null);
            $data = $this->_preservedNodeData($row, $metadata);
            $url = null;

            if ($type === Dynamic::class) {
                $dynamicData = $this->_dynamicData($metadata['dynamicSource'] ?? null);

                if ($dynamicData === null) {
                    $this->warning("MenuBuilder dynamic item “{$row['title']}” has an unavailable source; converted to Passive.", 1);
                    $type = Passive::class;
                } else {
                    $data = array_merge($data, $dynamicData);
                }
            }

            $node = [
                'title' => $row['title'] ?? '',
                'type' => $type,
                'classes' => $row['cssClass'] ?? null,
                'newWindow' => ($row['target'] ?? null) === '_blank',
                'customAttributes' => $this->_customAttributes($row),
                'data' => $data,
                'enabled' => (bool)($row['enabled'] ?? true),
                'enabledForSite' => (bool)($row['enabled'] ?? true),
            ];

            if (in_array($sourceType, ['entry', 'category', 'asset'], true) && $clickable) {
                [$linkedElementUid, $linkedElementType] = PluginMigrationHelper::resolveLinkedElement(
                    !empty($row['elementId']) ? (int)$row['elementId'] : null,
                    $sourceType,
                );

                if ($linkedElementUid && $linkedElementType) {
                    $node['linkedElementUid'] = $linkedElementUid;
                    $node['linkedElementType'] = $linkedElementType;
                } else {
                    [$node['type'], $url, $disableNode] = $this->_unavailableElementFallback($row);
                    $node['enabled'] = $disableNode ? false : $node['enabled'];
                    $node['enabledForSite'] = $disableNode ? false : $node['enabledForSite'];
                    $this->warning("MenuBuilder element item “{$row['title']}” has no linked element; its fallback behaviour was applied.", 1);
                }
            } elseif ($sourceType === 'url' && $clickable) {
                $url = !empty($row['customUrl']) ? (string)$row['customUrl'] : '#';
            } elseif ($sourceType === 'anchor' && $clickable) {
                $anchor = trim((string)($row['customUrl'] ?: ($row['handle'] ?? '')));
                $url = $anchor !== '' ? '#' . ltrim($anchor, '#') : '#';
            }

            if ($node['type'] === Custom::class && $url === null) {
                $url = !empty($row['customUrl']) ? (string)$row['customUrl'] : '#';
            }

            if ($url !== null) {
                $node['url'] = $url;
            }

            return PluginMigrationHelper::finalizeNode($node, fn(string $warning) => $this->warning($warning, 1));
        });
    }

    private function _nodeType(string $sourceType, bool $clickable): string
    {
        // Dynamic items project children even though their own container never renders a link.
        if ($sourceType === 'dynamic') {
            return Dynamic::class;
        }

        if (!$clickable) {
            return Passive::class;
        }

        return match ($sourceType) {
            'entry' => Entry::class,
            'category' => Category::class,
            'asset' => Asset::class,
            'nonclickable', 'separator' => Passive::class,
            default => Custom::class,
        };
    }

    private function _dynamicData(mixed $source): ?array
    {
        if (!is_array($source)) {
            return null;
        }

        $sourceId = (int)($source['sourceId'] ?? 0);
        $data = match ($source['sourceType'] ?? null) {
            'entries' => [
                'dynamicSource' => 'entrySection',
                'sectionId' => $sourceId,
            ],
            'categories' => [
                'dynamicSource' => 'categoryGroup',
                'groupId' => $sourceId,
            ],
            'assets' => [
                'dynamicSource' => 'assetVolume',
                'volumeId' => $sourceId,
            ],
            default => null,
        };

        if ($data === null || !$this->_dynamicSourceExists((string)$data['dynamicSource'], $sourceId)) {
            return null;
        }

        if (!empty($source['limit'])) {
            $data['limit'] = (int)$source['limit'];
        }

        if (!empty($source['orderBy'])) {
            $data['orderBy'] = (string)$source['orderBy'];
        }

        return $data;
    }

    private function _dynamicSourceExists(string $sourceType, int $sourceId): bool
    {
        if ($sourceId < 1) {
            return false;
        }

        return match ($sourceType) {
            'entrySection' => Craft::$app->getEntries()->getSectionById($sourceId) !== null,
            'categoryGroup' => Craft::$app->getCategories()->getGroupById($sourceId) !== null,
            'assetVolume' => Craft::$app->getVolumes()->getVolumeById($sourceId) !== null,
            default => false,
        };
    }

    private function _unavailableElementFallback(array $row): array
    {
        return match ($row['fallbackBehavior'] ?? 'hide') {
            'disableLink' => [Passive::class, null, false],
            'fallbackUrl' => [Custom::class, !empty($row['fallbackUrl']) ? (string)$row['fallbackUrl'] : '#', false],
            default => [Custom::class, '#', true],
        };
    }

    private function _customAttributes(array $row): array
    {
        $attributes = $this->_decodeBag($row['htmlAttributes'] ?? null);

        foreach ([
            'rel' => $row['rel'] ?? null,
            'id' => $row['htmlId'] ?? null,
            'aria-label' => $row['ariaLabel'] ?? null,
            'title' => $row['titleAttribute'] ?? null,
        ] as $attribute => $value) {
            if ($value !== null && $value !== '') {
                $attributes[$attribute] = $value;
            }
        }

        $rows = [];

        foreach ($attributes as $attribute => $value) {
            if (!is_string($attribute) || (!is_scalar($value) && $value !== null)) {
                continue;
            }

            $rows[] = [
                'attribute' => $attribute,
                'value' => (string)$value,
            ];
        }

        return $rows;
    }

    private function _preservedNodeData(array $row, array $metadata): array
    {
        $source = array_filter([
            'type' => $row['type'] ?? null,
            'handle' => $row['handle'] ?? null,
            'clickable' => isset($row['clickable']) ? (bool)$row['clickable'] : null,
            'icon' => $row['icon'] ?? null,
            'badge' => $row['badge'] ?? null,
            'description' => $row['description'] ?? null,
            'imageId' => !empty($row['image']) ? (int)$row['image'] : null,
            'featured' => !empty($row['featured']) ? true : null,
            'fallbackBehavior' => $row['fallbackBehavior'] ?? null,
            'fallbackUrl' => $row['fallbackUrl'] ?? null,
            'visibility' => $this->_decodeBag($row['visibility'] ?? null),
            'metadata' => $metadata,
        ], static fn(mixed $value): bool => $value !== null && $value !== '' && $value !== []);

        return $source === [] ? [] : ['migratedMenuBuilder' => $source];
    }

    private function _warnAboutUnsupportedNodeFeatures(array $rows): void
    {
        $warned = [];

        foreach ($rows as $row) {
            if ($this->_decodeBag($row['visibility'] ?? null) !== []) {
                $warned['visibility'] = 'MenuBuilder visibility rules are retained in node data but are not enforced by Navigation.';
            }

            $metadata = $this->_decodeBag($row['metadata'] ?? null);
            unset($metadata['dynamicSource']);

            if ($metadata !== []) {
                $warned['metadata'] = 'MenuBuilder mega-menu and mobile settings are retained in node data but are not applied by Navigation.';
            }

            if (
                !empty($row['icon']) ||
                !empty($row['badge']) ||
                !empty($row['description']) ||
                !empty($row['image']) ||
                !empty($row['featured'])
            ) {
                $warned['presentation'] = 'MenuBuilder item presentation fields are retained in node data but must be wired into Navigation templates or custom fields.';
            }

            if (($row['type'] ?? null) === 'separator') {
                $warned['separator'] = 'MenuBuilder separators are migrated as Passive nodes; recreate separator markup in your Navigation template.';
            }

            if (!empty($row['contentId'])) {
                $warned['fields'] = 'MenuBuilder item custom field values are not migrated; configure and populate matching Navigation node fields manually.';
            }
        }

        foreach ($warned as $warning) {
            $this->warning($warning, 1);
        }
    }

    private function _decodeBag(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        try {
            $decoded = Json::decodeIfJson($value);
        } catch (Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
