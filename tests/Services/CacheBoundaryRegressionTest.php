<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;

it('requires manual cache opt-in and bypasses off mode', function() {
    $menu = F::menu(); F::customNode($menu, 'Node', '/node');
    $settings = N::$plugin->getSettings(); $before = $settings->cacheMode;
    try {
        W::withAbsoluteUrl('https://cache.invalid/', function() use ($menu, $settings) {
            $cache = N::$plugin->getNavigationCache();
            $query = Node::find()->menuId($menu->id);
            $settings->cacheMode = 'off';
            expect($cache->shouldCacheQuery($query))->toBeFalse();
            $settings->cacheMode = 'manual';
            expect($cache->shouldCacheQuery($query))->toBeFalse();
            $query->useNavigationCache = true;
            expect($cache->shouldCacheQuery($query))->toBeTrue();
            $query->all();
            expect($cache->getCachedNodes($query))->not->toBeNull();
            $settings->cacheMode = 'off';
            expect($cache->getCachedNodes($query))->toBeNull();
        });
    } finally { $settings->cacheMode = $before; }
});

it('keeps warm cache content separate across sites', function() {
    $site = F::secondarySite(); $menu = F::menu();
    $node = F::customNode($menu, 'Node', '/primary');
    $other = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    expect($other)->not->toBeNull();
    $other->setUrl('/secondary');
    expect(Craft::$app->elements->saveElement($other, true, false))->toBeTrue();
    $settings = N::$plugin->getSettings(); $before = $settings->cacheMode; $settings->cacheMode = 'auto';
    try {
        W::withAbsoluteUrl('https://cache.invalid/', function() use ($menu, $node, $site) {
            foreach ([1,2] as $read) {
                expect(Node::find()->menuId($menu->id)->siteId($node->siteId)->one()->getUrl())->toBe('/primary');
                expect(Node::find()->menuId($menu->id)->siteId($site->id)->one()->getUrl())->toBe('/secondary');
            }
        });
    } finally { $settings->cacheMode = $before; }
});
