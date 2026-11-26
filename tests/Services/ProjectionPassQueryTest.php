<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\elements\Node;

/**
 * The projection pass only has work to do when Dynamic projections are attached,
 * which happens during hierarchy wiring. Without wiring it must not query children.
 */
function projectionPassTree(int $parents, int $children): array
{
    $menu = F::menu();
    $nodes = [];

    for ($i = 1; $i <= $parents; $i++) {
        $parent = F::customNode($menu, "P{$i}", "/projection-pass/{$i}");
        $nodes["P{$i}"] = $parent;

        for ($j = 1; $j <= $children; $j++) {
            $nodes["P{$i}C{$j}"] = F::customNode($menu, "P{$i}C{$j}", "/projection-pass/{$i}/{$j}", $parent);
        }
    }

    return [$menu, $nodes];
}

function projectionPassProfile(Closure $query): array
{
    // Warm the read cache first so both sides of a comparison measure the same path.
    $query()->all();

    $nodes = [];
    $profile = QueryProfiler::profile(function() use ($query, &$nodes) {
        $nodes = $query()->all();

        return $nodes;
    });

    $flags = [];

    foreach ($nodes as $node) {
        $flags[$node->title] = [$node->getCurrent(), $node->getActive(), $node->hasActiveChild()];

        foreach ($node->getEagerLoadedElements('children') ?? [] as $child) {
            $flags[$child->title] = [$child->getCurrent(), $child->getActive(), $child->hasActiveChild()];
        }
    }

    return [$profile['queries'], $flags];
}

it('does not query children per node for un-wired flat queries', function() {
    [$menu] = projectionPassTree(3, 3);

    W::withAbsoluteUrl('https://projection-pass.test/projection-pass/2/3', function() use ($menu) {
        [$withPass, $withPassFlags] = projectionPassProfile(fn() => Node::find()->menuId($menu->id)->withNodeHierarchy(false));
        [$withoutPass, $withoutPassFlags] = projectionPassProfile(fn() => Node::find()->menuId($menu->id)->withNodeHierarchy(false)->withProjectedChildren(false));

        expect($withPass)->toBe($withoutPass);
        expect($withPassFlags)->toBe($withoutPassFlags);
        expect($withPassFlags['P2C3'])->toBe([true, true, false]);
        expect($withPassFlags['P2'])->toBe([false, true, true]);
    });
});

it('does not query grandchildren for explicitly eager-loaded children', function() {
    [$menu] = projectionPassTree(3, 3);

    W::withAbsoluteUrl('https://projection-pass.test/projection-pass/2/3', function() use ($menu) {
        [$withPass, $withPassFlags] = projectionPassProfile(fn() => Node::find()->menuId($menu->id)->level(1)->withNodeHierarchy(false)->with(['children']));
        [$withoutPass, $withoutPassFlags] = projectionPassProfile(fn() => Node::find()->menuId($menu->id)->level(1)->withNodeHierarchy(false)->with(['children'])->withProjectedChildren(false));

        expect($withPass)->toBe($withoutPass);
        expect($withPassFlags)->toBe($withoutPassFlags);
        expect($withPassFlags['P2C3'])->toBe([true, true, false]);
        expect($withPassFlags['P2'])->toBe([false, true, true]);
    });
});

it('does not query children for menu-less queries', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];

    for ($i = 1; $i <= 4; $i++) {
        $parent = F::entryNode($menu, $entry);
        F::customNode($menu, "Child {$i}", "/projection-pass/child-{$i}", $parent);
    }

    W::withAbsoluteUrl('https://projection-pass.test/projection-pass/elsewhere', function() use ($entry) {
        [$withPass] = projectionPassProfile(fn() => Node::find()->elementId($entry->id));
        [$withoutPass] = projectionPassProfile(fn() => Node::find()->elementId($entry->id)->withProjectedChildren(false));

        expect($withPass)->toBe($withoutPass);
    });
});

it('leaves un-wired root-only queries to their in-memory hierarchy', function() {
    [$menu] = projectionPassTree(3, 3);

    W::withAbsoluteUrl('https://projection-pass.test/projection-pass/2/3', function() use ($menu) {
        [$withPass, $withPassFlags] = projectionPassProfile(fn() => Node::find()->menuId($menu->id)->level(1)->withNodeHierarchy(false));
        [$withoutPass, $withoutPassFlags] = projectionPassProfile(fn() => Node::find()->menuId($menu->id)->level(1)->withNodeHierarchy(false)->withProjectedChildren(false));

        // No children are loaded, so the root has no in-memory active child. It is
        // still active through its own path section.
        expect($withPass)->toBe($withoutPass);
        expect($withPassFlags)->toBe($withoutPassFlags);
        expect($withPassFlags['P2'])->toBe([false, true, false]);
    });
});
