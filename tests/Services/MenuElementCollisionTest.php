<?php

declare(strict_types=1);

use craft\db\Query;
use craft\db\Table;
use craft\elements\Entry;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Menu;
use verbb\navigation\Navigation;

it('creates menu elements that do not reclaim existing entry ids', function() {
    $entry = NavigationFixtureFactory::entries(1)[0];
    $entryId = (int)$entry->id;
    $entryTitle = $entry->title;

    $menu = NavigationFixtureFactory::menu();

    expect($menu->id)->not->toBe($entryId);

    $menuElement = Menu::find()->id($menu->id)->status(null)->one();
    expect($menuElement)->not->toBeNull();
    expect($menuElement->id)->toBe($menu->id);

    expect(
        (new Query())->select(['type'])->from([Table::ELEMENTS])->where(['id' => $entryId])->scalar()
    )->toBe(Entry::class);

    expect(Entry::find()->id($entryId)->status(null)->one()?->title)->toBe($entryTitle);

    // Saving the menu again must not retype the entry or wipe its title.
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    expect(
        (new Query())->select(['type'])->from([Table::ELEMENTS])->where(['id' => $entryId])->scalar()
    )->toBe(Entry::class);

    expect(Entry::find()->id($entryId)->status(null)->one()?->title)->toBe($entryTitle);
});

it('allocates exclusive element ids for legacy menu collisions without retyping content', function() {
    $entry = NavigationFixtureFactory::entries(1)[0];
    $menu = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($menu, 'Legacy parent', '/parent');
    $child = NavigationFixtureFactory::customNode($menu, 'Legacy child', '/child', $parent);
    $db = Craft::$app->getDb();
    $transaction = $db->beginTransaction();
    try {
        $row = (new Query())->from('{{%navigation_menus}}')->where(['id' => $menu->id])->one();
        $row['id'] = $entry->id;
        $db->createCommand()->insert('{{%navigation_menus}}', $row)->execute();
        $db->createCommand()->update('{{%navigation_nodes}}', ['menuId' => $entry->id], ['menuId' => $menu->id])->execute();
        $db->createCommand()->update('{{%navigation_menus_sites}}', ['menuId' => $entry->id], ['menuId' => $menu->id])->execute();
        $db->createCommand()->delete('{{%navigation_menus}}', ['id' => $menu->id])->execute();
        $db->createCommand()->delete(Table::ELEMENTS, ['id' => $menu->id])->execute();
        expect(Menu::find()->id($entry->id)->status(null)->one())->toBeNull();
        expect(verbb\navigation\helpers\MenuElementCollisionRepair::migrateLegacyMenuIds())->toBe(1);
        $reloaded = Navigation::$plugin->getMenus()->getMenuByUid($menu->uid);
        expect($reloaded->id)->not->toBe($entry->id);
        expect(Menu::find()->id($reloaded->id)->one()?->uid)->toBe($menu->uid);
        expect(Entry::find()->id($entry->id)->one()?->title)->toBe($entry->title);
        expect(verbb\navigation\elements\Node::find()->id($child->id)->one()?->getParent()?->id)->toBe($parent->id);
        expect(verbb\navigation\helpers\MenuElementCollisionRepair::migrateLegacyMenuIds())->toBe(0);
    } finally {
        $transaction->rollBack();
        Navigation::$plugin->getMenus()->resetCache();
    }
});
