<?php

declare(strict_types=1);

use craft\helpers\Gql as CraftGql;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\elements\Node;
use verbb\navigation\variables\NavigationVariable;

function oneHierarchyMenu(): array
{
    $menu = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($menu, 'Parent', '/one-parent');
    $child = NavigationFixtureFactory::customNode($menu, 'Child', '/one-parent/child', $parent);
    NavigationFixtureFactory::customNode($menu, 'Sibling', '/one-sibling');

    return [$menu, $parent, $child];
}

function oneHierarchyChildTitles(?Node $node): array
{
    expect($node)->toBeInstanceOf(Node::class);

    return array_map(fn($child) => $child->title, $node->getChildren()->all());
}

it('wires stored children for single-node reads on front-end menu queries', function(string $shape) {
    [$menu, $parent, $child] = oneHierarchyMenu();
    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/one-parent/child', function() use ($menu, $parent, $shape) {
        $query = (new NavigationVariable())->nodes($menu->handle);

        $node = match ($shape) {
            'one' => $query->one(),
            'explicit one' => $query->withNodeHierarchy(true)->one(),
            'nth' => $query->nth(0),
            'uid one' => $query->uid($parent->uid)->one(),
            'uid all' => $query->uid($parent->uid)->all()[0] ?? null,
        };

        expect($node?->id)->toBe($parent->id);
        expect(oneHierarchyChildTitles($node))->toBe(['Child']);
        expect($node->hasActiveChild())->toBeTrue();
        expect($node->getActive())->toBeTrue();
    });
})->with(['one', 'explicit one', 'nth', 'uid one', 'uid all']);

it('matches limit(1) for single-node reads with explicit hierarchy in console', function() {
    [$menu, $parent] = oneHierarchyMenu();

    $one = Node::find()->menuId($menu->id)->withNodeHierarchy(true)->one();
    $limited = Node::find()->menuId($menu->id)->withNodeHierarchy(true)->limit(1)->all()[0] ?? null;

    expect($one?->id)->toBe($parent->id);
    expect(oneHierarchyChildTitles($one))->toBe(oneHierarchyChildTitles($limited));
    expect(oneHierarchyChildTitles($one))->toBe(['Child']);
});

it('clears the single-read flag so the same query reads a filtered list afterwards', function() {
    [$menu, $parent] = oneHierarchyMenu();
    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/', function() use ($menu, $parent) {
        $query = (new NavigationVariable())->nodes($menu->handle);

        expect($query->one()?->id)->toBe($parent->id);
        expect($query->isFetchingOne())->toBeFalse();
        expect($query->limit)->toBeNull();

        // A filtered list builds its forest from its own results, so a flag left set would add children here.
        $listed = $query->title('Parent')->all();
        expect($listed)->toHaveCount(1);
        expect(oneHierarchyChildTitles($listed[0]))->toBe([]);
    });
});

it('wires stored children for an elementId() single-node read', function() {
    $menu = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $parent = NavigationFixtureFactory::entryNode($menu, $entry);
    NavigationFixtureFactory::customNode($menu, 'Child', '/entry-child', $parent);
    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/', function() use ($menu, $entry, $parent) {
        $node = (new NavigationVariable())->nodes($menu->handle)->elementId($entry->id)->one();

        expect($node?->id)->toBe($parent->id);
        expect(oneHierarchyChildTitles($node))->toBe(['Child']);
    });
});

it('returns children for a single node read through GraphQL', function() {
    [$menu, $parent] = oneHierarchyMenu();

    $result = Craft::$app->getGql()->executeQuery(CraftGql::createFullAccessSchema(), <<<'GQL'
    query($handle: String!) {
      navigationNode(navHandle: $handle, withNodeHierarchy: true) {
        id
        children {
          title
        }
      }
    }
    GQL, ['handle' => $menu->handle]);

    Craft::$app->getGql()->setActiveSchema(null);
    expect($result['errors'] ?? [])->toBe([]);
    expect((int)($result['data']['navigationNode']['id'] ?? 0))->toBe($parent->id);
    expect(array_column($result['data']['navigationNode']['children'] ?? [], 'title'))->toBe(['Child']);
});

it('treats filtered single-node reads like limit(1) while filtered lists keep their own forest', function() {
    [$menu, $parent] = oneHierarchyMenu();
    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/', function() use ($menu, $parent) {
        $variable = new NavigationVariable();
        $one = $variable->nodes($menu->handle)->title('Parent')->one();
        $limited = $variable->nodes($menu->handle)->title('Parent')->limit(1)->all()[0] ?? null;
        $listed = $variable->nodes($menu->handle)->title('Parent')->all()[0] ?? null;

        expect($one?->id)->toBe($parent->id);
        expect(oneHierarchyChildTitles($one))->toBe(oneHierarchyChildTitles($limited));
        expect(oneHierarchyChildTitles($one))->toBe(['Child']);
        expect(oneHierarchyChildTitles($listed))->toBe([]);
    });
});
