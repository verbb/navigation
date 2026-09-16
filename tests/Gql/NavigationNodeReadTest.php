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
          data
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
    expect(array_column($nodes[0]['children'], 'data'))->toBe([null, null]);
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

it('returns stored node data as JSON strings through GraphQL', function(array $data) {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Data node', '/data-node');
    $node->data = $data;
    expect(Craft::$app->getElements()->saveElement($node))->toBeTrue();

    $result = Craft::$app->getGql()->executeQuery(
        CraftGql::createFullAccessSchema(),
        'query($handle: String!) { navigationNodes(menuHandle: $handle) { data } }',
        ['handle' => $nav->handle],
    );

    expect($result['errors'] ?? [])->toBe([]);
    $value = $result['data']['navigationNodes'][0]['data'];
    expect($value)->toBeString();
    expect(json_decode($value, true, 512, JSON_THROW_ON_ERROR))->toBe($data);
})->with([
    'empty data' => [[]],
    'populated data' => [['label' => 'A "quoted" label', 'count' => 0, 'enabled' => false, 'nested' => ['tags' => ['one', 'two']]]],
]);

it('returns projected interface fields without exposing linked content', function() {
    $menu = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    [$entry] = NavigationFixtureFactory::entries(1, $section);
    NavigationFixtureFactory::dynamicSectionNode($menu, $section);
    $schema = new \craft\models\GqlSchema(['name' => 'Menu links only', 'scope' => ['navigationMenus.' . $menu->uid . ':read']]);
    $query = 'query($handle: String!) { navigationNodes(menuHandle: $handle, level: 1, withNodeHierarchy: true) { children { id uid siteId siteHandle language uri dateCreated root structureId navId navHandle navName element { id } } } }';
    $result = Craft::$app->getGql()->executeQuery($schema, $query, ['handle' => $menu->handle]);

    expect($result['errors'] ?? [])->toBe([]);
    expect($result['data']['navigationNodes'][0]['children'])->toBe([[
        'id' => 'projected:' . $entry->id,
        'uid' => 'projected:' . $entry->uid,
        'siteId' => $entry->siteId,
        'siteHandle' => $entry->getSite()->handle,
        'language' => $entry->getSite()->language,
        'uri' => $entry->uri,
        'dateCreated' => null,
        'root' => null,
        'structureId' => null,
        'navId' => $menu->id,
        'navHandle' => $menu->handle,
        'navName' => $menu->name,
        'element' => null,
    ]]);
});

it('includes projected nodes in the validated GraphQL interface schema', function() {
    NavigationFixtureFactory::menu();
    $schema = CraftGql::createFullAccessSchema();
    $result = Craft::$app->getGql()->executeQuery($schema, '{ __type(name: "NodeInterface") { possibleTypes { name } } }');

    expect($result['errors'] ?? [])->toBe([]);
    expect(array_column($result['data']['__type']['possibleTypes'], 'name'))->toContain('ProjectedNavigationNode');
    $definition = Craft::$app->getGql()->getSchemaDef($schema, true);
    expect(array_map(fn($error) => $error->getMessage(), $definition->validate()))->toBe([]);
});
