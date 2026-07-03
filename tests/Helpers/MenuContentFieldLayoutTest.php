<?php

declare(strict_types=1);

use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\TitleField;
use craft\fields\PlainText;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Menu;
use verbb\navigation\helpers\MenuContentFieldLayout;
use verbb\navigation\Navigation;

it('removes title from menu content field layouts', function() {
    $layout = new FieldLayout(['type' => Menu::class]);
    $tab = new FieldLayoutTab(['name' => 'Content']);
    $tab->setLayout($layout);
    $tab->setElements([
        Craft::createObject(TitleField::class),
    ]);
    $layout->setTabs([$tab]);

    $stripped = MenuContentFieldLayout::withoutTitle($layout);

    expect($stripped)->not->toBeNull();
    expect($stripped->isFieldIncluded('title'))->toBeFalse();
});

it('strips title from menu field layout project config', function() {
    $config = [
        'tabs' => [[
            'name' => 'Content',
            'elements' => [
                ['type' => TitleField::class, 'attribute' => 'title'],
                ['type' => 'craft\\fields\\PlainText', 'fieldUid' => 'test'],
            ],
        ]],
    ];

    $stripped = MenuContentFieldLayout::stripTitleFromConfig($config);

    expect($stripped['tabs'][0]['elements'])->toHaveCount(1);
    expect($stripped['tabs'][0]['elements'][0]['type'])->toBe('craft\\fields\\PlainText');
});

it('maps each field layout tab to a builder tab', function() {
    $nav = NavigationFixtureFactory::menu();
    $plainText = new PlainText([
        'name' => 'Plain Text',
        'handle' => 'plainText' . uniqid(),
    ]);
    $plainText2 = new PlainText([
        'name' => 'Plain Text 2',
        'handle' => 'plainText2' . uniqid(),
    ]);

    foreach ([$plainText, $plainText2] as $field) {
        if (!Craft::$app->getFields()->saveField($field)) {
            throw new RuntimeException('Failed saving plain text field fixture.');
        }
    }

    $layout = new FieldLayout(['type' => Menu::class]);
    $contentTab = new FieldLayoutTab(['name' => 'Content']);
    $contentTab->setLayout($layout);
    $contentTab->setElements([
        Craft::$app->getFields()->createLayoutElement([
            'type' => CustomField::class,
            'fieldUid' => $plainText->uid,
        ]),
    ]);

    $anotherTab = new FieldLayoutTab(['name' => 'Another']);
    $anotherTab->setLayout($layout);
    $anotherTab->setElements([
        Craft::$app->getFields()->createLayoutElement([
            'type' => CustomField::class,
            'fieldUid' => $plainText2->uid,
        ]),
    ]);

    $layout->setTabs([$contentTab, $anotherTab]);
    Craft::$app->getFields()->saveLayout($layout);

    $nav->setMenuFieldLayout($layout);
    Navigation::$plugin->getMenus()->saveMenu($nav);

    $nav = Navigation::$plugin->getMenus()->getMenuById($nav->id);
    $menuElement = Menu::find()->id($nav->id)->siteId(Craft::$app->getSites()->getPrimarySite()->id)->status(null)->one();

    expect($menuElement)->not->toBeNull();
    $builderTabs = MenuContentFieldLayout::getBuilderFormTabs(
        MenuContentFieldLayout::withoutTitle($menuElement->getFieldLayout()),
        $menuElement,
    );

    expect($builderTabs)->toHaveCount(2);
    expect($builderTabs[0]['label'])->toBe('Content');
    expect($builderTabs[1]['label'])->toBe('Another');
    expect($builderTabs[0]['html'])->toContain($plainText->handle);
    expect($builderTabs[1]['html'])->toContain($plainText2->handle);
});

it('does not expose title when rendering menu content in the builder', function() {
    $nav = NavigationFixtureFactory::menu();
    $plainText = new PlainText([
        'name' => 'Menu Plain Text',
        'handle' => 'menuPlainText' . uniqid(),
    ]);

    if (!Craft::$app->getFields()->saveField($plainText)) {
        throw new RuntimeException('Failed saving plain text field fixture.');
    }

    $layout = new FieldLayout(['type' => Menu::class]);
    $tab = new FieldLayoutTab(['name' => 'Content']);
    $tab->setLayout($layout);
    $tab->setElements([
        Craft::createObject(TitleField::class),
        Craft::$app->getFields()->createLayoutElement([
            'type' => CustomField::class,
            'fieldUid' => $plainText->uid,
        ]),
    ]);
    $layout->setTabs([$tab]);
    Craft::$app->getFields()->saveLayout($layout);

    $nav->setMenuFieldLayout($layout);
    Navigation::$plugin->getMenus()->saveMenu($nav);

    $nav = Navigation::$plugin->getMenus()->getMenuById($nav->id);
    $menuElement = Menu::find()->id($nav->id)->siteId(Craft::$app->getSites()->getPrimarySite()->id)->status(null)->one();

    expect($menuElement)->not->toBeNull();
    $contentLayout = MenuContentFieldLayout::withoutTitle($menuElement->getFieldLayout());

    expect(MenuContentFieldLayout::hasCustomFields($contentLayout))->toBeTrue();
    expect($contentLayout->isFieldIncluded('title'))->toBeFalse();

    $html = $contentLayout->createForm($menuElement)->render();

    expect($html)->not->toContain('name="title"');
    expect($html)->toContain($plainText->handle);
});

it('loads menu content fields from the menu field layout, not node fields', function() {
    $nav = NavigationFixtureFactory::menu();
    $nodeField = NavigationFixtureFactory::addPlainTextFieldToMenu($nav, 'nodePlainText' . uniqid());

    $menuField = new PlainText([
        'name' => 'Menu Plain Text',
        'handle' => 'menuPlainText' . uniqid(),
    ]);

    if (!Craft::$app->getFields()->saveField($menuField)) {
        throw new RuntimeException('Failed saving menu plain text field fixture.');
    }

    $menuLayout = new FieldLayout(['type' => Menu::class]);
    $tab = new FieldLayoutTab(['name' => 'Content']);
    $tab->setLayout($menuLayout);
    $tab->setElements([
        Craft::$app->getFields()->createLayoutElement([
            'type' => CustomField::class,
            'fieldUid' => $menuField->uid,
        ]),
    ]);
    $menuLayout->setTabs([$tab]);
    Craft::$app->getFields()->saveLayout($menuLayout);

    $nav->setMenuFieldLayout($menuLayout);
    Navigation::$plugin->getMenus()->saveMenu($nav);

    $nav = Navigation::$plugin->getMenus()->getMenuById($nav->id);
    $menuElement = Menu::find()->id($nav->id)->siteId(Craft::$app->getSites()->getPrimarySite()->id)->status(null)->one();

    expect($menuElement->nodeFieldLayoutId)->toBe($nav->fieldLayoutId);
    expect($menuElement->menuFieldLayoutId)->toBe($nav->menuFieldLayoutId);

    $layout = $menuElement->getFieldLayout();
    $handles = array_map(fn($field) => $field->handle, $layout->getCustomFields());

    expect($handles)->toContain($menuField->handle);
    expect($handles)->not->toContain($nodeField->handle);
});

it('does not show menu content tabs when menu fields are not configured', function() {
    $nav = NavigationFixtureFactory::menu();
    $nodeField = NavigationFixtureFactory::addPlainTextFieldToMenu($nav, 'nodePlainText' . uniqid());

    $nav = Navigation::$plugin->getMenus()->getMenuById($nav->id);
    $menuElement = Menu::find()->id($nav->id)->siteId(Craft::$app->getSites()->getPrimarySite()->id)->status(null)->one();

    expect($nav->menuFieldLayoutId)->toBeNull();
    expect($menuElement->menuFieldLayoutId)->toBeNull();
    expect($menuElement->nodeFieldLayoutId)->toBe($nav->fieldLayoutId);

    // Simulate stale elements.fieldLayoutId left after menu fields were removed.
    Craft::$app->getDb()->createCommand()
        ->update('{{%elements}}', ['fieldLayoutId' => $nav->fieldLayoutId], ['id' => $nav->id])
        ->execute();

    $menuElement = Menu::find()->id($nav->id)->siteId(Craft::$app->getSites()->getPrimarySite()->id)->status(null)->one();

    expect($menuElement->getFieldLayout())->toBeNull();
    expect(MenuContentFieldLayout::hasCustomFields($menuElement->getFieldLayout()))->toBeFalse();

    expect($nodeField->handle)->not->toBeEmpty();
});
