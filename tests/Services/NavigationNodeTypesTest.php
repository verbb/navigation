<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\nodetypes\GroupColumn;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\variables\NavigationVariable;

it('renders group column nodes without urls', function() {
    $nav = NavigationFixtureFactory::menu();
    $column = NavigationFixtureFactory::groupColumnNode($nav, 'Column');
    NavigationFixtureFactory::customNode($nav, 'Link', '/link', $column);

    $tree = (new NavigationVariable())->tree(['handle' => $nav->handle]);

    expect($tree)->toHaveCount(1);
    expect($tree[0]['title'])->toBe('Column');
    expect($tree[0]['url'])->toBeNull();
    expect($tree[0]['children'])->toHaveCount(1);
    expect($tree[0]['children'][0]['title'])->toBe('Link');
});

it('exposes crawlable span tags for passive and group column nodes', function() {
    expect(Passive::getTag())->toBe('span');
    expect(GroupColumn::getTag())->toBe('span');

    $nav = NavigationFixtureFactory::menu();
    $passive = NavigationFixtureFactory::passiveNode($nav, 'Trigger');

    expect($passive->getTag())->toBe('span');
});
