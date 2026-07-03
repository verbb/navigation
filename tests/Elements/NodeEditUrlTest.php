<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Node;

it('exposes a CP edit URL and post-edit redirect for the menu builder', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Contact2', '/contact');

    $cpEditUrl = $node->getCpEditUrl();

    expect($cpEditUrl)->not->toBeNull();
    expect($cpEditUrl)->toContain('edit/' . $node->id);

    $postEditUrl = $node->getPostEditUrl();

    expect($postEditUrl)->not->toBeNull();
    expect($postEditUrl)->toContain('navigation/menus/build/' . $nav->id);
});

it('does not register front-end preview targets for nodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Contact2', '/contact');

    expect($node->getUrl())->toBe('/contact');
    expect($node->getPreviewTargets())->toBe([]);
});

it('tracks element changes for cache invalidation integrations', function() {
    expect(Node::trackChanges())->toBeTrue();
});

it('includes the menu in full-page editor breadcrumbs', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Contact', '/contact');

    $crumbs = $node->getCrumbs();

    expect($crumbs)->toHaveCount(2);
    expect($crumbs[1]['label'])->toBe($nav->name);
    expect($crumbs[1]['url'])->toContain('navigation/menus/build/' . $nav->id);
});
