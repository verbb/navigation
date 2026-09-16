<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;

it('loads deleted menu field content for restoration and permanent cleanup', function(string $action) {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $node = F::customNodeWithField($menu, $field, 'Retained trash content');
    $menus = Navigation::$plugin->getMenus();
    expect($menus->deleteMenu($menu))->toBeTrue();
    $menus->resetCache();
    Craft::$app->fields->refreshFields();
    expect($menus->getMenuById($menu->id))->toBeNull();

    $trashed = Node::find()->id($node->id)->status(null)->trashed()->one();
    expect($trashed)->not->toBeNull();
    expect($trashed->getFieldValue($field->handle))->toBe('Retained trash content');

    if ($action === 'restore') {
        expect($menus->saveMenu($menu))->toBeTrue();
        expect(Node::find()->id($node->id)->one()->getFieldValue($field->handle))->toBe('Retained trash content');
    } else {
        expect(Craft::$app->elements->deleteElement($trashed, true))->toBeTrue();
        expect((new craft\db\Query())->from('{{%elements}}')->where(['id' => $node->id])->exists())->toBeFalse();
        expect((new craft\db\Query())->from('{{%navigation_nodes}}')->where(['id' => $node->id])->exists())->toBeFalse();
    }
})->with(['restore', 'purge']);
