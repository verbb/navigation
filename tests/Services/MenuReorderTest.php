<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\services\Menus;
use verbb\navigation\Navigation;

it('reorders menus through complete project config payloads', function() {
    $first = NavigationFixtureFactory::menu();
    $second = NavigationFixtureFactory::menu();
    $menus = Navigation::$plugin->getMenus();

    expect($menus->reorderMenus([$second->id, $first->id]))->toBeTrue();

    $menus->resetCache();
    $first = $menus->getMenuById($first->id);
    $second = $menus->getMenuById($second->id);

    expect($second->sortOrder)->toBe(1)
        ->and($first->sortOrder)->toBe(2);

    $projectConfig = Craft::$app->getProjectConfig();
    $firstConfig = $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $first->uid);
    $secondConfig = $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $second->uid);

    expect($secondConfig['sortOrder'])->toBe(1)
        ->and($firstConfig['sortOrder'])->toBe(2)
        ->and($firstConfig)->toHaveKeys(['name', 'handle', 'structure', 'siteSettings'])
        ->and($secondConfig)->toHaveKeys(['name', 'handle', 'structure', 'siteSettings']);
});
