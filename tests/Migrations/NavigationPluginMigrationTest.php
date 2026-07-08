<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\migrations\plugins\MigrateFromFreeNav;
use verbb\navigation\migrations\plugins\MigrateFromNavkit;
use verbb\navigation\Navigation;
use verbb\navigation\variables\NavigationVariable;

it('detects Navkit as a migration source when tables exist', function() {
    if (!PluginMigrationHelper::tableExists('navkit_menus')) {
        $this->markTestSkipped('Navkit tables not present.');
    }

    $sources = Navigation::$plugin->getMigrations()->getSources();

    expect($sources['navkit']['ready'])->toBeTrue()
        ->and($sources['navkit']['consoleCommand'])->toBe('navigation/migrate/navkit')
        ->and(Navigation::$plugin->getMigrations()->getSourceMenus('navkit'))->toBeArray();
});

it('migrates a Navkit menu into Navigation', function() {
    if (!PluginMigrationHelper::tableExists('navkit_menus')) {
        $this->markTestSkipped('Navkit tables not present.');
    }

    $existing = Navigation::$plugin->getMenus()->getMenuByHandle('general');

    if ($existing) {
        Navigation::$plugin->getMenus()->deleteMenu($existing);
    }

    $migration = Navigation::$plugin->createMigrator(MigrateFromNavkit::class, [
        'handle' => 'general',
    ]);

    $result = $migration->run();

    expect($result->ok)->toBeTrue();

    $menu = Navigation::$plugin->getMenus()->getMenuByHandle('general');

    expect($menu)->not->toBeNull()
        ->and($menu->name)->toBe('General');
});

it('detects FreeNav as a migration source when tables exist', function() {
    if (!PluginMigrationHelper::tableExists('freenav_menus')) {
        $this->markTestSkipped('FreeNav tables not present.');
    }

    $sources = Navigation::$plugin->getMigrations()->getSources();

    expect($sources['free-nav']['ready'])->toBeTrue()
        ->and($sources['free-nav']['consoleCommand'])->toBe('navigation/migrate/free-nav')
        ->and(Navigation::$plugin->getMigrations()->getSourceMenus('free-nav'))->toBeArray();
});

it('adds ready migration sources to settings nav', function() {
    $navItems = (new NavigationVariable())->getSettingsNavItems();

    if (Navigation::$plugin->getMigrations()->isSourceReady('free-nav')) {
        expect($navItems)->toHaveKey('migrate/free-nav');
    }
});

it('migrates a FreeNav menu into Navigation', function() {
    if (!PluginMigrationHelper::tableExists('freenav_menus')) {
        $this->markTestSkipped('FreeNav tables not present.');
    }

    $existing = Navigation::$plugin->getMenus()->getMenuByHandle('test');

    if ($existing) {
        Navigation::$plugin->getMenus()->deleteMenu($existing);
    }

    $migration = Navigation::$plugin->createMigrator(MigrateFromFreeNav::class, [
        'handle' => 'test',
    ]);

    $result = $migration->run();

    expect($result->ok)->toBeTrue();

    $menu = Navigation::$plugin->getMenus()->getMenuByHandle('test');

    expect($menu)->not->toBeNull()
        ->and($menu->name)->toBe('Test');
});

it('skips existing handles when requested', function() {
    NavigationFixtureFactory::menu('freenavSkipExisting');

    if (!PluginMigrationHelper::tableExists('freenav_menus')) {
        $this->markTestSkipped('FreeNav tables not present.');
    }

    $migration = Navigation::$plugin->createMigrator(MigrateFromFreeNav::class, [
        'handle' => 'test',
        'skipExisting' => true,
    ]);

    $result = $migration->run();

    expect($result->ok)->toBeTrue()
        ->and($result->stats['menusSkipped'] ?? 0)->toBeGreaterThan(0);
});
