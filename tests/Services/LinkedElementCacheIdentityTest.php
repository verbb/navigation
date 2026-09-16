<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Category;
use verbb\navigation\nodetypes\Entry;

it('resolves the original link after a temporary incompatible type lookup', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    expect($node->getElement()?->id)->toBe($entry->id);
    $node->type = Category::class;
    expect($node->getElement())->toBeNull();
    $node->type = Entry::class;
    $node->setLinkedElementId([$entry->id]);
    expect($node->getElement()?->id)->toBe($entry->id);
    expect($node->getUrl())->toBe($entry->getUrl());
});

it('resolves a new linked identity after an earlier missing-element lookup', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = new Node(['menuId' => $menu->id, 'siteId' => $entry->siteId, 'type' => Entry::class]);
    expect($node->getElement())->toBeNull();
    $node->elementId = $entry->id;
    expect($node->getElement()?->id)->toBe($entry->id);
});

it('accepts an empty element-selector array when clearing a link', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $node->getElement();
    $node->setLinkedElementId([]);
    expect($node->elementId)->toBeNull();
    expect($node->getElement())->toBeNull();
});

it('retains batch-loaded cache misses until the linked identity changes', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = new Node(['menuId' => $menu->id, 'siteId' => $entry->siteId, 'type' => Category::class, 'elementId' => $entry->id]);
    N::$plugin->getNodeRead()->eagerLoadLinkedElements([$node]);
    $profile = QueryProfiler::profile(function() use ($node) {
        for ($i = 0; $i < 5; $i++) {
            expect($node->getElement())->toBeNull();
        }
    });
    expect($profile['queries'])->toBe(0);
    $node->type = Entry::class;
    expect($node->getElement()?->id)->toBe($entry->id);
});

it('resolves linked elements for a newly selected site and reuses unchanged selections', function() {
    $secondary = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    expect($node->getElement()?->siteId)->toBe($entry->siteId);
    $node->setElementSiteId($secondary->id);
    expect($node->getElement()?->siteId)->toBe($secondary->id);
    $profile = QueryProfiler::profile(function() use ($node, $secondary) {
        for ($i = 0; $i < 5; $i++) {
            expect($node->getElement()?->siteId)->toBe($secondary->id);
        }
    });
    expect($profile['queries'])->toBe(0);
});
