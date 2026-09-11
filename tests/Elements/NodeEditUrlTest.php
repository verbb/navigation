<?php

declare(strict_types=1);

use craft\base\Element;
use craft\helpers\Cp;
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

it('keeps generic element chips free of builder-only presentation', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'External', '/external');
    $node->newWindow = true;
    $node->classes = 'featured';

    $chipHtml = Cp::elementChipHtml($node, ['context' => 'index']);
    $declaringClass = (new ReflectionMethod(Node::class, 'getChipLabelHtml'))->getDeclaringClass()->getName();

    expect($declaringClass)->toBe(Element::class)
        ->and($chipHtml)->toContain('External')
        ->and($chipHtml)->not->toContain('node-info-icons')
        ->and($chipHtml)->not->toContain('node-edit-btn')
        ->and($chipHtml)->not->toContain('featured');
});

it('includes the menu in full-page editor breadcrumbs', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Contact', '/contact');

    $crumbs = $node->getCrumbs();

    expect($crumbs)->toHaveCount(2);
    expect($crumbs[1]['label'])->toBe($nav->name);
    expect($crumbs[1]['url'])->toContain('navigation/menus/build/' . $nav->id);
});
