<?php

declare(strict_types=1);

use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\web\Request;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Menu;
use verbb\navigation\Navigation;

it('saves menu content field values from the publish request payload', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;

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

    $request = Craft::createObject([
        'class' => Request::class,
        'bodyParams' => [
            'fieldsLocation' => 'fields',
            'siteId' => $siteId,
            'fields' => [
                $plainText->handle => 'ww',
                $plainText2->handle => '',
            ],
        ],
    ]);

    Craft::$app->set('request', $request);

    $saved = Navigation::$plugin->getMenus()->saveMenuContentFromRequest((int)$nav->id, $siteId);

    expect($saved)->toBeTrue();

    $menuElement = Menu::find()->id($nav->id)->siteId($siteId)->status(null)->one();

    expect($menuElement)->not->toBeNull();
    expect($menuElement->getFieldValue($plainText->handle))->toBe('ww');
    expect($menuElement->getFieldValue($plainText2->handle))->toBeIn([null, '']);

    $tabs = Navigation::$plugin->getMenus()->getMenuContentTabsForBuilder((int)$nav->id, $siteId);

    expect($tabs)->not->toBeEmpty();
    expect($tabs[0]['html'])->toContain('ww');
});
