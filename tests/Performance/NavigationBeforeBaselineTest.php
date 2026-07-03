<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\variables\NavigationVariable;

it('captures flat and deep custom-node tree baselines', function() {
    $variable = new NavigationVariable();
    $siteId = Craft::$app->getSites()->getPrimarySite()->id;

    $flatNav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::flatCustomNodes($flatNav, 50);

    $deepNav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::deepCustomNodes($deepNav, 12);

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();

    $profiles = [
        'flat.nodes' => QueryProfiler::profile(fn() => $variable->nodes(['handle' => $flatNav->handle, 'siteId' => $siteId])->all()),
        'flat.tree' => QueryProfiler::profile(fn() => $variable->tree(['handle' => $flatNav->handle, 'siteId' => $siteId])),
        'flat.render' => QueryProfiler::profile(fn() => WebRequestSimulator::withAbsoluteUrl($siteUrl, fn() => (string)$variable->render(['handle' => $flatNav->handle, 'siteId' => $siteId]))),
        'deep.nodes' => QueryProfiler::profile(fn() => $variable->nodes(['handle' => $deepNav->handle, 'siteId' => $siteId])->all()),
        'deep.tree' => QueryProfiler::profile(fn() => $variable->tree(['handle' => $deepNav->handle, 'siteId' => $siteId])),
        'deep.render' => QueryProfiler::profile(fn() => WebRequestSimulator::withAbsoluteUrl($siteUrl, fn() => (string)$variable->render(['handle' => $deepNav->handle, 'siteId' => $siteId]))),
    ];

    foreach ($profiles as $profile) {
        expect($profile['queries'])->toBeGreaterThan(0);
    }

    expect($profiles['flat.tree']['resultSize'])->toBe(50);
    expect($profiles['deep.tree']['resultSize'])->toBe(1);
    expect($profiles['flat.tree']['queries'])->toBeLessThanOrEqual(2);
    expect($profiles['deep.tree']['queries'])->toBeLessThanOrEqual(2);
    expect($profiles['flat.render']['queries'])->toBeLessThanOrEqual(2);
    expect($profiles['deep.render']['queries'])->toBeLessThanOrEqual(2);

    fwrite(STDERR, "\nNavigation shape baseline: " . json_encode(compactNavigationProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures eager-loading effects for child access', function() {
    $nav = NavigationFixtureFactory::menu();

    for ($i = 1; $i <= 5; $i++) {
        $parent = NavigationFixtureFactory::customNode($nav, "Parent {$i}", "/parent-{$i}");

        for ($j = 1; $j <= 3; $j++) {
            NavigationFixtureFactory::customNode($nav, "Child {$i}.{$j}", "/parent-{$i}/child-{$j}", $parent);
        }
    }

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $profiles = [
        'query-only' => QueryProfiler::profile(fn() => $variable->nodes($criteria)->level(1)->all()),
        'touch-children' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $children = 0;

            foreach ($variable->nodes($criteria)->level(1)->all() as $node) {
                $children += count($node->children->all());
            }

            return $children;
        }),
        'with-children-touch-children' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $children = 0;

            foreach ($variable->nodes($criteria)->level(1)->with(['children'])->all() as $node) {
                $children += count($node->children);
            }

            return $children;
        }),
        'with-node-hierarchy-touch-children' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $children = 0;

            foreach ($variable->nodes($criteria)->level(1)->withNodeHierarchy()->all() as $node) {
                $children += count($node->getChildren());
            }

            return $children;
        }),
    ];

    expect($profiles['touch-children']['queries'])->toBeGreaterThan($profiles['query-only']['queries']);
    expect($profiles['with-node-hierarchy-touch-children']['resultSize'])->toBe(15);
    expect($profiles['with-node-hierarchy-touch-children']['queries'])->toBeLessThan($profiles['touch-children']['queries']);
    expect($profiles['with-node-hierarchy-touch-children']['queries'])->toBeLessThanOrEqual(3);

    fwrite(STDERR, "\nNavigation eager-loading baseline: " . json_encode(compactNavigationProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures mixed node type and linked element baselines', function() {
    $nav = NavigationFixtureFactory::menu();
    $secondarySite = NavigationFixtureFactory::secondarySite();

    NavigationFixtureFactory::customNode($nav, 'Custom Node', '/custom-node');
    NavigationFixtureFactory::passiveNode($nav, 'Passive Group');
    NavigationFixtureFactory::siteNode($nav, $secondarySite);

    foreach (NavigationFixtureFactory::entries(5) as $entry) {
        NavigationFixtureFactory::entryNode($nav, $entry);
    }

    foreach (NavigationFixtureFactory::categories(5) as $category) {
        NavigationFixtureFactory::categoryNode($nav, $category);
    }

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $profiles = [
        'query-only' => QueryProfiler::profile(fn() => $variable->nodes($criteria)->all()),
        'touch-urls' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $urls = 0;

            foreach ($variable->nodes($criteria)->all() as $node) {
                if ($node->getUrl() !== null) {
                    $urls++;
                }
            }

            return $urls;
        }),
        'touch-linked-elements' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $linkedElements = 0;

            foreach ($variable->nodes($criteria)->all() as $node) {
                if ($node->getElement()) {
                    $linkedElements++;
                }
            }

            return $linkedElements;
        }),
        'with-linked-elements' => QueryProfiler::profile(function() use ($variable, $criteria): int {
            $linkedElements = 0;

            foreach ($variable->nodes($criteria)->withLinkedElements()->all() as $node) {
                if ($node->getElement()) {
                    $linkedElements++;
                }
            }

            return $linkedElements;
        }),
        'render' => QueryProfiler::profile(fn() => WebRequestSimulator::withAbsoluteUrl(
            Craft::$app->getSites()->getPrimarySite()->getBaseUrl(),
            fn() => (string)$variable->render($criteria),
        )),
    ];

    expect($profiles['touch-linked-elements']['queries'])->toBeGreaterThan($profiles['query-only']['queries']);
    expect($profiles['with-linked-elements']['resultSize'])->toBeGreaterThan(0);
    expect($profiles['with-linked-elements']['queries'])->toBeLessThan($profiles['touch-linked-elements']['queries']);
    expect($profiles['with-linked-elements']['queries'])->toBeLessThanOrEqual(3);

    fwrite(STDERR, "\nNavigation mixed node baseline: " . json_encode(compactNavigationProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures custom field access baselines on nodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $field = NavigationFixtureFactory::addPlainTextFieldToMenu($nav);

    for ($i = 1; $i <= 20; $i++) {
        NavigationFixtureFactory::customNodeWithField($nav, $field, "Field Value {$i}");
    }

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $profiles = [
        'query-only' => QueryProfiler::profile(fn() => $variable->nodes($criteria)->all()),
        'touch-custom-field' => QueryProfiler::profile(function() use ($variable, $criteria, $field): int {
            $values = 0;

            foreach ($variable->nodes($criteria)->all() as $node) {
                if ($node->getFieldValue($field->handle)) {
                    $values++;
                }
            }

            return $values;
        }),
    ];

    expect($profiles['query-only']['queries'])->toBeGreaterThan(0);
    expect($profiles['touch-custom-field']['resultSize'])->toBe(20);

    fwrite(STDERR, "\nNavigation custom field baseline: " . json_encode(compactNavigationProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

it('captures multisite query baselines', function() {
    $secondarySite = NavigationFixtureFactory::secondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();

    NavigationFixtureFactory::customNode($nav, 'Primary Site Node', '/primary-site-node', null, $primarySite->id);
    NavigationFixtureFactory::customNode($nav, 'Secondary Site Node', '/secondary-site-node', null, $secondarySite->id);

    $variable = new NavigationVariable();

    $profiles = [
        'primary-site' => QueryProfiler::profile(fn() => $variable->nodes(['handle' => $nav->handle, 'siteId' => $primarySite->id])->all()),
        'secondary-site' => QueryProfiler::profile(fn() => $variable->nodes(['handle' => $nav->handle, 'siteId' => $secondarySite->id])->all()),
        'all-sites-unique' => QueryProfiler::profile(fn() => $variable->nodes(['handle' => $nav->handle])->site('*')->unique()->all()),
    ];

    foreach ($profiles as $profile) {
        expect($profile['queries'])->toBeGreaterThan(0);
    }

    fwrite(STDERR, "\nNavigation multisite baseline: " . json_encode(compactNavigationProfiles($profiles), JSON_PRETTY_PRINT) . "\n");
})->group('perf');

function compactNavigationProfiles(array $profiles): array
{
    return array_map(static fn(array $profile): array => [
        'resultType' => $profile['resultType'],
        'resultSize' => $profile['resultSize'],
        'durationMs' => $profile['durationMs'],
        'queries' => $profile['queries'],
        'duplicatePatterns' => $profile['duplicatePatterns'],
    ], $profiles);
}
