<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;
use verbb\navigation\variables\NavigationVariable;

beforeEach(function() {
    Navigation::$plugin->getSettings()->cacheMode = NavigationCache::MODE_AUTO;
});

it('invalidates menu cache on each node save without per-node cache key explosion', function() {
    $menu = NavigationFixtureFactory::menu();
    $nodes = [];

    for ($i = 1; $i <= 10; $i++) {
        $nodes[] = NavigationFixtureFactory::customNode($menu, "Item {$i}", "/item-{$i}");
    }

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $menu->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
    $cache = Navigation::$plugin->getNavigationCache();
    $query = (new NavigationVariable())->nodes($criteria);

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria, $cache, $query, $nodes) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->all();

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->not->toBeFalse();

        foreach ($nodes as $index => $node) {
            $node->title = "Updated {$index}";
            Craft::$app->getElements()->saveElement($node);
        }

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->toBeFalse();
    });
});

it('repopulates cache with a single query after bulk node saves', function() {
    $menu = NavigationFixtureFactory::menu();

    for ($i = 1; $i <= 10; $i++) {
        NavigationFixtureFactory::customNode($menu, "Bulk {$i}", "/bulk-{$i}");
    }

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $menu->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria) {
        $variable = new NavigationVariable();
        $profile = QueryProfiler::profile(fn() => $variable->nodes($criteria)->all());

        expect($profile['resultSize'])->toBe(10);
        expect($profile['queries'])->toBeLessThanOrEqual(1);
    });
});

it('fires a cache invalidation event with menu tags', function() {
    $menu = NavigationFixtureFactory::menu();
    $cache = Navigation::$plugin->getNavigationCache();
    $fired = null;

    $cache->on(NavigationCache::EVENT_INVALIDATE, function($event) use (&$fired) {
        $fired = $event;
    });

    $cache->invalidateMenu($menu->uid);

    expect($fired)->not->toBeNull();
    expect($fired->tags)->toContain('navigation:menu:' . $menu->uid);
    expect($fired->menuUid)->toBe($menu->uid);
});
