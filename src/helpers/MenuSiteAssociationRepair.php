<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;

use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Db;
use craft\helpers\StringHelper;

use DateTime;

class MenuSiteAssociationRepair
{
    // Static Methods
    // =========================================================================

    /**
     * Restore site rows only when an authoritative source identifies the sites.
     *
     * @return array{repairedMenus: int, insertedRows: int, unresolvedMenus: int, repairedMenuUids: string[], sources: array{projectConfig: int, elements: int, singleSite: int}}
     */
    public static function run(): array
    {
        $result = [
            'repairedMenus' => 0,
            'insertedRows' => 0,
            'unresolvedMenus' => 0,
            'repairedMenuUids' => [],
            'sources' => [
                'projectConfig' => 0,
                'elements' => 0,
                'singleSite' => 0,
            ],
        ];

        $db = Craft::$app->getDb();

        if (!$db->tableExists('{{%navigation_menus}}') || !$db->tableExists('{{%navigation_menus_sites}}')) {
            return $result;
        }

        $menus = (new Query())
            ->select(['m.id', 'm.uid'])
            ->from(['m' => '{{%navigation_menus}}'])
            ->leftJoin(['ms' => '{{%navigation_menus_sites}}'], '[[ms.menuId]] = [[m.id]]')
            ->where(['ms.id' => null])
            ->all($db);

        if (!$menus) {
            return $result;
        }

        $sites = (new Query())
            ->select(['id', 'uid'])
            ->from([Table::SITES])
            ->where(['dateDeleted' => null])
            ->indexBy('uid')
            ->all($db);

        $transaction = $db->beginTransaction();

        try {
            foreach ($menus as $menu) {
                [$siteSettings, $source] = self::_siteSettingsForMenu((int)$menu['id'], (string)$menu['uid'], $sites);

                if (!$siteSettings) {
                    $result['unresolvedMenus']++;

                    continue;
                }

                $now = Db::prepareDateForDb(new DateTime());

                foreach ($siteSettings as $siteId => $enabled) {
                    $db->createCommand()->insert('{{%navigation_menus_sites}}', [
                        'menuId' => $menu['id'],
                        'siteId' => $siteId,
                        'enabled' => $enabled,
                        'dateCreated' => $now,
                        'dateUpdated' => $now,
                        'uid' => StringHelper::UUID(),
                    ])->execute();

                    $result['insertedRows']++;
                }

                $result['repairedMenus']++;
                $result['repairedMenuUids'][] = (string)$menu['uid'];
                $result['sources'][$source]++;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        if ($result['repairedMenus'] > 0) {
            Navigation::$plugin->getMenus()->resetCache();
        }

        return $result;
    }

    /**
     * Replace each menu's site settings with the current database associations.
     */
    public static function mergeDatabaseSiteSettings(array $menus, ?array $menuUids = null): array
    {
        $siteSettings = self::databaseSiteSettingsByMenuUid();

        foreach ($siteSettings as $menuUid => $settings) {
            if ($menuUids !== null && !in_array($menuUid, $menuUids, true)) {
                continue;
            }

            if (!isset($menus[$menuUid]) || !is_array($menus[$menuUid])) {
                continue;
            }

            $menus[$menuUid]['siteSettings'] = $settings;
        }

        return $menus;
    }

    /**
     * Return project-config-compatible site settings keyed by menu UID.
     */
    public static function databaseSiteSettingsByMenuUid(): array
    {
        $db = Craft::$app->getDb();

        if (!$db->tableExists('{{%navigation_menus}}') || !$db->tableExists('{{%navigation_menus_sites}}')) {
            return [];
        }

        $rows = (new Query())
            ->select([
                'menuUid' => 'm.uid',
                'siteUid' => 's.uid',
                'ms.enabled',
            ])
            ->from(['ms' => '{{%navigation_menus_sites}}'])
            ->innerJoin(['m' => '{{%navigation_menus}}'], '[[m.id]] = [[ms.menuId]]')
            ->innerJoin(['s' => Table::SITES], '[[s.id]] = [[ms.siteId]]')
            ->where(['s.dateDeleted' => null])
            ->orderBy(['ms.id' => SORT_ASC])
            ->all($db);

        $siteSettings = [];

        foreach ($rows as $row) {
            $siteSettings[$row['menuUid']][$row['siteUid']] = [
                'enabled' => (bool)$row['enabled'],
            ];
        }

        return $siteSettings;
    }


    // Private Methods
    // =========================================================================

    private static function _siteSettingsForMenu(int $menuId, string $menuUid, array $sites): array
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $config = $projectConfig->get("navigation.menus.$menuUid.siteSettings")
            ?? $projectConfig->get("navigation.navs.$menuUid.siteSettings");
        $settings = self::_settingsFromProjectConfig($config, $sites);

        if ($settings) {
            return [$settings, 'projectConfig'];
        }

        $settings = self::_settingsFromElementSites($menuId, $sites);

        if ($settings) {
            return [$settings, 'elements'];
        }

        if (count($sites) === 1) {
            $site = reset($sites);

            return [[(int)$site['id'] => true], 'singleSite'];
        }

        return [[], null];
    }

    private static function _settingsFromProjectConfig(mixed $config, array $sites): array
    {
        if (!is_array($config)) {
            return [];
        }

        $settings = [];

        foreach ($config as $siteUid => $siteConfig) {
            if (!isset($sites[$siteUid]) || !is_array($siteConfig)) {
                continue;
            }

            $settings[(int)$sites[$siteUid]['id']] = (bool)($siteConfig['enabled'] ?? true);
        }

        return $settings;
    }

    private static function _settingsFromElementSites(int $menuId, array $sites): array
    {
        $siteIds = array_map(static fn(array $site): int => (int)$site['id'], $sites);
        $rows = (new Query())
            ->select(['siteId', 'enabled'])
            ->from([Table::ELEMENTS_SITES])
            ->where([
                'elementId' => $menuId,
                'siteId' => $siteIds,
            ])
            ->all();

        $settings = [];

        foreach ($rows as $row) {
            $settings[(int)$row['siteId']] = (bool)$row['enabled'];
        }

        return $settings;
    }
}
