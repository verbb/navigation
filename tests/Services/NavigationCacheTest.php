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

it('serves repeated nav reads from cache on the front end', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 4, 3);

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria) {
        $variable = new NavigationVariable();
        $first = QueryProfiler::profile(fn() => $variable->nodes($criteria)->all());
        $second = QueryProfiler::profile(fn() => $variable->nodes($criteria)->all());

        expect($first['resultSize'])->toBe(16);
        expect($first['queries'])->toBeGreaterThan(0);
        expect($second['queries'])->toBe(0);
    });
});

it('preserves node timestamps on warm front-end reads', function(string $profile) {
    $settings = Navigation::$plugin->getSettings();
    $previousProfile = $settings->cacheProfile;
    $settings->cacheProfile = $profile;

    try {
        $nav = NavigationFixtureFactory::menu();
        NavigationFixtureFactory::customNode($nav, 'Timestamped', '/timestamped');
        $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
        $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();

        WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria) {
            $variable = new NavigationVariable();
            $cold = $variable->nodes($criteria)->all();
            $warm = QueryProfiler::profile(fn() => $variable->nodes($criteria)->all());

            expect($warm['queries'])->toBe(0);
            $cached = $variable->nodes($criteria)->all()[0];

            foreach (['dateCreated', 'dateUpdated'] as $attribute) {
                expect($cold[0]->$attribute)->toBeInstanceOf(DateTime::class);
                expect($cached->$attribute)->toBeInstanceOf(DateTime::class);
                expect($cached->$attribute->format(DateTime::ATOM))->toBe($cold[0]->$attribute->format(DateTime::ATOM));
            }
        });
    } finally {
        $settings->cacheProfile = $previousProfile;
    }
})->with([NavigationCache::PROFILE_LITE, NavigationCache::PROFILE_STANDARD, NavigationCache::PROFILE_FULL]);

it('invalidates cached nav reads when a node is saved', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Original', '/original');

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria, $node) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->all();

        $node->title = 'Updated';
        Craft::$app->getElements()->saveElement($node);

        $titles = array_map(
            static fn($item) => $item->title,
            $variable->nodes($criteria)->all(),
        );

        expect($titles)->toBe(['Updated']);
    });
});

it('invalidates cached nav reads when nav settings are saved', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Menu Item', '/menu-item');

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
    $cache = Navigation::$plugin->getNavigationCache();
    $query = (new NavigationVariable())->nodes($criteria);

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria, $nav, $cache, $query) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->all();

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->not->toBeFalse();

        $nav->name = 'Renamed Navigation';
        Navigation::$plugin->getMenus()->saveMenu($nav);

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->toBeFalse();
    });
});

it('preserves children on cached level-scoped front-end reads', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->level(1)->all();

        $roots = $variable->nodes($criteria)->level(1)->all();

        expect($roots)->toHaveCount(1);
        expect($roots[0]->getChildren())->toHaveCount(1);
        expect($roots[0]->getChildren()[0]->title)->toBe('Child');
    });
});

it('preserves standard profile custom field values from cache', function() {
    $nav = NavigationFixtureFactory::menu();
    $field = NavigationFixtureFactory::addPlainTextFieldToMenu($nav);
    NavigationFixtureFactory::customNodeWithField($nav, $field, 'Cached Label');

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria, $field) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->all();

        $cachedNodes = $variable->nodes($criteria)->all();

        expect($cachedNodes)->toHaveCount(1);
        expect($cachedNodes[0]->getFieldValue($field->handle))->toBe('Cached Label');
    });
});

it('exposes invalidateCache for manual busting', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Item', '/item');

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
    $cache = Navigation::$plugin->getNavigationCache();
    $query = (new NavigationVariable())->nodes($criteria);

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria, $nav, $cache, $query) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->all();

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->not->toBeFalse();

        $variable->invalidateCache($nav->handle);

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->toBeFalse();
    });
});
