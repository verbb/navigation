<?php

declare(strict_types=1);

use craft\db\Query;
use craft\db\Table;
use craft\events\ConfigEvent;
use craft\helpers\StringHelper;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\helpers\MenuSiteAssociationRepair;
use verbb\navigation\Navigation;

it('merges database site associations into migrated project config', function() {
    F::existingSecondarySite();
    $menu = F::menu();
    $menus = [
        $menu->uid => [
            'name' => $menu->name,
            'siteSettings' => [],
        ],
    ];

    $merged = MenuSiteAssociationRepair::mergeDatabaseSiteSettings($menus);
    $databaseSettings = MenuSiteAssociationRepair::databaseSiteSettingsByMenuUid();

    expect($merged[$menu->uid]['siteSettings'])->toBe($databaseSettings[$menu->uid]);
    expect($merged[$menu->uid]['siteSettings'])->toHaveCount(2);
});

it('repairs missing associations from the existing Menu element sites', function() {
    F::existingSecondarySite();
    $menu = F::menu();
    $db = Craft::$app->getDb();
    $transaction = $db->beginTransaction();

    try {
        $db->createCommand()->update('{{%navigation_menus}}', ['uid' => StringHelper::UUID()], ['id' => $menu->id])->execute();
        $elementSettings = (new Query())
            ->select(['siteId', 'enabled'])
            ->from([Table::ELEMENTS_SITES])
            ->where(['elementId' => $menu->id])
            ->orderBy(['siteId' => SORT_ASC])
            ->all();
        $db->createCommand()->delete('{{%navigation_menus_sites}}', ['menuId' => $menu->id])->execute();

        $result = MenuSiteAssociationRepair::run();
        $restored = (new Query())
            ->select(['siteId', 'enabled'])
            ->from('{{%navigation_menus_sites}}')
            ->where(['menuId' => $menu->id])
            ->orderBy(['siteId' => SORT_ASC])
            ->all();

        expect($result['repairedMenus'])->toBe(1);
        expect($result['sources']['elements'])->toBe(1);
        expect($restored)->toBe($elementSettings);
    } finally {
        $transaction->rollBack();
        Navigation::$plugin->getMenus()->resetCache();
    }
});

it('uses the primary site only when a single-site install has no other evidence', function() {
    $menu = F::menu();
    $db = Craft::$app->getDb();
    $transaction = $db->beginTransaction();

    try {
        expect(Craft::$app->getSites()->getAllSites())->toHaveCount(1);
        $db->createCommand()->update('{{%navigation_menus}}', ['uid' => StringHelper::UUID()], ['id' => $menu->id])->execute();
        $db->createCommand()->delete('{{%navigation_menus_sites}}', ['menuId' => $menu->id])->execute();
        $db->createCommand()->delete(Table::ELEMENTS_SITES, ['elementId' => $menu->id])->execute();

        $result = MenuSiteAssociationRepair::run();
        $rows = (new Query())
            ->select(['siteId', 'enabled'])
            ->from('{{%navigation_menus_sites}}')
            ->where(['menuId' => $menu->id])
            ->all();

        expect($result['sources']['singleSite'])->toBe(1);
        expect($rows)->toHaveCount(1);
        expect((int)$rows[0]['siteId'])->toBe(Craft::$app->getSites()->getPrimarySite()->id);
        expect((bool)$rows[0]['enabled'])->toBeTrue();
    } finally {
        $transaction->rollBack();
        Navigation::$plugin->getMenus()->resetCache();
    }
});

it('does not guess when a multisite menu has no association evidence', function() {
    F::existingSecondarySite();
    $menu = F::menu();
    $db = Craft::$app->getDb();
    $transaction = $db->beginTransaction();

    try {
        $db->createCommand()->update('{{%navigation_menus}}', ['uid' => StringHelper::UUID()], ['id' => $menu->id])->execute();
        $db->createCommand()->delete('{{%navigation_menus_sites}}', ['menuId' => $menu->id])->execute();
        $db->createCommand()->delete(Table::ELEMENTS_SITES, ['elementId' => $menu->id])->execute();

        $result = MenuSiteAssociationRepair::run();

        expect($result['repairedMenus'])->toBe(0);
        expect($result['unresolvedMenus'])->toBe(1);
        expect((new Query())->from('{{%navigation_menus_sites}}')->where(['menuId' => $menu->id])->exists())->toBeFalse();
    } finally {
        $transaction->rollBack();
        Navigation::$plugin->getMenus()->resetCache();
    }
});

it('refuses project config that would remove every existing menu site', function() {
    $menu = F::menu();
    $config = $menu->getConfig();
    $config['siteSettings'] = [];
    $before = (int)(new Query())
        ->from('{{%navigation_menus_sites}}')
        ->where(['menuId' => $menu->id])
        ->count();

    expect(fn() => Navigation::$plugin->getMenus()->handleChangedMenu(new ConfigEvent([
        'tokenMatches' => [$menu->uid],
        'newValue' => $config,
    ])))->toThrow(RuntimeException::class, 'Refusing to remove every site association');

    $after = (int)(new Query())
        ->from('{{%navigation_menus_sites}}')
        ->where(['menuId' => $menu->id])
        ->count();

    expect($after)->toBe($before);
});
