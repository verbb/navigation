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
