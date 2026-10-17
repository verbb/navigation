<?php

use craft\elements\Entry as EntryElement;
use verbb\navigation\helpers\ElementPickerHelper;
use verbb\navigation\nodetypes\Entry as EntryNodeType;

test('element picker filters configured sources to user-accessible keys', function () {
    $allKeys = array_values(array_filter(array_map(
        fn(array $source) => $source['key'] ?? null,
        Craft::$app->getElementSources()->getSources(EntryElement::class, 'modal'),
    )));

    if ($allKeys === []) {
        expect(true)->toBeTrue();

        return;
    }

    $filtered = ElementPickerHelper::filterSourcesForUser(EntryElement::class, ['section:missing', $allKeys[0]]);

    expect($filtered)->toBe([$allKeys[0]]);
});

test('entry pickers respect source and peer-entry view permissions', function(string $access) {
    $menu = Tests\Support\Fixtures\NavigationFixtureFactory::menu();
    $section = Tests\Support\Fixtures\NavigationFixtureFactory::entrySection();
    $hiddenSection = Tests\Support\Fixtures\NavigationFixtureFactory::entrySection();
    $entries = Tests\Support\Fixtures\NavigationFixtureFactory::entries(2, $section);
    $hiddenEntry = Tests\Support\Fixtures\NavigationFixtureFactory::entries(1, $hiddenSection)[0];
    $editor = new craft\elements\User(['username' => uniqid('picker-'), 'email' => uniqid('picker-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($editor))->toBeTrue();
    $entries[0]->setAuthorIds([$editor->id]);
    expect(Craft::$app->getElements()->saveElement($entries[0], false))->toBeTrue();

    boundaryRequest(function() use ($menu, $section, $hiddenSection, $entries, $hiddenEntry, $editor, $access) {
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        $permissions = $access === 'none' ? [] : ['viewEntries:' . $section->uid];
        if ($access === 'peers') {
            $permissions[] = 'viewPeerEntries:' . $section->uid;
        }
        expect(Craft::$app->getUserPermissions()->saveUserPermissions($editor->id, $permissions))->toBeTrue();
        Craft::$app->getUser()->setIdentity($editor);

        $sources = ElementPickerHelper::filterSourcesForUser(EntryElement::class, '*');
        $tabs = verbb\navigation\Navigation::$plugin->getMenus()->getBuilderTabs($menu);
        if ($access === 'none') {
            expect($sources)->toBe([]);
            expect(array_column($tabs, 'elementType'))->not->toContain(EntryElement::class);
        } else {
            expect($sources)->toContain('section:' . $section->uid)->not->toContain('section:' . $hiddenSection->uid);
            expect(ElementPickerHelper::filterSourcesForUser(EntryElement::class, ['section:' . $hiddenSection->uid]))->toBe([]);
        }
        $config = ElementPickerHelper::getPickerConfig([], EntryNodeType::class, EntryElement::class);
        $query = EntryElement::find()->id([$entries[0]->id, $entries[1]->id, $hiddenEntry->id])->orderBy('elements.id');
        Craft::configure($query, $config['criteria'] ?? []);
        $expected = match ($access) {
            'none' => [],
            'own' => [$entries[0]->id],
            'peers' => [$entries[0]->id, $entries[1]->id],
        };
        expect(array_map('intval', $query->ids()))->toBe($expected);
    });
})->with(['none', 'own', 'peers']);

test('element picker config applies entry criteria settings', function () {
    $permissions = [
        EntryNodeType::class => [
            'enabled' => true,
            'permissions' => '*',
            'excludeWithoutUri' => true,
            'hideSingleSectionUids' => [],
        ],
    ];

    $config = ElementPickerHelper::getPickerConfig($permissions, EntryNodeType::class, EntryElement::class);

    expect($config)->toHaveKey('criteria')
        ->and($config['criteria']['uri'])->toBe(':notempty:');
});
