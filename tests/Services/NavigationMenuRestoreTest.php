<?php

declare(strict_types=1);

use craft\events\ConfigEvent;
use Tests\Support\Fixtures\NavigationFixtureFactory;
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

    $menus->handleChangedMenu(new ConfigEvent([
        'tokenMatches' => [$menu->uid],
        'newValue' => $config,
    ]));

    $restoredMenu = Navigation::$plugin->getMenus()->getMenuById($menu->id);
    expect($restoredMenu)->not->toBeNull();

    $restoredParent = Node::find()->id($parent->id)->status(null)->one();
    $restoredChild = Node::find()->id($child->id)->status(null)->one();

    expect($restoredParent)->not->toBeNull();
    expect($restoredChild)->not->toBeNull();

    $restoredChild->setStructureId($restoredMenu->structureId);
    $restoredChildParent = $restoredChild->getParent();

    expect($restoredChildParent?->id)->toBe($parent->id);
});
