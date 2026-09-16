<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\models\MenuSettings;

it('preserves imported root and child order when new nodes normally go at the beginning', function() {
    $menu = F::menu();
    $menu->defaultPlacement = MenuSettings::DEFAULT_PLACEMENT_BEGINNING;
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $parent = F::customNode($menu, 'First authored root', '/first');
    F::customNode($menu, 'First authored child', '/first/one', $parent);
    F::customNode($menu, 'Second authored child', '/first/two', $parent);
    F::customNode($menu, 'Second authored root', '/second');
    $before = array_column(Node::find()->menuId($menu->id)->all(), 'title');
    $result = ImportExportHelper::importMenuFromJson(ImportExportHelper::generateMenuExport($menu));
    expect($result->errors)->toBe([]);
    expect($result->menu->defaultPlacement)->toBe(MenuSettings::DEFAULT_PLACEMENT_BEGINNING);
    expect(array_column(Node::find()->menuId($result->menu->id)->all(), 'title'))->toBe($before);
});
