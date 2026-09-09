<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\dynamic\sources\EntrySectionDynamicSource;
use verbb\navigation\helpers\NodeOutputSafety;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\variables\NavigationVariable;

it('excludes disabled entries from dynamic section projections', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entries = NavigationFixtureFactory::entries(1, $section);
    $entry = $entries[0];

    $entry->enabled = false;
    if (!Craft::$app->getElements()->saveElement($entry)) {
        throw new RuntimeException('Failed disabling entry fixture: ' . json_encode($entry->getErrors()));
    }

    $dynamic = NavigationFixtureFactory::dynamicSectionNode($nav, $section);
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;

    $projected = EntrySectionDynamicSource::getProjectedChildren($dynamic, $siteId);
    expect($projected)->toHaveCount(0);

    $roots = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->level(1)
        ->withNodeHierarchy()
        ->all();

    expect($roots)->toHaveCount(1);
    expect($roots[0]->getChildren())->toHaveCount(0);
});

it('includes live entries in dynamic section projections', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    NavigationFixtureFactory::entries(2, $section);
    $dynamic = NavigationFixtureFactory::dynamicSectionNode($nav, $section);
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;

    expect(EntrySectionDynamicSource::getProjectedChildren($dynamic, $siteId))->toHaveCount(2);
});

it('includes disabled entries when pending projections are opted in', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entries = NavigationFixtureFactory::entries(1, $section);
    $entry = $entries[0];

    $entry->enabled = false;
    if (!Craft::$app->getElements()->saveElement($entry)) {
        throw new RuntimeException('Failed disabling entry fixture: ' . json_encode($entry->getErrors()));
    }

    $dynamic = NavigationFixtureFactory::dynamicSectionNode($nav, $section);
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $dynamicSources = \verbb\navigation\Navigation::$plugin->getDynamicSources();

    expect(EntrySectionDynamicSource::getProjectedChildren($dynamic, $siteId))->toHaveCount(0);

    $dynamicSources->includePendingProjections(true);

    try {
        expect(EntrySectionDynamicSource::getProjectedChildren($dynamic, $siteId))->toHaveCount(1);
    } finally {
        $dynamicSources->includePendingProjections(false);
    }

    $roots = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->level(1)
        ->includePendingProjections()
        ->withNodeHierarchy()
        ->all();

    expect($roots)->toHaveCount(1);
    expect($roots[0]->getChildren())->toHaveCount(1);
});

it('injects projections for nested dynamic nodes in flat hierarchy output', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    NavigationFixtureFactory::entries(1, $section);

    $root = NavigationFixtureFactory::customNode($nav, 'Root', '/root');
    NavigationFixtureFactory::dynamicSectionNode($nav, $section, $root);

    $flat = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->withNodeHierarchy()
        ->all();

    $projected = array_filter(
        $flat,
        static fn(mixed $node): bool => $node instanceof \verbb\navigation\models\ProjectedNode,
    );

    expect($projected)->toHaveCount(1);
});

it('evaluates sandboxed custom URL templates and rejects unsafe schemes', function() {
    $nav = NavigationFixtureFactory::menu();
    $safe = NavigationFixtureFactory::customNode($nav, 'Safe Twig', '{{ 6 * 7 }}');
    $unsafe = NavigationFixtureFactory::customNode($nav, 'Unsafe', 'javascript:alert(1)');

    expect($safe->getUrl())->toBe('42');
    expect($unsafe->getUrl())->toBeNull();

    $unsafe->classes = 'is-active';
    $unsafe->customAttributes = [
        ['attribute' => 'data-x', 'value' => '1'],
        ['attribute' => 'onclick', 'value' => 'alert(1)'],
    ];

    $markup = (string)$unsafe->getLinkAttributes();
    expect($markup)->toContain('data-x="1"');
    expect($markup)->not->toContain('onclick');
    expect(NodeOutputSafety::sanitizeUrl('javascript:alert(1)'))->toBeNull();
    expect($safe->type)->toBe(Custom::class);
});
