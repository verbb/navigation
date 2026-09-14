<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;

it('preserves a visible grandchild after filtering its parent from a public flat read', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'Root', '/root');
    $hidden = F::customNode($menu, 'Hidden', '/hidden', $root);
    $child = F::customNode($menu, 'Visible grandchild', '/grandchild', $hidden);
    $hidden->enabled = false;
    expect(Craft::$app->elements->saveElement($hidden))->toBeTrue();
    W::withAbsoluteUrl('https://read-interactions.test/grandchild', function() use ($menu, $root, $child) {
        foreach ([1, 2] as $read) {
            $nodes = Node::find()->menuId($menu->id)->limit(null)->all();
            expect(array_column($nodes, 'id'))->toBe([$root->id, $child->id]);
        }
    });
});

it('resolves descendant active state for root-only queries on cold and cached reads', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'Root', '/unrelated-root');
    $child = F::customNode($menu, 'Child', '/current', $root);
    W::withAbsoluteUrl('https://read-interactions.test/current', function() use ($menu, $root, $child) {
        $cache = N::$plugin->getNavigationCache();
        foreach ([1, 2] as $read) {
            $query = Node::find()->menuId($menu->id)->level(1);
            expect($cache->shouldCacheQuery($query))->toBeTrue();
            if ($read === 2) {
                expect($cache->getCachedNodes($query))->not->toBeNull();
            }
            $nodes = $query->all();
            expect(array_column($nodes, 'id'))->toBe([$root->id]);
            expect($nodes[0]->getChildren()->one()->id)->toBe($child->id);
            expect($nodes[0]->getChildren()->one()->getCurrent())->toBeTrue();
            expect($nodes[0]->hasActiveChild())->toBeTrue();
            expect($nodes[0]->getActive())->toBeTrue();
            $profile = QueryProfiler::profile(function() use ($nodes) {
                N::$plugin->getActiveMatcher()->resolve($nodes);
                return $nodes;
            });
            expect($profile['queries'])->toBe(0);
        }
    });
});

it('keeps localized linked node titles synchronized without overriding authored titles', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    $linked = Craft::$app->elements->getElementById($entry->id, craft\elements\Entry::class, $site->id);
    $localized->setElementSiteId($site->id);
    $localized->title = $linked->title;
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    $authored = F::entryNode($menu, $entry);
    $authored = Node::find()->id($authored->id)->siteId($site->id)->status(null)->one();
    $authored->setElementSiteId($site->id);
    $authored->title = 'Authored title';
    expect(Craft::$app->elements->saveElement($authored, true, false))->toBeTrue();

    $linked->title = 'Updated localized entry';
    expect(Craft::$app->elements->saveElement($linked, true, false))->toBeTrue();
    expect(Node::find()->id($node->id)->siteId($site->id)->one()->title)->toBe('Updated localized entry');
    expect(Node::find()->id($authored->id)->siteId($site->id)->one()->title)->toBe('Authored title');
});

it('restores a localized node after its linked entry is restored', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    $localized->setElementSiteId($site->id);
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    $linked = Craft::$app->elements->getElementById($entry->id, craft\elements\Entry::class, $site->id);
    expect(Craft::$app->elements->deleteElement($linked))->toBeTrue();
    expect(Node::find()->id($node->id)->siteId($site->id)->one())->toBeNull();
    expect(Craft::$app->elements->restoreElement($linked))->toBeTrue();
    expect(Node::find()->id($node->id)->siteId($site->id)->one()?->id)->toBe($node->id);
});

it('disables every site when a linked entry is deleted and restores each site status', function(bool $linkToLocalSite, bool $secondaryEnabled) {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $localized = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    $localized->setElementSiteId($linkToLocalSite ? $site->id : $entry->siteId);
    $localized->setEnabledForSite($secondaryEnabled);
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();

    expect(Craft::$app->elements->deleteElement($entry))->toBeTrue();
    $variants = Node::find()->id($node->id)->site('*')->status(null)->all();
    expect($variants)->toHaveCount(2);
    foreach ($variants as $variant) {
        expect($variant->getEnabledForSite())->toBeFalse();
        expect($variant->getIsDisabledByLinkedElement())->toBeTrue();
    }

    expect(Craft::$app->elements->restoreElement($entry))->toBeTrue();
    $primary = Node::find()->id($node->id)->siteId($entry->siteId)->status(null)->one();
    $secondary = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    expect($primary->getEnabledForSite())->toBeTrue();
    expect($secondary->getEnabledForSite())->toBe($secondaryEnabled);
    expect($primary->getIsDisabledByLinkedElement())->toBeFalse();
    expect($secondary->getIsDisabledByLinkedElement())->toBeFalse();
})->with([[false, false], [true, false], [true, true]]);

it('preserves globally disabled linked nodes through deletion and restoration on all sites', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $node->enabled = false;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    expect(Craft::$app->elements->deleteElement($entry))->toBeTrue();
    foreach (Node::find()->id($node->id)->site('*')->status(null)->all() as $variant) {
        expect($variant->enabled)->toBeFalse();
    }
    expect(Craft::$app->elements->restoreElement($entry))->toBeTrue();
    foreach (Node::find()->id($node->id)->site('*')->status(null)->all() as $variant) {
        expect($variant->enabled)->toBeFalse();
        expect($variant->getIsDisabledByLinkedElement())->toBeFalse();
    }
});
