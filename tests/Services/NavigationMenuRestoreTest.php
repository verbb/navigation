<?php

declare(strict_types=1);

use craft\events\ConfigEvent;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;

it('restores a soft-deleted menu and nested nodes via project config', function() {
    $menu = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($menu, 'Parent', '/parent');
    $child = NavigationFixtureFactory::customNode($menu, 'Child', '/parent/child', $parent);

    $config = $menu->getConfig();
    $menus = Navigation::$plugin->getMenus();

    $menus->handleDeletedMenu(new ConfigEvent([
        'tokenMatches' => [$menu->uid],
    ]));

    expect(Navigation::$plugin->getMenus()->getMenuById($menu->id))->toBeNull();

    $trashedParent = Node::find()->id($parent->id)->status(null)->trashed()->one();
    expect($trashedParent)->not->toBeNull();
    expect($trashedParent->deletedWithMenu)->toBeTrue();

    $trashedMenuElement = Menu::find()->id($menu->id)->status(null)->trashed()->one();
    expect($trashedMenuElement)->not->toBeNull();

    $menus->handleChangedMenu(new ConfigEvent([
        'tokenMatches' => [$menu->uid],
        'newValue' => $config,
    ]));

    $restoredMenu = Navigation::$plugin->getMenus()->getMenuById($menu->id);
    expect($restoredMenu)->not->toBeNull();

    $liveMenuElement = Menu::find()->id($menu->id)->status(null)->one();
    expect($liveMenuElement)->not->toBeNull();
    expect(Menu::find()->id($menu->id)->status(null)->trashed()->one())->toBeNull();

    $restoredParent = Node::find()->id($parent->id)->status(null)->one();
    $restoredChild = Node::find()->id($child->id)->status(null)->one();

    expect($restoredParent)->not->toBeNull();
    expect($restoredChild)->not->toBeNull();
    expect($restoredParent->deletedWithMenu)->toBeFalse();
    expect($restoredChild->deletedWithMenu)->toBeFalse();
    // Structure parents must survive soft-delete + restore (A13 residual).
    expect($restoredChild->getParent()?->id)->toBe($restoredParent->id);
});

it('preserves reordered roots and nested siblings when restoring a menu', function() {
    $menu = NavigationFixtureFactory::menu();
    $a = NavigationFixtureFactory::customNode($menu, 'A', '/a');
    $b = NavigationFixtureFactory::customNode($menu, 'B', '/b');
    $c = NavigationFixtureFactory::customNode($menu, 'C', '/c');
    $a1 = NavigationFixtureFactory::customNode($menu, 'A1', '/a1', $a);
    $a2 = NavigationFixtureFactory::customNode($menu, 'A2', '/a2', $a);
    $a2->data = ['authored' => ['value' => '0']];
    $a2->enabled = false;
    expect(Craft::$app->elements->saveElement($a2))->toBeTrue();
    $trash = NavigationFixtureFactory::customNode($menu, 'Already trashed', '/trash');
    expect(Craft::$app->elements->deleteElement($trash))->toBeTrue();
    expect(Craft::$app->structures->prependToRoot($menu->structureId, $c))->toBeTrue();
    expect(Craft::$app->structures->prepend($menu->structureId, $a2, $a))->toBeTrue();
    $order = static fn() => Node::find()->menuId($menu->id)->status(null)->ids();
    $expected = [$c->id, $a->id, $a2->id, $a1->id, $b->id];
    expect($order())->toBe($expected);

    for ($cycle = 0; $cycle < 2; $cycle++) {
        expect(Navigation::$plugin->getMenus()->deleteMenu($menu))->toBeTrue();
        expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
        expect($order())->toBe($expected);
        $restored = Node::find()->id($a2->id)->status(null)->one();
        expect($restored->getParentId())->toBe($a->id);
        expect($restored->enabled)->toBeFalse();
        expect($restored->data)->toBe(['authored' => ['value' => '0']]);
        expect(Node::find()->id($trash->id)->trashed()->status(null)->exists())->toBeTrue();
    }
});
