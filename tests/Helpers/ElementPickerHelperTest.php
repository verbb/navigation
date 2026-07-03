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
