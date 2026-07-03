<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\migrations\plugins\MigrateFromTkaNavigation;
use verbb\navigation\nodetypes\Asset;
use verbb\navigation\nodetypes\Category;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\nodetypes\Passive;

it('resolves Navigate element nodes from elementType', function() {
    expect(PluginMigrationHelper::resolveNavigateNodeType('element', 'entry'))->toBe(Entry::class)
        ->and(PluginMigrationHelper::resolveNavigateNodeType('element', 'category'))->toBe(Category::class)
        ->and(PluginMigrationHelper::resolveNavigateNodeType('element', 'asset'))->toBe(Asset::class);
});

it('resolves Navigate url and heading types case-insensitively', function() {
    expect(PluginMigrationHelper::resolveNavigateNodeType('Url'))->toBe(Custom::class)
        ->and(PluginMigrationHelper::resolveNavigateNodeType('url'))->toBe(Custom::class)
        ->and(PluginMigrationHelper::resolveNavigateNodeType('Heading'))->toBe(Passive::class)
        ->and(PluginMigrationHelper::resolveNavigateNodeType('heading'))->toBe(Passive::class);
});

it('resolves linked elements from short Navigate elementType handles', function() {
    $entry = NavigationFixtureFactory::entries(1)[0];

    [$uid, $type] = PluginMigrationHelper::resolveLinkedElement((int)$entry->id, 'entry');

    expect($uid)->toBe($entry->uid)
        ->and($type)->toBe(\craft\elements\Entry::class);
});

it('lists each tka navigation once on multi-site installs', function() {
    if (!PluginMigrationHelper::tableExists('tka_navigations')) {
        $this->markTestSkipped('tka navigation tables not present.');
    }

    $menus = MigrateFromTkaNavigation::getMenus();
    $handles = array_column($menus, 'handle');

    expect($handles)->toBe(array_values(array_unique($handles)));
});
