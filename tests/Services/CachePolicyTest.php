<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;

it('keeps warmed menu URLs separate for each site', function() {
    $primary = Craft::$app->getSites()->getPrimarySite();
    $secondary = F::existingSecondarySite();
    $menu = F::menu();
    $node = F::customNode($menu, 'Local link', '/primary');
    $translated = Node::find()->id($node->id)->siteId($secondary->id)->status(null)->one();
    $translated->url = '/secondary';
    expect(Craft::$app->getElements()->saveElement($translated))->toBeTrue();

    $settings = Navigation::$plugin->getSettings();
    $previous = $settings->cacheMode;
    $settings->cacheMode = NavigationCache::MODE_AUTO;
    try {
        W::withAbsoluteUrl($primary->getBaseUrl(), function() use ($menu, $primary, $secondary) {
            $read = static fn($site) => Node::find()->menuId($menu->id)->siteId($site->id)->all();
            $expected = [
                [$primary, '/primary'],
                [$secondary, '/secondary'],
            ];
            foreach ($expected as [$site, $url]) {
                expect($read($site)[0]->getUrl())->toBe($url);
            }
            foreach ($expected as [$site, $url]) {
                $profile = QueryProfiler::profile(function() use ($read, $site, $url) {
                    $nodes = $read($site);
                    expect($nodes)->toHaveCount(1);
                    expect((int)$nodes[0]->siteId)->toBe((int)$site->id);
                    expect($nodes[0]->getUrl())->toBe($url);
                    return $nodes;
                });
                expect($profile['queries'])->toBe(0);
            }
        });
    } finally {
        $settings->cacheMode = $previous;
    }
});

it('honours off mode and manual cache opt-in during actual reads', function(string $mode, bool $optIn, bool $cached) {
    $menu = F::menu();
    $node = F::customNode($menu, 'Cache policy', '/policy');
    $settings = Navigation::$plugin->getSettings();
    $previous = $settings->cacheMode;
    $settings->cacheMode = $mode;
    try {
        W::withAbsoluteUrl(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), function() use ($menu, $node, $optIn, $cached) {
            $read = static fn() => Node::find()->menuId($menu->id)->withNavigationCache($optIn)->all();
            $read();
            $profile = QueryProfiler::profile(function() use ($read, $node) {
                $nodes = $read();
                expect(array_map(static fn($item) => (int)$item->id, $nodes))->toBe([(int)$node->id]);
                return $nodes;
            });
            if ($cached) {
                expect($profile['queries'])->toBe(0);
            } else {
                expect($profile['queries'])->toBeGreaterThan(0);
            }
        });
    } finally {
        $settings->cacheMode = $previous;
    }
})->with([
    'off overrides opt-in' => [NavigationCache::MODE_OFF, true, false],
    'manual without opt-in' => [NavigationCache::MODE_MANUAL, false, false],
    'manual with opt-in' => [NavigationCache::MODE_MANUAL, true, true],
]);
