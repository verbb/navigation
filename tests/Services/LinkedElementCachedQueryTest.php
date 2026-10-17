<?php

declare(strict_types=1);

use craft\base\Element;
use craft\elements\Entry;
use craft\elements\db\EntryQuery;
use craft\events\DefineUrlEvent;
use craft\events\EagerLoadElementsEvent;
use craft\services\Elements;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use verbb\navigation\elements\Node;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

class CachedLinkedEntry extends Entry
{
    public static array $selectedIds = [];
    public static int $selectedSiteId;
    public static array $result = [];
    public static array $eagerLoads = [];
    public static ?EntryQuery $lastQuery = null;

    public static function find(): EntryQuery
    {
        $query = (new EntryQuery(static::class))
            ->id(static::$selectedIds)->siteId(static::$selectedSiteId)
            ->status(null)->drafts(null)->provisionalDrafts(null)->revisions(null)
            ->indexBy(static fn(Entry $element) => 'entry-' . $element->id)
            ->with(static::$eagerLoads);
        $query->setCachedResult(static::$result);

        return static::$lastQuery = $query;
    }
}

class CachedLinkedNodeType extends verbb\navigation\nodetypes\Entry
{
    public static function getElementType(): string
    {
        return CachedLinkedEntry::class;
    }
}

beforeEach(function() {
    $this->registerCachedLinkedType = static function(RegisterNodeTypeEvent $event) {
        $event->types[] = CachedLinkedNodeType::class;
    };
    Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $this->registerCachedLinkedType);
});

afterEach(function() {
    Event::off(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $this->registerCachedLinkedType);
    CachedLinkedEntry::$result = [];
    CachedLinkedEntry::$eagerLoads = [];
    CachedLinkedEntry::$lastQuery = null;
});

it('honors provider cached results without querying or rehydrating them', function(string $resultMode, bool $cachedMenu) {
    $menu = F::menu();
    [$first, $second, $missing] = F::entries(3);
    foreach ([$first, $second, $first, $missing] as $entry) {
        F::entryNode($menu, $entry);
    }
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    foreach ($nodes as $node) {
        $node->type = CachedLinkedNodeType::class;
    }
    if ($cachedMenu) {
        $cache = Navigation::$plugin->getNavigationCache();
        $nodes = $cache->hydrateNodes($cache->exportNodes($nodes, NavigationCache::PROFILE_STANDARD));
    }
    CachedLinkedEntry::$selectedIds = [$first->id, $second->id, $missing->id];
    CachedLinkedEntry::$selectedSiteId = $first->siteId;
    $expected = [];
    $urlCalls = 0;
    if ($resultMode !== 'empty') {
        $source = $resultMode === 'draft' ? Craft::$app->getDrafts()->createDraft($first) : $first;
        foreach ([$source, $second] as $entry) {
            $element = (new EntryQuery(CachedLinkedEntry::class))->id($entry->id)->siteId($entry->siteId)
                ->status(null)->drafts(null)->provisionalDrafts(null)->revisions(null)->one();
            $element->title = 'Unsaved provider title';
            $element->on(Element::EVENT_DEFINE_URL, static function(DefineUrlEvent $event) use (&$urlCalls) {
                $urlCalls++;
                $event->url = 'https://cached.example/' . $event->sender->id;
            });
            $expected[$element->getCanonicalId()] = $element;
        }
        // Providers can return sparse, custom indexes in any order.
        CachedLinkedEntry::$result = ['second' => $expected[$second->id], 'first' => $expected[$first->id]];
    }
    $prepareCalls = 0;
    $populateCalls = 0;
    $prepare = static function() use (&$prepareCalls) { $prepareCalls++; };
    $populate = static function() use (&$populateCalls) { $populateCalls++; };
    Event::on(EntryQuery::class, EntryQuery::EVENT_BEFORE_PREPARE, $prepare);
    Event::on(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENTS, $populate);
    try {
        $profile = QueryProfiler::profile(function() use ($nodes, $expected) {
            foreach ($nodes as $node) {
                $element = $expected[$node->elementId] ?? null;
                expect($node->getElement())->toBe($element);
                expect($node->getUrl())->toBe($element ? 'https://cached.example/' . $element->id : null);
                expect($node->getElement()?->title)->toBe($element ? 'Unsaved provider title' : null);
            }
        });
        expect($profile['queries'])->toBe(0);
        expect($prepareCalls)->toBe(0);
        expect($populateCalls)->toBe(0);
        expect($urlCalls)->toBe($resultMode === 'empty' ? 0 : 3);
        expect(CachedLinkedEntry::$lastQuery->getCachedResult())->toBe(CachedLinkedEntry::$result);
        expect(QueryProfiler::profile(fn() => array_map(fn(Node $node) => $node->getUrl(), $nodes))['queries'])->toBe(0);
    } finally {
        Event::off(EntryQuery::class, EntryQuery::EVENT_BEFORE_PREPARE, $prepare);
        Event::off(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENTS, $populate);
    }
})->with(['empty', 'populated', 'draft'])->with([false, true]);

it('discards provider cached results when the requested criteria change', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    F::entryNode($menu, $entry);
    F::entryNode($menu, $entry);
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    foreach ($nodes as $node) {
        $node->type = CachedLinkedNodeType::class;
    }
    CachedLinkedEntry::$selectedIds = [-1];
    CachedLinkedEntry::$selectedSiteId = $entry->siteId;
    $profile = QueryProfiler::profile(function() use ($nodes, $entry) {
        foreach ($nodes as $node) {
            expect($node->getUrl())->toBe($entry->getUrl());
        }
        expect($nodes[0]->getElement())->not->toBe($nodes[1]->getElement());
    });
    expect($profile['queries'])->toBe(1);
    expect(CachedLinkedEntry::$lastQuery->getCachedResult())->toBeNull();
});


it('preserves callback changes while eager loading cached provider elements', function(string $change) {
    $menu = F::menu();
    [$first, $second, $replacement] = F::entries(3);
    F::entryNode($menu, $first);
    F::entryNode($menu, $second);
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    foreach ($nodes as $node) {
        $node->type = CachedLinkedNodeType::class;
    }
    CachedLinkedEntry::$selectedIds = [$first->id, $second->id];
    CachedLinkedEntry::$selectedSiteId = $first->siteId;
    CachedLinkedEntry::$result = (new EntryQuery(CachedLinkedEntry::class))
        ->id(CachedLinkedEntry::$selectedIds)->siteId($first->siteId)->indexBy('id')->all();
    CachedLinkedEntry::$eagerLoads = ['authors'];
    $assigned = clone CachedLinkedEntry::$result[$second->id];
    $assigned->uri = 'assigned-during-cached-eager-load';
    $calls = 0;
    $eagerLoad = static function(EagerLoadElementsEvent $event) use ($nodes, $change, $assigned, $replacement, &$calls) {
        if ($event->elementType !== CachedLinkedEntry::class) {
            return;
        }
        $event->with = [];
        if (++$calls > 1) {
            expect($event->elements[0]->id)->toBe($replacement->id);
            return;
        }
        expect($event->elements)->toBe(array_values(CachedLinkedEntry::$result));
        match ($change) {
            'assignment' => $nodes[1]->setElement($assigned),
            'null' => $nodes[1]->setElement(null),
            'selection' => $nodes[1]->elementId = $replacement->id,
        };
    };
    Event::on(Elements::class, Elements::EVENT_BEFORE_EAGER_LOAD_ELEMENTS, $eagerLoad);
    try {
        $profile = QueryProfiler::profile(function() use ($nodes, $first, $assigned, $replacement, $change) {
            expect($nodes[0]->getElement())->toBe(CachedLinkedEntry::$result[$first->id]);
            if ($change === 'selection') {
                expect($nodes[1]->getElement()?->id)->toBe($replacement->id);
            } else {
                expect($nodes[1]->getElement())->toBe($change === 'assignment' ? $assigned : null);
            }
        });
        expect($calls)->toBe($change === 'selection' ? 2 : 1);
        expect($profile['queries'])->toBe($change === 'selection' ? 1 : 0);
    } finally {
        Event::off(Elements::class, Elements::EVENT_BEFORE_EAGER_LOAD_ELEMENTS, $eagerLoad);
    }
})->with(['assignment', 'null', 'selection']);
