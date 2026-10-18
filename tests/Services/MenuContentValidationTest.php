<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\BuilderController;
use verbb\navigation\elements\Menu;
use verbb\navigation\Navigation;

it('returns menu field errors without changing saved content and clears them on retry', function(string $action) {
    $menu = F::menu();
    $field = new craft\fields\PlainText(['name' => 'Menu badge', 'handle' => 'badge' . uniqid()]);
    expect(Craft::$app->fields->saveField($field))->toBeTrue();
    $layout = new craft\models\FieldLayout(['type' => Menu::class]);
    $layout->setTabs([new craft\models\FieldLayoutTab([
        'layout' => $layout,
        'name' => 'Content',
        'elements' => [['type' => craft\fieldlayoutelements\CustomField::class, 'fieldUid' => $field->uid, 'required' => true]],
    ])]);
    $menu->setMenuFieldLayout($layout);
    expect(Navigation::$plugin->menus->saveMenu($menu))->toBeTrue();
    $content = Menu::find()->id($menu->id)->one();
    $content->setFieldValue($field->handle, 'Original badge');
    expect(Craft::$app->elements->saveElement($content))->toBeTrue();

    boundaryRequest(function() use ($menu, $field, $content, $action) {
        $controller = new BuilderController('builder', Navigation::$plugin);
        $payload = ['menuId' => $menu->id, 'siteId' => $content->siteId, 'fieldsLocation' => 'fields', 'fields' => [$field->handle => '']];
        Craft::$app->request->setBodyParams($payload);
        $response = $controller->$action();
        expect($response->statusCode)->toBe(400);
        expect($response->data['errors'] ?? [])->toHaveKey($field->handle);
        expect(implode(' ', $response->data['errors'][$field->handle]))->toContain('cannot be blank');
        expect(Menu::find()->id($menu->id)->one()->getFieldValue($field->handle))->toBe('Original badge');

        $payload['fields'][$field->handle] = 'Saved badge';
        Craft::$app->request->setBodyParams($payload);
        $controller->$action();
        expect(Menu::find()->id($menu->id)->one()->getFieldValue($field->handle))->toBe('Saved badge');
        expect(Navigation::$plugin->menus->getMenuContentSaveErrors())->toBe([]);
    });
})->with(['actionMenuContentSlideoutSave', 'actionSaveMenuContent']);
