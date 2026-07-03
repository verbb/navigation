<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\variables\NavigationVariable;

it('captures baseline query counts for core navigation read APIs', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child A', '/parent/child-a', $parent);
    NavigationFixtureFactory::customNode($nav, 'Child B', '/parent/child-b', $parent);

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();

    $profiles = [
        'nodes' => QueryProfiler::profile(fn() => $variable->nodes($criteria)->all()),
        'tree' => QueryProfiler::profile(fn() => $variable->tree($criteria)),
        'render' => QueryProfiler::profile(fn() => WebRequestSimulator::withAbsoluteUrl($siteUrl, fn() => (string)$variable->render($criteria))),
    ];

    foreach ($profiles as $profile) {
        expect($profile['queries'])->toBeGreaterThan(0);
        expect($profile['durationMs'])->toBeGreaterThanOrEqual(0.0);
    }

    fwrite(STDERR, "\nNavigation perf smoke profile: " . json_encode($profiles, JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures linked element hydration costs for entry-backed nodes', function() {
    $nav = NavigationFixtureFactory::menu();

    foreach (NavigationFixtureFactory::entries(10) as $entry) {
        NavigationFixtureFactory::entryNode($nav, $entry);
    }

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $profiles = [
        'query-only' => QueryProfiler::profile(fn() => $variable->nodes($criteria)->all()),
        'touch-linked-elements' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $nodes = $variable->nodes($criteria)->all();
            $linkedElements = 0;

            foreach ($nodes as $node) {
                if ($node->getElement()) {
                    $linkedElements++;
                }
            }

            return $linkedElements;
        }),
    ];

    expect($profiles['query-only']['queries'])->toBeGreaterThan(0);
    expect($profiles['touch-linked-elements']['queries'])->toBeGreaterThan($profiles['query-only']['queries']);

    fwrite(STDERR, "\nNavigation linked element perf profile: " . json_encode($profiles, JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures batched linked element hydration improvements', function() {
    $nav = NavigationFixtureFactory::menu();

    foreach (NavigationFixtureFactory::entries(10) as $entry) {
        NavigationFixtureFactory::entryNode($nav, $entry);
    }

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $profiles = [
        'lazy-touch-linked-elements' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $linkedElements = 0;

            foreach ($variable->nodes($criteria)->all() as $node) {
                if ($node->getElement()) {
                    $linkedElements++;
                }
            }

            return $linkedElements;
        }),
        'batched-touch-linked-elements' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $nodes = $variable->nodes($criteria)->withLinkedElements()->all();

            $linkedElements = 0;

            foreach ($nodes as $node) {
                if ($node->getElement()) {
                    $linkedElements++;
                }
            }

            return $linkedElements;
        }),
    ];

    expect($profiles['batched-touch-linked-elements']['resultSize'])->toBe(10);
    expect($profiles['batched-touch-linked-elements']['queries'])->toBeLessThan($profiles['lazy-touch-linked-elements']['queries']);

    fwrite(STDERR, "\nNavigation batched linked element perf profile: " . json_encode($profiles, JSON_PRETTY_PRINT) . "\n");
})->group('perf');
