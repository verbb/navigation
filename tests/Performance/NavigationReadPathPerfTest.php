<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\variables\NavigationVariable;

it('captures wide branching tree and render baselines', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 10, 5);

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();

    $profiles = [
        'nodes' => QueryProfiler::profile(fn() => $variable->nodes($criteria)->all()),
        'tree' => QueryProfiler::profile(fn() => $variable->tree($criteria)),
        'render' => QueryProfiler::profile(fn() => WebRequestSimulator::withAbsoluteUrl($siteUrl, fn() => (string)$variable->render($criteria))),
    ];

    expect($profiles['nodes']['resultSize'])->toBe(60);
    expect($profiles['tree']['resultSize'])->toBe(10);
    expect($profiles['tree']['queries'])->toBeLessThanOrEqual(2);
    expect($profiles['render']['queries'])->toBeLessThanOrEqual(2);

    fwrite(STDERR, "\nNavigation wide tree baseline: " . json_encode(compactReadProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures combined linked element and hierarchy flags', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');

    foreach (NavigationFixtureFactory::entries(5) as $entry) {
        NavigationFixtureFactory::entryNode($nav, $entry, $parent);
    }

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $profiles = [
        'lazy' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $linked = 0;

            foreach ($variable->nodes($criteria)->level(1)->all() as $node) {
                $linked += count($node->children->all());

                foreach ($node->children->all() as $child) {
                    if ($child->getElement()) {
                        $linked++;
                    }
                }
            }

            return $linked;
        }),
        'optimized' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $linked = 0;

            foreach ($variable->nodes($criteria)->level(1)->withNodeHierarchy()->withLinkedElements()->all() as $node) {
                $linked += count($node->getChildren());

                foreach ($node->getChildren() as $child) {
                    if ($child->getElement()) {
                        $linked++;
                    }
                }
            }

            return $linked;
        }),
    ];

    expect($profiles['optimized']['resultSize'])->toBeGreaterThan(0);
    expect($profiles['optimized']['queries'])->toBeLessThan($profiles['lazy']['queries']);
    expect($profiles['optimized']['queries'])->toBeLessThanOrEqual(4);

    fwrite(STDERR, "\nNavigation combined read flags baseline: " . json_encode(compactReadProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures deep entry-backed render baseline', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = null;

    foreach (NavigationFixtureFactory::entries(8) as $index => $entry) {
        $parent = NavigationFixtureFactory::entryNode(
            $nav,
            $entry,
            $parent,
        );
    }

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();

    $profiles = [
        'nodes-only' => QueryProfiler::profile(fn() => $variable->nodes($criteria)->all()),
        'tree' => QueryProfiler::profile(fn() => $variable->tree($criteria)),
        'render' => QueryProfiler::profile(fn() => WebRequestSimulator::withAbsoluteUrl($siteUrl, fn() => (string)$variable->render($criteria))),
    ];

    expect($profiles['tree']['resultSize'])->toBe(1);
    expect($profiles['render']['queries'])->toBeLessThanOrEqual(3);
    expect($profiles['tree']['queries'])->toBeGreaterThan($profiles['nodes-only']['queries']);

    fwrite(STDERR, "\nNavigation deep entry render baseline: " . json_encode(compactReadProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures id-scoped hierarchy wiring baseline', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child A', '/parent/a', $parent);
    NavigationFixtureFactory::customNode($nav, 'Child B', '/parent/b', $parent);

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
    $parentNode = $variable->nodes($criteria)->level(1)->one();

    $profiles = [
        'id-scoped-with-hierarchy' => QueryProfiler::profile(function() use ($variable, $criteria, $parentNode): int {
            $children = 0;

            foreach ($variable->nodes($criteria)->id($parentNode->id)->withNodeHierarchy()->all() as $node) {
                $children += count($node->getChildren());
            }

            return $children;
        }),
    ];

    expect($profiles['id-scoped-with-hierarchy']['resultSize'])->toBe(2);
    expect($profiles['id-scoped-with-hierarchy']['queries'])->toBeLessThanOrEqual(3);

    fwrite(STDERR, "\nNavigation id-scoped hierarchy baseline: " . json_encode(compactReadProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

function compactReadProfiles(array $profiles): array
{
    return array_map(static fn(array $profile): array => [
        'resultType' => $profile['resultType'],
        'resultSize' => $profile['resultSize'],
        'durationMs' => $profile['durationMs'],
        'queries' => $profile['queries'],
        'duplicatePatterns' => $profile['duplicatePatterns'],
    ], $profiles);
}
