<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\variables\NavigationVariable;

it('auto-wires children on nav-scoped front-end reads', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 2, 3);

    WebRequestSimulator::withAbsoluteUrl(
        Craft::$app->getSites()->getPrimarySite()->getBaseUrl(),
        function() use ($nav) {
            $variable = new NavigationVariable();
            $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
            $roots = $variable->nodes($criteria)->level(1)->all();

            expect($roots)->toHaveCount(2);

            foreach ($roots as $root) {
                expect($root->getChildren())->toHaveCount(3);
            }
        },
    );
});

it('opts out of auto hierarchy with withNodeHierarchy(false)', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 2, 3);

    WebRequestSimulator::withAbsoluteUrl(
        Craft::$app->getSites()->getPrimarySite()->getBaseUrl(),
        function() use ($nav) {
            $roots = (new NavigationVariable())
                ->nodes(['handle' => $nav->handle])
                ->level(1)
                ->withNodeHierarchy(false)
                ->all();

            expect($roots)->toHaveCount(2);
            expect($roots[0]->getEagerLoadedElements('children'))->toBeNull();
        },
    );
});

it('forces hierarchy wiring with explicit withNodeHierarchy(true) in console', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 2, 2);

    $roots = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->level(1)
        ->withNodeHierarchy(true)
        ->all();

    expect($roots)->toHaveCount(2);
    expect($roots[0]->getChildren())->toHaveCount(2);
});

it('does not auto-wire hierarchy for unscoped queries', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 2, 2);

    WebRequestSimulator::withAbsoluteUrl(
        Craft::$app->getSites()->getPrimarySite()->getBaseUrl(),
        function() use ($nav) {
            $nodes = (new NavigationVariable())
                ->nodes(['siteId' => Craft::$app->getSites()->getPrimarySite()->id])
                ->level(1)
                ->all();

            expect($nodes)->not->toBeEmpty();
            expect($nodes[0]->getEagerLoadedElements('children'))->toBeNull();
        },
    );
});

it('preserves requested statuses in subset query children on cold and warm reads', function(string $scope) {
    $menu = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($menu, 'Earlier root', '/earlier');
    $parent = NavigationFixtureFactory::customNode($menu, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($menu, 'Enabled child', '/enabled', $parent);
    $disabled = NavigationFixtureFactory::customNode($menu, 'Disabled child', '/disabled', $parent);
    $disabled->enabled = false;
    expect(Craft::$app->getElements()->saveElement($disabled))->toBeTrue();
    $siteDisabled = NavigationFixtureFactory::customNode($menu, 'Site-disabled child', '/site-disabled', $parent);
    $siteDisabled->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($siteDisabled))->toBeTrue();
    $settings = \verbb\navigation\Navigation::$plugin->getSettings();
    $previousMode = $settings->cacheMode;
    $settings->cacheMode = 'auto';

    try {
        WebRequestSimulator::withAbsoluteUrl(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), function() use ($menu, $parent, $scope) {
            foreach (['enabled', null] as $status) {
                foreach (['cold', 'warm'] as $temperature) {
                    $query = \verbb\navigation\elements\Node::find()->menuId($menu->id)->withNodeHierarchy(true)->status($status);
                    match ($scope) {
                        'level' => $query->level(1),
                        'id' => $query->id($parent->id),
                        'limit' => $query->limit(2),
                        'offset' => $query->offset(1),
                    };
                    if ($status === null && $scope === 'level') {
                        $query->anyStatus();
                    }
                    $cache = \verbb\navigation\Navigation::$plugin->getNavigationCache();
                    expect($cache->getCachedNodes($query) !== null)->toBe($temperature === 'warm');
                    $nodes = $query->all();
                    $loaded = array_values(array_filter($nodes, fn($node) => $node->id === $parent->id))[0];
                    $titles = array_map(fn($child) => $child->title, $loaded->getChildren()->all());
                    expect($titles)->toBe($status === null ? ['Enabled child', 'Disabled child', 'Site-disabled child'] : ['Enabled child']);
                }
            }
        });
    } finally {
        $settings->cacheMode = $previousMode;
    }
})->with(['level', 'id', 'limit', 'offset']);
