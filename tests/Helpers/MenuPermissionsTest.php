<?php

use craft\elements\Entry as EntryElement;
use verbb\navigation\helpers\MenuPermissions;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Entry;

test('menu permissions normalize legacy element and node type keys', function () {
    $normalized = MenuPermissions::normalize([
        EntryElement::class => [
            'enabled' => '1',
            'permissions' => ['section:blog'],
        ],
        'verbb\\navigation\\nodetypes\\CustomType' => [
            'enabled' => '1',
        ],
        Entry::class => [
            'enabled' => '',
            'permissions' => '*',
        ],
    ]);

    expect($normalized)->toHaveKey(Entry::class)
        ->and($normalized[Entry::class]['enabled'])->toBeTrue()
        ->and($normalized[Entry::class]['permissions'])->toBe('*')
        ->and($normalized)->toHaveKey(Custom::class)
        ->and($normalized[Custom::class]['enabled'])->toBeTrue();
});

test('menu permissions read canonical keys only', function () {
    $permissions = [
        Entry::class => [
            'enabled' => true,
            'permissions' => ['section:news'],
        ],
    ];

    expect(MenuPermissions::isTypeEnabled($permissions, Entry::class))->toBeTrue()
        ->and(MenuPermissions::getTypeSources($permissions, Entry::class))->toBe(['section:news']);
});
