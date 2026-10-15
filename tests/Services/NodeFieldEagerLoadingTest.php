<?php

declare(strict_types=1);

use craft\elements\ElementCollection;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Entries;
use craft\models\FieldLayoutTab;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;

it('eager-loads node custom fields for front-end guests', function() {
    $menu = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entry = NavigationFixtureFactory::entries(1, $section)[0];
    $field = new Entries([
        'name' => 'Related entry',
        'handle' => 'relatedEntry' . uniqid(),
        'sources' => ['section:' . $section->uid],
    ]);

    expect(Craft::$app->getFields()->saveField($field))->toBeTrue();

    $layout = $menu->getFieldLayout();
    $layout->setTabs([
        new FieldLayoutTab([
            'layout' => $layout,
            'name' => 'Content',
            'elements' => [[
                'type' => CustomField::class,
                'fieldUid' => $field->uid,
            ]],
        ]),
    ]);

    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    $node = NavigationFixtureFactory::customNode($menu, 'Related item', '/related-item');
    $node->setFieldValue($field->handle, [$entry->id]);

    expect(Craft::$app->getElements()->saveElement($node))->toBeTrue();

    $site = Craft::$app->getSites()->getPrimarySite();
    $user = Craft::$app->getUser();
    $identity = $user->getIdentity();

    try {
        $user->setIdentity(null);

        WebRequestSimulator::withAbsoluteUrl($site->getBaseUrl(), function() use ($menu, $site, $field, $entry) {
            expect(Craft::$app->getUser()->getIsGuest())->toBeTrue();

            $nodes = Node::find()
                ->menuId($menu->id)
                ->siteId($site->id)
                ->with([$field->handle])
                ->all();

            expect($nodes)->toHaveCount(1);

            $value = $nodes[0]->getFieldValue($field->handle);

            expect($value)->toBeInstanceOf(ElementCollection::class);
            expect($value->ids()->all())->toBe([$entry->id]);
        });
    } finally {
        $user->setIdentity($identity);
    }
});
