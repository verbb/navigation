<?php

declare(strict_types=1);

use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Menu;
use verbb\navigation\Navigation;

it('returns builder state for a menu', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;

    $plainText = new PlainText([
        'name' => 'Plain Text',
        'handle' => 'plainText' . uniqid(),
    ]);

    if (!Craft::$app->getFields()->saveField($plainText)) {
        throw new RuntimeException('Failed saving plain text field fixture.');
    }

    $layout = new FieldLayout(['type' => Menu::class]);
    $tab = new FieldLayoutTab(['name' => 'Content']);
    $tab->setLayout($layout);
    $tab->setElements([
        Craft::$app->getFields()->createLayoutElement([
            'type' => CustomField::class,
            'fieldUid' => $plainText->uid,
        ]),
    ]);
    $layout->setTabs([$tab]);
    Craft::$app->getFields()->saveLayout($layout);

    $nav->setMenuFieldLayout($layout);
    Navigation::$plugin->getMenus()->saveMenu($nav);

    $state = Navigation::$plugin->getBuilderState()->getState((int)$nav->id, $siteId);

    expect($state['menu']['id'])->toBe($nav->id);
    expect($state['site']['id'])->toBe($siteId);
    expect($state['stagingEnabled'])->toBeTrue();
    expect($state['menuContent']['hasFields'])->toBeTrue();
    expect($state['builderTabs'])->not->toBeEmpty();
});

it('saves menu content fields on the menu element', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;

    $plainText = new PlainText([
        'name' => 'Plain Text',
        'handle' => 'plainText' . uniqid(),
    ]);

    if (!Craft::$app->getFields()->saveField($plainText)) {
        throw new RuntimeException('Failed saving plain text field fixture.');
    }

    $layout = new FieldLayout(['type' => Menu::class]);
    $tab = new FieldLayoutTab(['name' => 'Content']);
    $tab->setLayout($layout);
    $tab->setElements([
        Craft::$app->getFields()->createLayoutElement([
            'type' => CustomField::class,
            'fieldUid' => $plainText->uid,
        ]),
    ]);
    $layout->setTabs([$tab]);
    Craft::$app->getFields()->saveLayout($layout);

    $nav->setMenuFieldLayout($layout);
    Navigation::$plugin->getMenus()->saveMenu($nav);

    $saved = Navigation::$plugin->getMenus()->saveMenuContentFromDraft(
        (int)$nav->id,
        $siteId,
        [$plainText->handle => 'saved value'],
    );

    expect($saved)->toBeTrue();

    $menuElement = Menu::find()->id($nav->id)->siteId($siteId)->status(null)->one();

    expect($menuElement->getFieldValue($plainText->handle))->toBe('saved value');
});
