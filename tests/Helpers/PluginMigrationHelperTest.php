<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\migrations\plugins\MigrateFromNavkit;
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

it('maps Navkit link types to Navigation node types', function() {
    expect(PluginMigrationHelper::mapNavkitNodeType('entry'))->toBe(Entry::class)
        ->and(PluginMigrationHelper::mapNavkitNodeType('category'))->toBe(Category::class)
        ->and(PluginMigrationHelper::mapNavkitNodeType('asset'))->toBe(Asset::class)
        ->and(PluginMigrationHelper::mapNavkitNodeType('url'))->toBe(Custom::class)
        ->and(PluginMigrationHelper::mapNavkitNodeType('passive'))->toBe(Passive::class);
});

it('derives parent ids from nested-set structure rows', function() {
    $flat = PluginMigrationHelper::attachStructureParents([
        ['id' => 1, 'level' => 1, 'lft' => 2, 'rgt' => 5],
        ['id' => 2, 'level' => 2, 'lft' => 3, 'rgt' => 4],
    ]);

    expect($flat[0]['parentId'])->toBeNull()
        ->and($flat[1]['parentId'])->toBe(1);
});

it('lists each tka navigation once on multi-site installs', function() {
    if (!PluginMigrationHelper::tableExists('tka_navigations')) {
        $this->markTestSkipped('tka navigation tables not present.');
    }

    $menus = MigrateFromTkaNavigation::getMenus();
    $handles = array_column($menus, 'handle');

    expect($handles)->toBe(array_values(array_unique($handles)));
});
