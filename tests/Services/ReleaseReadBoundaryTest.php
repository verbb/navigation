<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\Navigation;
use verbb\navigation\controllers\NodesController;
use verbb\navigation\elements\Node;

it('does not attach descendants of a filtered parent to a different branch', function() {
    $menu = F::menu();
    $first = F::customNode($menu, 'Visible root', '/visible');
    $hidden = F::customNode($menu, 'Hidden root', '/hidden');
    $child = F::customNode($menu, 'Visible child', '/child', $hidden);
    $hidden->enabled = false;
    expect(Craft::$app->elements->saveElement($hidden))->toBeTrue();

    W::withAbsoluteUrl('https://read-boundary.test/child', function() use ($menu, $first, $child) {
        foreach ([1, 2] as $read) {
            $nodes = Node::find()->menuId($menu->id)->all();
            expect(array_column($nodes, 'id'))->toBe([$first->id, $child->id]);
            expect(array_column($nodes[0]->getChildren()->all(), 'id'))->toBe([]);
            expect($nodes[1]->getParent()?->id)->toBeNull();
            expect($nodes[0]->hasActiveChild())->toBeFalse();
            expect($nodes[1]->getCurrent())->toBeTrue();
        }
    });
});

it('keeps entry active matching on the linked site base URL', function(bool $home) {
    $site = F::secondarySite();
    $site->setBaseUrl('https://localized.test/fr/');
    expect(Craft::$app->sites->saveSite($site))->toBeTrue();
    $menu = F::menu();
    $section = F::entrySection();
    if ($home) {
        $settings = $section->getSiteSettings();
        $settings[$site->id]->uriFormat = '__home__';
        $section->setSiteSettings($settings);
        expect(Craft::$app->entries->saveSection($section))->toBeTrue();
    }
    $entry = F::entries(1, $section)[0];
    $node = F::entryNode($menu, $entry);
    $node->setElementSiteId($site->id);
    expect(Craft::$app->elements->saveElement($node, true, false))->toBeTrue();
    $linked = Craft::$app->elements->getElementById($entry->id, craft\elements\Entry::class, $site->id);
    expect($linked)->not->toBeNull();
    $url = 'https://localized.test/fr/' . ($home ? '' : $linked->uri);

    foreach ([$url => true, 'https://foreign.test/' . ($home ? '' : $linked->uri) => false] as $requestUrl => $current) {
        W::withAbsoluteUrl($requestUrl, function() use ($menu, $node, $url, $current) {
            $result = Node::find()->menuId($menu->id)->siteId($node->siteId)->one();
            expect(rtrim($result->getUrl(), '/'))->toBe(rtrim($url, '/'));
            expect($result->getCurrent())->toBe($current);
        });
    }
})->with([false, true]);

it('preserves stored structure bounds when adding dynamic projections', function() {
    $menu = F::menu();
    $section = F::entrySection();
    F::entries(1, $section);
    $parent = F::dynamicSectionNode($menu, $section);
    $first = F::customNode($menu, 'First stored', '/first', $parent);
    $second = F::customNode($menu, 'Second stored', '/second', $parent);
    $bounds = Node::find()->id($parent->id)->withNodeHierarchy(false)->one()->rgt;

    W::withAbsoluteUrl('https://read-boundary.test/', function() use ($menu, $parent, $first, $second, $bounds) {
        foreach ([1, 2] as $read) {
            $nodes = Node::find()->menuId($menu->id)->all();
            expect($nodes[0]->id)->toBe($parent->id);
            expect($nodes[0]->rgt)->toBe($bounds);
            expect(array_slice(array_column($nodes[0]->getChildren()->all(), 'id'), 0, 2))->toBe([$first->id, $second->id]);
            expect($nodes)->toHaveCount(4);
        }
    });
});

it('keeps interleaved site hierarchy and active ancestors separate', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Child', '/child', $parent);
    $localized = Node::find()->id($child->id)->siteId($site->id)->status(null)->one();
    $localized->setUrl('/child');
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    W::withAbsoluteUrl('https://read-boundary.test/child', function() use ($menu, $parent, $child, $site) {
        $nodes = Node::find()->menuId($menu->id)->site('*')->withNodeHierarchy()->all();
        expect($nodes)->toHaveCount(4);
        foreach ($nodes as $node) {
            if ($node->id === $parent->id) {
                expect(array_column($node->getChildren()->all(), 'id'))->toBe([$child->id]);
                expect($node->getChildren()->one()->siteId)->toBe($node->siteId);
                expect($node->hasActiveChild())->toBeTrue();
            } else {
                expect($node->getParent()?->id)->toBe($parent->id);
                expect($node->getParent()?->siteId)->toBe($node->siteId);
            }
        }
    });
});

it('preserves query rows when a filtered ancestor leaves a gap in levels', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'Root', '/root');
    $hidden = F::customNode($menu, 'Hidden child', '/hidden', $root);
    $grandchild = F::customNode($menu, 'Visible grandchild', '/grandchild', $hidden);
    $hidden->enabled = false;
    expect(Craft::$app->elements->saveElement($hidden))->toBeTrue();

    $query = Node::find()->menuId($menu->id)->limit(null)->withNodeHierarchy();
    expect($query->ids())->toBe([$root->id, $grandchild->id]);
    expect(array_column($query->all(), 'id'))->toBe([$root->id, $grandchild->id]);
});

it('wires hierarchy independently of an explicit query sort', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Z parent', '/parent');
    $child = F::customNode($menu, 'A child', '/child', $parent);
    $other = F::customNode($menu, 'M root', '/other');
    $nodes = Node::find()->menuId($menu->id)->limit(null)->orderBy('title asc')->withNodeHierarchy()->all();

    expect(array_column($nodes, 'id'))->toBe([$child->id, $other->id, $parent->id]);
    expect($nodes[0]->getParent()?->id)->toBe($parent->id);
    expect(array_column($nodes[2]->getChildren()->all(), 'id'))->toBe([$child->id]);
    expect($nodes[1]->getChildren()->all())->toBe([]);
});

it('filters localized URL suffixes by each result site', function(string $scope) {
    $site = F::secondarySite();
    $menu = F::menu();
    $node = F::customNode($menu, 'Localized', '/localized');
    $node->urlSuffix = '#primary';
    expect(Craft::$app->elements->saveElement($node, true, false))->toBeTrue();
    $other = Node::find()->id($node->id)->siteId($site->id)->status(null)->one();
    $other->urlSuffix = '#secondary';
    expect(Craft::$app->elements->saveElement($other, true, false))->toBeTrue();

    $query = Node::find()->menuId($menu->id)
        ->siteId($scope === '*' ? '*' : [$node->siteId, $site->id])
        ->withNodeHierarchy(false);
    $query->urlSuffix = '#secondary';
    expect(array_column($query->all(), 'siteId'))->toBe([$site->id]);
    expect((int)$query->count())->toBe(1);
})->with(['*', 'array']);

it('enables the URL filter when called without an argument', function() {
    $menu = F::menu();
    $link = F::customNode($menu, 'Link', '/link');
    F::passiveNode($menu, 'Heading');
    expect(Node::find()->menuId($menu->id)->hasUrl()->ids())->toBe([$link->id]);
    expect((int)Node::find()->menuId($menu->id)->hasUrl(false)->count())->toBe(2);
});

it('uses the current site when parent options omit a site ID', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Parent option', '/parent');
    boundaryRequest(function() use ($menu, $node) {
        Craft::$app->request->setBodyParams(['menuId' => $menu->id]);
        $response = (new NodesController('nodes', Navigation::$plugin))->actionGetParentOptions();
        expect($response->data['changeCount'])->toBe(0);
        expect(array_column($response->data['options'], 'value'))->toContain($node->id);
    });
});

it('keeps a removed linked site from breaking public menu reads', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $node = F::entryNode($menu, F::entries(1)[0]);
    $node->setElementSiteId($site->id);
    expect(Craft::$app->elements->saveElement($node, true, false))->toBeTrue();
    expect(Craft::$app->sites->deleteSiteById($site->id))->toBeTrue();

    W::withAbsoluteUrl('https://read-boundary.test/', function() use ($node, $menu) {
        foreach ([1, 2] as $read) {
            $result = Node::find()->menuId($menu->id)->one();
            expect($result->id)->toBe($node->id);
            expect($result->getUrl())->toBeNull();
            expect($result->getCurrent())->toBeFalse();
        }
    });
});

it('marks every stored ancestor active for a nested projected current page', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'Root', '/root');
    $middle = F::customNode($menu, 'Middle', '/middle', $root);
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $dynamic = F::dynamicSectionNode($menu, $section);
    $dynamic->setParentId($middle->id);
    expect(Craft::$app->elements->saveElement($dynamic))->toBeTrue();
    $url = rtrim(Craft::$app->sites->getPrimarySite()->getBaseUrl(), '/') . '/' . $entry->uri;

    W::withAbsoluteUrl($url, function() use ($menu) {
        foreach ([1, 2] as $read) {
            $nodes = Node::find()->menuId($menu->id)->all();
            expect($nodes)->toHaveCount(4);
            expect($nodes[3]->getCurrent())->toBeTrue();
            foreach (array_slice($nodes, 0, 3) as $ancestor) {
                expect($ancestor->hasActiveChild())->toBeTrue();
                expect($ancestor->getActive())->toBeTrue();
            }
        }
    });
});
