<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

it('retains a zero relative URL in node URI and rendered breadcrumb output', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Numeric destination', '0');
    $loaded = Node::find()->id($node->id)->one();
    expect($loaded->getNodeUri())->toBe('0');
    expect((string)$loaded->getLink())->toContain('href="0"');

    F::customNode($menu, 'Current page', '/numeric-child', $node);
    $url = rtrim(Craft::$app->sites->primarySite->getBaseUrl(), '/') . '/numeric-child';
    W::withAbsoluteUrl($url, function() use ($menu) {
        $breadcrumbs = Navigation::$plugin->getMenuBreadcrumbs()->getBreadcrumbs($menu->handle);
        expect($breadcrumbs)->toHaveCount(2);
        expect($breadcrumbs[0]['url'])->toBe('0');
        expect($breadcrumbs[0]['link'])->toContain('<a ', 'href="0"');
    });
});

it('preserves zero-valued attributes in link helpers and default rendering', function() {
    $menu = F::menu();
    $node = F::passiveNode($menu, 'Focusable group');
    $node->customAttributes = [
        ['attribute' => 'tabindex', 'value' => '0'],
        ['attribute' => 'data-count', 'value' => '0'],
    ];
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    expect((string)$node->getLink())->toContain('tabindex="0"', 'data-count="0"');
    $node->customAttributes = [];
    expect((string)$node->getLink(['tabindex' => 0]))->toContain('tabindex="0"');
    expect((string)(new verbb\navigation\variables\NavigationVariable())->render($menu->handle))
        ->toContain('tabindex="0"', 'data-count="0"');
});

it('marks a zero relative URL current at its destination', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Numeric destination', '0');
    $url = rtrim(Craft::$app->sites->primarySite->getBaseUrl(), '/') . '/0';
    W::withAbsoluteUrl($url, function() use ($menu, $node) {
        expect(Navigation::$plugin->getContextResolver()->resolve($menu->handle)->current()?->id)->toBe($node->id);
    });
});
