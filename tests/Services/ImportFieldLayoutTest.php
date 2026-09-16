<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;

it('preserves node custom fields when creating a menu from an export', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $node = F::customNodeWithField($menu, $field, 'Exported badge');
    $result = ImportExportHelper::importMenuFromJson(ImportExportHelper::generateMenuExport($menu));
    expect($result->errors)->toBe([]);
    $imported = Node::find()->menuId($result->menu->id)->one();
    expect($imported->getFieldLayout()->getFieldByHandle($field->handle))->not->toBeNull();
    expect($imported->getFieldValue($field->handle))->toBe('Exported badge');
    expect($result->menu->fieldLayoutId)->not->toBe($menu->fieldLayoutId);
    expect(Node::find()->id($node->id)->one()->getFieldValue($field->handle))->toBe('Exported badge');
});

it('preserves menu content and keeps imported layouts independent on update', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $layout = craft\models\FieldLayout::createFromConfig($menu->getFieldLayout()->getConfig());
    $layout->type = verbb\navigation\elements\Menu::class;
    $layout->resetUids();
    $menu->setMenuFieldLayout($layout);
    expect(verbb\navigation\Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $menu = verbb\navigation\Navigation::$plugin->getMenus()->getMenuById($menu->id);
    $content = verbb\navigation\elements\Menu::find()->id($menu->id)->one();
    $content->setFieldValue($field->handle, 'Menu introduction');
    expect(Craft::$app->elements->saveElement($content))->toBeTrue();
    $payload = ImportExportHelper::generateMenuExport($menu);
    $result = ImportExportHelper::importMenuFromJson($payload);
    expect($result->errors)->toBe([]);
    expect(verbb\navigation\elements\Menu::find()->id($result->menu->id)->one()->getFieldValue($field->handle))->toBe('Menu introduction');
    expect($result->menu->menuFieldLayoutId)->not->toBe($menu->menuFieldLayoutId);
    expect($result->menu->getMenuFieldLayout()->uid)->not->toBe($menu->getMenuFieldLayout()->uid);
    $payload['menu']['handle'] = $result->menu->handle;
    $updated = ImportExportHelper::importMenuFromJson($payload, 'update');
    expect($updated->errors)->toBe([]);
    expect(verbb\navigation\elements\Menu::find()->id($updated->menu->id)->one()->getFieldValue($field->handle))->toBe('Menu introduction');
    expect(verbb\navigation\elements\Menu::find()->id($menu->id)->one()->getFieldValue($field->handle))->toBe('Menu introduction');
});

it('rejects missing exported fields before replacing existing content', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $node = F::customNodeWithField($menu, $field, 'Keep original');
    $payload = ImportExportHelper::generateMenuExport($menu);
    $payload['menu']['nodeFieldLayout']['tabs'][0]['elements'][0]['fieldUid'] = craft\helpers\StringHelper::UUID();
    $result = ImportExportHelper::importMenuFromJson($payload, 'update');
    expect($result->errors)->not->toBe([]);
    expect(Node::find()->id($node->id)->one()->getFieldValue($field->handle))->toBe('Keep original');
});

it('clears explicitly empty imported layouts but preserves layouts in legacy exports', function() {
    $menu = F::menu();
    $field = F::addPlainTextFieldToMenu($menu);
    $payload = ImportExportHelper::generateMenuExport($menu);
    unset($payload['menu']['nodeFieldLayout'], $payload['menu']['menuFieldLayout']);
    $legacy = ImportExportHelper::importMenuFromJson($payload, 'update');
    expect($legacy->errors)->toBe([]);
    expect($legacy->menu->getFieldLayout()->getFieldByHandle($field->handle))->not->toBeNull();
    $payload['menu']['nodeFieldLayout'] = null;
    $cleared = ImportExportHelper::importMenuFromJson($payload, 'update');
    expect($cleared->errors)->toBe([]);
    expect($cleared->menu->getFieldLayout()->getCustomFields())->toBe([]);
});
