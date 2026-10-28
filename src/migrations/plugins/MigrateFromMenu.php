<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\Navigation;
use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Site;

use Craft;
use craft\db\Query;
use craft\helpers\Json;

use RuntimeException;

class MigrateFromMenu extends BasePluginMigrator
{
    // Static Methods
    // =========================================================================

    public static function sourceLabel(): string
    {
        return 'Menu';
    }

    public static function pluginHandle(): string
    {
        return 'menu';
    }

    public static function sourceTable(): string
    {
        return 'menu_menus';
    }

    public static function getMenus(): array
    {
        if (!PluginMigrationHelper::tableExists(self::sourceTable())) {
            return [];
        }

        return array_map(static fn(array $menu) => [
            'handle' => $menu['handle'],
            'label' => $menu['title'],
        ], self::_menus());
    }

    private static function _menus(): array
    {
        // Menu definitions are non-localized elements; their title lives on the primary site.
        return (new Query())
            ->select(['m.*', 'es.title', 'e.enabled', 'structureMaxLevels' => 's.maxLevels'])
            ->from(['m' => '{{%menu_menus}}'])
            ->innerJoin(['s' => '{{%structures}}'], '[[s.id]] = [[m.structureId]]')
            ->innerJoin(['e' => '{{%elements}}'], '[[e.id]] = [[m.id]]')
            ->innerJoin(['es' => '{{%elements_sites}}'], [
                'and', '[[es.elementId]] = [[m.id]]',
                ['es.siteId' => Craft::$app->getSites()->getPrimarySite()->id],
            ])
            ->where(['e.dateDeleted' => null, 'e.draftId' => null, 'e.revisionId' => null])
            ->orderBy(['m.sortOrder' => SORT_ASC, 'm.id' => SORT_ASC])
            ->all();
    }


    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        foreach (['menu_menus', 'menu_nodes', 'menu_menus_sites'] as $table) {
            if (!PluginMigrationHelper::tableExists($table)) {
                $this->error('Menu tables not found. Install Menu and run its migrations first.');

                return false;
            }
        }

        $menus = self::_menus();

        if ($menus === []) {
            $this->info('No Menu menus found to migrate.');

            return true;
        }

        $filter = $this->handlesFilter();
        $ok = true;

        foreach ($menus as $menu) {
            if (!$this->matchesHandle($menu['handle'], $filter)) {
                continue;
            }

            if (!$this->importExportPayload($this->_buildPayload($menu), $menu['title'])) {
                $ok = false;
            }
        }

        return $ok;
    }


    // Private Methods
    // =========================================================================

    private function _buildPayload(array $menu): array
    {
        $enabledSiteIds = array_map('intval', (new Query())
            ->select('siteId')
            ->from('{{%menu_menus_sites}}')
            ->where(['menuId' => $menu['id'], 'enabled' => true])
            ->column());
        $siteSettings = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            // Menu treats an empty enabled-site list as unrestricted.
            $siteSettings[$site->handle] = ['enabled' => $enabledSiteIds === [] || in_array((int)$site->id, $enabledSiteIds, true)];
        }

        $menuData = [
            'name' => $menu['title'],
            'handle' => $menu['handle'],
            'instructions' => $menu['instructions'],
            'maxLevels' => $menu['maxLevels'] ?? $menu['structureMaxLevels'],
            'propagationMethod' => 'all',
            'titleTranslationMethod' => 'site',
            'siteSettings' => $siteSettings,
        ];
        $layout = !empty($menu['nodeFieldLayoutId'])
            ? Craft::$app->getFields()->getLayoutById((int)$menu['nodeFieldLayoutId'])
            : null;

        if ($layout) {
            $menuData['nodeFieldLayout'] = $layout->getConfig();
        }

        $this->warning('Review menu permissions and templates after migration; Menu permissions and Twig calls are not copied.', 1);

        return PluginMigrationHelper::baseExportPayload($menuData, $this->_buildNodeTree($menu, $siteSettings));
    }

    private function _buildNodeTree(array $menu, array $siteSettings): array
    {
        $rows = (new Query())
            ->select(['n.*', 'e.enabled', 'se.level', 'se.lft'])
            ->from(['n' => '{{%menu_nodes}}'])
            ->innerJoin(['e' => '{{%elements}}'], '[[e.id]] = [[n.id]]')
            ->innerJoin(['se' => '{{%structureelements}}'], [
                'and', '[[se.elementId]] = [[n.id]]', ['se.structureId' => $menu['structureId']],
            ])
            ->where(['n.menuId' => $menu['id'], 'e.dateDeleted' => null, 'e.draftId' => null, 'e.revisionId' => null])
            ->orderBy(['se.lft' => SORT_ASC])
            ->all();
        $locales = [];

        foreach ((new Query())->from('{{%elements_sites}}')->where(['elementId' => array_column($rows, 'id')])->all() as $locale) {
            $locales[$locale['elementId']][(int)$locale['siteId']] = $locale;
        }

        return PluginMigrationHelper::buildNestedTree(PluginMigrationHelper::attachStructureParents($rows), function(array $row) use ($menu, $siteSettings, $locales): array {
            $node = $this->_buildNode($row);
            $available = $locales[$row['id']] ?? [];
            $primaryId = (int)Craft::$app->getSites()->getPrimarySite()->id;
            $sourceLocale = $available[$primaryId] ?? reset($available);

            foreach (Craft::$app->getSites()->getAllSites() as $site) {
                if (!$siteSettings[$site->handle]['enabled']) {
                    continue;
                }

                // Prefer an enabled source site, including menus restricted to a secondary site.
                if (!$sourceLocale || !$siteSettings[Craft::$app->getSites()->getSiteById((int)$sourceLocale['siteId'])->handle]['enabled']) {
                    $sourceLocale = $available[$site->id] ?? $sourceLocale;
                }
            }

            if (!$sourceLocale) {
                throw new RuntimeException('Menu node has no site content: ' . $row['id']);
            }

            $sourceSite = Craft::$app->getSites()->getSiteById((int)$sourceLocale['siteId']);
            $node['title'] = $sourceLocale['title'] ?? '';
            $node['sourceSiteHandle'] = $sourceSite->handle;
            $node['enabled'] = (bool)$row['enabled'] && (bool)$menu['enabled'];
            $node['enabledForSite'] = $node['enabled'] && (bool)$sourceLocale['enabled'];
            $node['fieldValues'] = $this->_fieldValues((int)$row['id'], (int)$sourceLocale['siteId']);

            foreach (Craft::$app->getSites()->getAllSites() as $site) {
                if ($site->id === $sourceSite->id || !$siteSettings[$site->handle]['enabled']) {
                    continue;
                }

                $locale = $available[$site->id] ?? null;
                $node['siteOverrides'][$site->handle] = [
                    'title' => $locale['title'] ?? $node['title'],
                    'enabledForSite' => $node['enabled'] && (bool)($locale['enabled'] ?? false),
                    'fieldValues' => $locale ? $this->_fieldValues((int)$row['id'], (int)$site->id) : [],
                ];
            }

            // Site nodes reference Craft sites through data, not linked element UIDs.
            return $node['type'] === Site::class ? $node : PluginMigrationHelper::finalizeNode($node, fn(string $warning) => $this->warning($warning, 1));
        });
    }

    private function _fieldValues(int $nodeId, int $siteId): array
    {
        $element = Craft::$app->getElements()->getElementById($nodeId, null, $siteId);

        return $element?->getSerializedFieldValues() ?? [];
    }

    private function _buildNode(array $row): array
    {
        $data = Json::decodeIfJson($row['data']) ?? [];
        $type = $row['type'];
        $node = [
            'type' => PluginMigrationHelper::mapFreeNavNodeType($type) ?? Custom::class,
            'url' => $row['url'],
            'classes' => $row['classes'],
            'urlSuffix' => $row['urlSuffix'],
            'newWindow' => (bool)$row['newWindow'],
            'customAttributes' => $this->_customAttributes($row['customAttributes']),
            'data' => ['migratedMenu' => ['type' => $type, 'followElement' => (bool)$row['followElement'], 'data' => $data]],
        ];

        if ($type === 'site') {
            $site = Craft::$app->getSites()->getSiteById((int)$row['elementId']);

            if ($site) {
                $node['data']['siteId'] = (int)$site->id;
                // Menu's site URL always includes a slash before the suffix.
                $node['urlSuffix'] = '/' . ltrim((string)$row['urlSuffix'], '/');
            } else {
                $node['type'] = Passive::class;
                $this->warning('A Menu site link has no matching site; converted to Passive.', 1);
            }
        } elseif ($type === 'dynamic') {
            $this->_configureDynamicNode($node, is_array($data) ? $data : []);
        } elseif (in_array($type, ['entry', 'category', 'asset', 'product'], true)) {
            [$uid, $elementType] = PluginMigrationHelper::resolveLinkedElement((int)$row['elementId'], $type);

            if ($uid && $elementType) {
                $node['linkedElementUid'] = $uid;
                $node['linkedElementType'] = $elementType;
            }
        } elseif (!in_array($type, ['custom', 'passive'], true)) {
            $this->warning("Unknown Menu node type “{$type}”; converted to Custom URL.", 1);
        }

        if ($node['type'] === Custom::class && empty($node['url'])) {
            $node['url'] = '#';
        }

        return $node;
    }

    private function _customAttributes(mixed $value): array
    {
        $attributes = PluginMigrationHelper::parseCustomAttributes($value);
        $result = [];

        foreach ($attributes as $key => $attribute) {
            $result[] = is_array($attribute) ? $attribute : ['attribute' => (string)$key, 'value' => (string)$attribute];
        }

        return $result;
    }

    private function _configureDynamicNode(array &$node, array $data): void
    {
        $source = $data['source'] ?? '';
        $mapping = match ($source) {
            'entrySection' => ['sections', 'section', 'sectionId'],
            'categoryGroup' => ['categorygroups', 'group', 'groupId'],
            'assetVolume' => ['volumes', 'volume', 'volumeId'],
            'productType' => ['commerce_producttypes', 'productType', 'productTypeId'],
            default => null,
        };
        $id = null;

        if ($mapping && Navigation::$plugin->getDynamicSources()->getProviderClass($source) && PluginMigrationHelper::tableExists($mapping[0])) {
            $id = (new Query())->select('id')->from('{{%' . $mapping[0] . '}}')->where(['handle' => $data[$mapping[1]] ?? ''])->scalar();
        }

        if (!$id) {
            $node['type'] = Passive::class;
            $this->warning('A Menu dynamic source is unavailable; converted to Passive and retained its settings in node data.', 1);

            return;
        }

        $node['type'] = Dynamic::class;
        $node['data'] += [
            'dynamicSource' => $source,
            $mapping[2] => (int)$id,
            'limit' => isset($data['limit']) && is_numeric($data['limit']) ? max(1, (int)$data['limit']) : 50,
            'orderBy' => 'title asc',
            'projectedChildrenPosition' => 'after',
        ];
        $this->warning('Review dynamic output: Navigation uses published content and preserves section/category hierarchy instead of Menu’s flat, all-status list.', 1);
    }
}
