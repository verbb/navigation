<?php

declare(strict_types=1);

use craft\helpers\Gql as CraftGql;
use Tests\Support\Fixtures\NavigationFixtureFactory;

afterEach(function (): void {
    Craft::$app->getGql()->setActiveSchema(null);
});

it('returns nested children through GraphQL with NodeRead hierarchy wiring', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child A', '/parent/child-a', $parent);
    NavigationFixtureFactory::customNode($nav, 'Child B', '/parent/child-b', $parent);

    $schema = CraftGql::createFullAccessSchema();
    $query = <<<'GQL'
    query($handle: String!) {
      navigationNodes(navHandle: $handle, level: 1, withNodeHierarchy: true) {
        title
        children {
          title
        }
      }
    }
    GQL;

    $result = Craft::$app->getGql()->executeQuery($schema, $query, [
        'handle' => $nav->handle,
    ]);

    if (!empty($result['errors'])) {
        throw new RuntimeException('GraphQL query failed: ' . json_encode($result['errors']));
    }

    $nodes = $result['data']['navigationNodes'] ?? [];

    expect($nodes)->toHaveCount(1);
    expect($nodes[0]['title'] ?? null)->toBe('Parent');
    expect($nodes[0]['children'] ?? [])->toHaveCount(2);
});

it('returns projected dynamic section children through GraphQL', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    NavigationFixtureFactory::entries(2, $section);
    NavigationFixtureFactory::dynamicSectionNode($nav, $section);

    $schema = CraftGql::createFullAccessSchema();
    $query = <<<'GQL'
    query($handle: String!) {
      navigationNodes(navHandle: $handle, level: 1, withNodeHierarchy: true) {
        title
        isProjected
        children {
          title
          isProjected
          url
        }
      }
    }
    GQL;

    $result = Craft::$app->getGql()->executeQuery($schema, $query, [
        'handle' => $nav->handle,
    ]);

    if (!empty($result['errors'])) {
        throw new RuntimeException('GraphQL query failed: ' . json_encode($result['errors']));
    }

    $nodes = $result['data']['navigationNodes'] ?? [];

    expect($nodes)->toHaveCount(1);
    expect($nodes[0]['isProjected'] ?? null)->toBeFalse();
    expect($nodes[0]['children'] ?? [])->toHaveCount(2);
    expect($nodes[0]['children'][0]['isProjected'] ?? null)->toBeTrue();
    expect($nodes[0]['children'][1]['isProjected'] ?? null)->toBeTrue();
});

it('batch-hydrates linked elements through GraphQL withLinkedElements', function() {
    $nav = NavigationFixtureFactory::menu();

    foreach (NavigationFixtureFactory::entries(5) as $entry) {
        NavigationFixtureFactory::entryNode($nav, $entry);
    }

    $schema = CraftGql::createFullAccessSchema();
    $query = <<<'GQL'
    query($handle: String!) {
      navigationNodes(navHandle: $handle, withLinkedElements: true) {
        elementId
        element {
          id
        }
      }
    }
    GQL;

    $result = Craft::$app->getGql()->executeQuery($schema, $query, [
        'handle' => $nav->handle,
    ]);

    if (!empty($result['errors'])) {
        throw new RuntimeException('GraphQL query failed: ' . json_encode($result['errors']));
    }

    $linkedElements = 0;

    foreach ($result['data']['navigationNodes'] ?? [] as $node) {
        if (!empty($node['element']['id'])) {
            $linkedElements++;
        }
    }

    expect($linkedElements)->toBe(5);
});
