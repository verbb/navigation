<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\Navigation;
use verbb\navigation\variables\NavigationVariable;

it('boots Navigation and can create a basic custom-node tree', function() {
    expect(Navigation::$plugin)->not->toBeNull();

    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    $tree = (new NavigationVariable())->tree(['handle' => $nav->handle]);

    expect($tree)->toHaveCount(1);
    expect($tree[0]['title'])->toBe('Parent');
    expect($tree[0]['children'])->toHaveCount(1);
    expect($tree[0]['children'][0]['title'])->toBe('Child');
});
