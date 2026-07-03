<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\variables\NavigationVariable;

/**
 * Active state matrix:
 *
 * | Flag            | Meaning                                      | Typical template use        |
 * |-----------------|----------------------------------------------|-----------------------------|
 * | current         | Exact URL match                              | aria-current, primary style |
 * | active          | current OR path section OR active descendant | .active on branch           |
 * | hasActiveChild  | Descendant nav node is current               | open dropdown submenu       |
 */

function withPageTrigger(string $trigger, callable $callback): mixed
{
    $general = Craft::$app->getConfig()->getGeneral();
    $original = $general->pageTrigger;
    $general->pageTrigger = $trigger;

    try {
        return $callback();
    } finally {
        $general->pageTrigger = $original;
    }
}

function withUrl(string $path, callable $callback): mixed
{
    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');
    $absoluteUrl = str_starts_with($path, 'http') ? $path : $siteUrl . $path;

    return WebRequestSimulator::withAbsoluteUrl($absoluteUrl, $callback);
}

function nodesFor(string $handle): array
{
    return (new NavigationVariable())
        ->nodes(['handle' => $handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id])
        ->withNodeHierarchy(true)
        ->all();
}

function nodeByTitle(array $nodes, string $title): ?verbb\navigation\elements\Node
{
    foreach ($nodes as $node) {
        if ($node->title === $title) {
            return $node;
        }
    }

    return null;
}

it('marks only the exact node as current', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'About', '/about');

    withUrl('/about', function() use ($nav) {
        $about = nodeByTitle(nodesFor($nav->handle), 'About');

        expect($about?->getCurrent())->toBeTrue();
        expect($about?->getActive())->toBeTrue();
        expect($about?->hasActiveChild())->toBeFalse();
    });
});

it('marks ancestors active and hasActiveChild when a child is current', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    withUrl('/parent/child', function() use ($nav) {
        $nodes = nodesFor($nav->handle);
        $parent = nodeByTitle($nodes, 'Parent');
        $child = nodeByTitle($nodes, 'Child');

        expect($child?->getCurrent())->toBeTrue();
        expect($child?->getActive())->toBeTrue();
        expect($parent?->getCurrent())->toBeFalse();
        expect($parent?->getActive())->toBeTrue();
        expect($parent?->hasActiveChild())->toBeTrue();
    });
});

it('marks path-section nodes active without hasActiveChild for off-nav pages', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Section', '/section');

    withUrl('/section/deep-page', function() use ($nav) {
        $section = nodeByTitle(nodesFor($nav->handle), 'Section');

        expect($section?->getCurrent())->toBeFalse();
        expect($section?->getActive())->toBeTrue();
        expect($section?->hasActiveChild())->toBeFalse();
    });
});

it('returns the deepest current node from getActiveNode', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Products', '/products');
    NavigationFixtureFactory::customNode($nav, 'Widget', '/products/widget', $parent);

    withUrl('/products/widget', function() use ($nav) {
        $activeNode = (new NavigationVariable())->getActiveNode(['handle' => $nav->handle]);

        expect($activeNode?->title)->toBe('Widget');
    });
});

it('returns projected dynamic section children from getActiveNode', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entries = NavigationFixtureFactory::entries(2, $section);
    NavigationFixtureFactory::dynamicSectionNode($nav, $section);

    $entryPath = '/' . ltrim($entries[1]->uri, '/');

    withUrl($entryPath, function() use ($nav, $entries) {
        $activeNode = (new NavigationVariable())->getActiveNode(['handle' => $nav->handle]);

        expect($activeNode)->toBeInstanceOf(\verbb\navigation\models\ProjectedNode::class);
        expect($activeNode?->elementId)->toBe($entries[1]->id);
    });
});

it('returns projected nodes from getCurrentNodes and navigation context', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entries = NavigationFixtureFactory::entries(2, $section);
    NavigationFixtureFactory::dynamicSectionNode($nav, $section);

    $entryPath = '/' . ltrim($entries[0]->uri, '/');

    withUrl($entryPath, function() use ($nav, $entries, $section) {
        $variable = new NavigationVariable();
        $currentNodes = $variable->getCurrentNodes(['handle' => $nav->handle]);
        $context = $variable->context($nav->handle);

        expect($currentNodes)->toHaveCount(1);
        expect($currentNodes[0])->toBeInstanceOf(\verbb\navigation\models\ProjectedNode::class);
        expect($currentNodes[0]->elementId)->toBe($entries[0]->id);
        expect($context->current())->toBeInstanceOf(\verbb\navigation\models\ProjectedNode::class);
        expect($context->current()?->elementId)->toBe($entries[0]->id);
        expect($context->parent()?->title)->toBe($section->name);
    });
});

it('returns branch-active nodes from getActiveNodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    withUrl('/parent/child', function() use ($nav) {
        $titles = array_map(
            static fn($node) => $node->title,
            (new NavigationVariable())->getActiveNodes(['handle' => $nav->handle]),
        );

        expect($titles)->toBe(['Parent', 'Child']);
    });
});

it('returns deepest current nodes from getCurrentNodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    withUrl('/parent/child', function() use ($nav) {
        $titles = array_map(
            static fn($node) => $node->title,
            (new NavigationVariable())->getCurrentNodes(['handle' => $nav->handle]),
        );

        expect($titles)->toBe(['Child']);
    });
});

it('activates site nodes for sub-pages', function() {
    $nav = NavigationFixtureFactory::menu();
    $site = Craft::$app->getSites()->getPrimarySite();
    NavigationFixtureFactory::siteNode($nav, $site);

    withUrl('/any-sub-page', function() use ($nav) {
        $siteNode = nodesFor($nav->handle)[0];

        expect($siteNode->getCurrent())->toBeFalse();
        expect($siteNode->getActive())->toBeTrue();
    });
});

it('does not partially match similar url prefixes', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Blog', '/blog');
    NavigationFixtureFactory::customNode($nav, 'Blog Post', '/blog-post');

    withUrl('/blog-post', function() use ($nav) {
        $nodes = nodesFor($nav->handle);

        expect(nodeByTitle($nodes, 'Blog')?->getActive())->toBeFalse();
        expect(nodeByTitle($nodes, 'Blog Post')?->getCurrent())->toBeTrue();
    });
});

it('leaves passive nodes inactive', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::passiveNode($nav, 'Passive Group');

    withUrl('/anything', function() use ($nav) {
        $passive = nodesFor($nav->handle)[0];

        expect($passive->getCurrent())->toBeFalse();
        expect($passive->getActive())->toBeFalse();
        expect($passive->hasActiveChild())->toBeFalse();
    });
});

it('handles wide deep trees without descendant queries for hasActiveChild', function() {
    $nav = NavigationFixtureFactory::menu();
    $root = NavigationFixtureFactory::customNode($nav, 'Root', '/root');
    $level2 = NavigationFixtureFactory::customNode($nav, 'Level 2', '/root/level-2', $root);
    $level3 = NavigationFixtureFactory::customNode($nav, 'Level 3', '/root/level-2/level-3', $level2);
    NavigationFixtureFactory::customNode($nav, 'Level 4', '/root/level-2/level-3/level-4', $level3);

    withUrl('/root/level-2/level-3/level-4', function() use ($nav) {
        $nodes = nodesFor($nav->handle);

        expect(nodeByTitle($nodes, 'Root')?->hasActiveChild())->toBeTrue();
        expect(nodeByTitle($nodes, 'Level 4')?->getCurrent())->toBeTrue();
        expect(nodeByTitle($nodes, 'Level 4')?->hasActiveChild())->toBeFalse();
    });
});

it('applies resolved active state after a cache hit', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    withUrl('/parent/child', function() use ($criteria) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->all();
        $nodes = $variable->nodes($criteria)->withNodeHierarchy(true)->all();

        expect(nodeByTitle($nodes, 'Parent')?->hasActiveChild())->toBeTrue();
        expect(nodeByTitle($nodes, 'Child')?->getCurrent())->toBeTrue();
    });
});

it('resolves sibling branches independently', function() {
    $nav = NavigationFixtureFactory::menu();
    $root1 = NavigationFixtureFactory::customNode($nav, 'One', '/one');
    NavigationFixtureFactory::customNode($nav, 'One Child', '/one/child', $root1);
    NavigationFixtureFactory::customNode($nav, 'Two', '/two');

    withUrl('/two', function() use ($nav) {
        $nodes = nodesFor($nav->handle);

        expect(nodeByTitle($nodes, 'Two')?->getCurrent())->toBeTrue();
        expect(nodeByTitle($nodes, 'One')?->getActive())->toBeFalse();
        expect(nodeByTitle($nodes, 'One Child')?->getActive())->toBeFalse();
    });
});

it('marks entry-backed nodes current using linked element uris', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    NavigationFixtureFactory::entryNode($nav, $entry);

    $entryPath = '/' . ltrim($entry->uri, '/');

    withUrl($entryPath, function() use ($nav, $entry) {
        $node = nodesFor($nav->handle)[0];

        expect($node->getCurrent())->toBeTrue();
        expect($node->getActive())->toBeTrue();
        expect($node->elementId)->toBe($entry->id);
    });
});

it('marks homepage entry nodes current on the site root url', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::homepageEntry();
    NavigationFixtureFactory::entryNode($nav, $entry);

    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    withUrl($siteUrl, function() use ($nav) {
        $node = nodesFor($nav->handle)[0];

        expect($node->getCurrent())->toBeTrue();
        expect($node->getActive())->toBeTrue();
    });
});

it('strips path pagination segments before matching active nodes', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Section', '/section');

    withPageTrigger('page', function() use ($nav) {
        withUrl('/section/page/2', function() use ($nav) {
            $section = nodeByTitle(nodesFor($nav->handle), 'Section');

            expect($section?->getCurrent())->toBeFalse();
            expect($section?->getActive())->toBeTrue();
        });
    });
});

it('marks nodes current when the request url includes a url suffix', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Section', '/section');
    $node->urlSuffix = '/details';
    Craft::$app->getElements()->saveElement($node);

    withUrl('/section/details', function() use ($nav) {
        $section = nodeByTitle(nodesFor($nav->handle), 'Section');

        expect($section?->getCurrent())->toBeTrue();
        expect($section?->getActive())->toBeTrue();
    });
});

it('does not mark nodes current when only the base url matches but the suffix differs', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Section', '/section');
    $node->urlSuffix = '/details';
    Craft::$app->getElements()->saveElement($node);

    withUrl('/section', function() use ($nav) {
        $section = nodeByTitle(nodesFor($nav->handle), 'Section');

        expect($section?->getCurrent())->toBeFalse();
    });
});
