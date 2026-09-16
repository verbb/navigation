<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;
use verbb\navigation\variables\NavigationVariable;

it('retains stored levels and result order during hierarchy hydration', function(string $criteria) {
    $menu = F::menu();
    $root = F::customNode($menu, $criteria === 'alphabetic' ? 'A root' : 'Match root', '/root');
    $middle = F::customNode($menu, $criteria === 'alphabetic' ? 'B middle' : 'Other middle', '/middle', $root);
    $leaf = F::customNode($menu, $criteria === 'alphabetic' ? 'D leaf' : 'Match leaf', '/leaf', $middle);
    F::customNode($menu, $criteria === 'alphabetic' ? 'C root' : 'Other root', '/other');
    $query = static function() use ($menu, $criteria) {
        $query = Node::find()->menuId($menu->id);
        return match ($criteria) {
            'reverse' => $query->orderBy('lft desc'),
            'alphabetic' => $query->orderBy('title asc'),
            default => $query->title('Match*'),
        };
    };
    $raw = $query()->withNodeHierarchy(false)->all();
    $expected = array_map(static fn(Node $node) => [$node->id, $node->level], $raw);
    W::withAbsoluteUrl('https://hierarchy.test/', function() use ($query, $expected, $leaf, $middle) {
        foreach ([1, 2] as $read) {
            $nodes = $query()->all();
            expect(array_map(static fn(Node $node) => [$node->id, $node->level], $nodes))->toBe($expected);
            $loadedLeaf = array_values(array_filter($nodes, static fn(Node $node) => $node->id === $leaf->id))[0];
            expect($loadedLeaf->getParentId())->toBe($middle->id);
        }
    });
})->with(['reverse', 'filtered', 'alphabetic']);

it('does not move a filtered node when only its title is saved', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'Root', '/root');
    $middle = F::customNode($menu, 'Middle', '/middle', $root);
    $leaf = F::customNode($menu, 'Selected leaf', '/leaf', $middle);
    $before = Node::find()->id($leaf->id)->withNodeHierarchy(false)->one();
    W::withAbsoluteUrl('https://hierarchy.test/', function() use ($menu) {
        $loaded = Node::find()->menuId($menu->id)->title('Selected leaf')->one();
        $loaded->title = 'Renamed leaf';
        expect(Craft::$app->elements->saveElement($loaded))->toBeTrue();
    });
    $after = Node::find()->id($leaf->id)->withNodeHierarchy(false)->one();
    expect($after->title)->toBe('Renamed leaf');
    expect([$after->level, $after->lft, $after->rgt, $after->getParentId()])
        ->toBe([$before->level, $before->lft, $before->rgt, $middle->id]);
});

it('keeps filtered forests without rewriting stored node metadata', function(bool $direct) {
    $menu = F::menu();
    $root = F::customNode($menu, 'Match root', '/root');
    $middle = F::customNode($menu, 'Middle', '/middle', $root);
    $leaf = F::customNode($menu, 'Match leaf', '/leaf', $middle);
    W::withAbsoluteUrl('https://hierarchy.test/', function() use ($menu, $root, $middle, $leaf, $direct) {
        if ($direct) {
            $nodes = Node::find()->menuId($menu->id)->title('Match*')->withNodeHierarchy(false)->all();
            $tree = Navigation::$plugin->getNodeRead()->buildNodeTree($nodes);
            expect($nodes[1]->getParentId())->toBe($middle->id);
            expect($nodes[1]->level)->toBe(3);
        } else {
            $tree = (new NavigationVariable())->tree(['menuId' => $menu->id, 'title' => 'Match*']);
        }
        expect(array_column($tree, 'id'))->toBe([$root->id, $leaf->id]);
        expect(array_column($tree, 'level'))->toBe([1, 3]);
        expect(array_map(static fn(array $row) => $row['children'] ?? [], $tree))->toBe([[], []]);
    });
})->with([false, true]);

it('keeps stored results and Dynamic children when sorting interleaves branches', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'A root', '/root');
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $dynamic = F::dynamicSectionNode($menu, $section, $root);
    $dynamic->title = 'B dynamic';
    expect(Craft::$app->elements->saveElement($dynamic))->toBeTrue();
    $leaf = F::customNode($menu, 'D leaf', '/leaf', $dynamic);
    $other = F::customNode($menu, 'C root', '/other');
    W::withAbsoluteUrl('https://hierarchy.test/', function() use ($menu, $root, $dynamic, $leaf, $other, $entry) {
        $nodes = Node::find()->menuId($menu->id)->orderBy('title asc')->all();
        $stored = array_values(array_filter($nodes, static fn($node) => $node instanceof Node));
        $projected = array_values(array_filter($nodes, static fn($node) => $node instanceof verbb\navigation\models\ProjectedNode));
        expect(array_column($stored, 'id'))->toBe([$root->id, $dynamic->id, $other->id, $leaf->id]);
        expect($projected)->toHaveCount(1);
        expect($projected[0]->id)->toBe('projected:' . $entry->id);
        expect($projected[0]->level)->toBe(3);
        $tree = Navigation::$plugin->getNodeRead()->buildNodeTree($nodes, false, true);
        expect(array_column($tree, 'id'))->toBe([$root->id, $other->id]);
        expect(array_column($tree[0]['children'][0]['children'], 'id'))->toBe([$leaf->id, $projected[0]->id]);
    });
});

it('rebuilds cached filtered hierarchies written with incorrect structure levels', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'Root', '/root');
    $middle = F::customNode($menu, 'Middle', '/middle', $root);
    $middle->type = verbb\navigation\nodetypes\Passive::class;
    expect(Craft::$app->elements->saveElement($middle))->toBeTrue();
    $leaf = F::customNode($menu, 'Leaf', '/leaf', $middle);
    $settings = Navigation::$plugin->getSettings();
    $before = [$settings->cacheMode, $settings->cacheProfile];
    $settings->cacheMode = 'auto';
    $settings->cacheProfile = 'standard';

    try {
        W::withAbsoluteUrl('https://hierarchy.test/', function() use ($menu, $root, $middle, $leaf) {
            $cache = Navigation::$plugin->getNavigationCache();
            $query = Node::find()->menuId($menu->id)->type(verbb\navigation\nodetypes\Custom::class);
            expect($cache->shouldCacheQuery($query))->toBeTrue();
            $rawQuery = (clone $query)->withNodeHierarchy(false);
            $rawQuery->bypassReadCache = true;
            $raw = $rawQuery->all();
            $payload = $cache->exportNodes($raw, 'standard');
            // The previous cache format persisted orphaned matches as level-one roots.
            $payload['nodes'][1]['level'] = 1;
            $legacyKey = sprintf('navigation:tree:v5:%s:%s:standard:%s', $menu->uid, $root->siteId, $cache->buildCriteriaHash($query));
            Craft::$app->cache->set($legacyKey, $payload);
            try {
                foreach (['cold', 'warm'] as $temperature) {
                    $freshQuery = Node::find()->menuId($menu->id)->type(verbb\navigation\nodetypes\Custom::class);
                    if ($temperature === 'warm') {
                        expect($cache->getCachedNodes($freshQuery))->not->toBeNull();
                    }
                    $nodes = $freshQuery->all();
                    expect(array_map(static fn(Node $node) => [$node->id, $node->level], $nodes))->toBe([[$root->id, 1], [$leaf->id, 3]]);
                }
                $nodes[1]->title = 'Renamed cached leaf';
                expect(Craft::$app->elements->saveElement($nodes[1]))->toBeTrue();
                $saved = Node::find()->id($leaf->id)->withNodeHierarchy(false)->one();
                expect([$saved->level, $saved->getParentId()])->toBe([3, $middle->id]);
            } finally {
                Craft::$app->cache->delete($legacyKey);
            }
        });
    } finally {
        [$settings->cacheMode, $settings->cacheProfile] = $before;
    }
});
