<?php

declare(strict_types=1);

use craft\base\Element;
use craft\elements\Entry;
use craft\elements\db\EntryQuery;
use craft\events\DefineUrlEvent;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use verbb\navigation\elements\Node;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

class IndexedLinkedEntry extends Entry
{
    public static int $indexCalls = 0;
    public static string $indexMode = 'callback';

    public static function find(): EntryQuery
    {
        return (new EntryQuery(static::class))->indexBy(static::$indexMode === 'property' ? 'url' : static function(Entry $element) {
            static::$indexCalls++;
            return $element->id;
        });
    }
}

class IndexedLinkedNodeType extends verbb\navigation\nodetypes\Entry
{
    public static function getElementType(): string
    {
        return IndexedLinkedEntry::class;
    }
}

it('honors selection changes and assignments during individual linked element hydration', function(string $mode, string $change) {
    $secondary = F::secondarySite();
    $menu = F::menu();
    [$first, $entry, $replacement] = F::entries(3);
    F::entryNode($menu, $first);
    F::entryNode($menu, $entry);
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    $node = $mode === 'standalone' ? clone $nodes[1] : $nodes[1];
    $assigned = clone $entry;
    $assigned->uri = 'assigned-in-individual-hydration';
    $expected = match ($change) {
        'site' => Entry::find()->id($entry->id)->siteId($secondary->id)->one(),
        'selection' => $replacement,
        'assignment' => $assigned,
        'null' => null,
    };
    $changed = false;
    $nested = null;
    $initialize = function(Event $event) use ($node, $nodes, $mode, $change, $first, $secondary, $replacement, $assigned, &$changed, &$nested) {
        if ($mode === 'nested' && $event->sender->id === $first->id) {
            $nested = $node->getElement();
        }
        if ($changed || $event->sender->id !== $node->elementId) {
            return;
        }
        $changed = true;
        match ($change) {
            'site' => $node->setElementSiteId($secondary->id),
            'selection' => $node->elementId = $replacement->id,
            'assignment' => $node->setElement($assigned),
            'null' => $node->setElement(null),
        };
    };
    Event::on(Entry::class, Entry::EVENT_INIT, $initialize);
    try {
        if ($mode === 'nested') {
            expect($nodes[0]->getUrl())->toBe($first->getUrl());
            $resolved = $nested;
        } else {
            $resolved = $node->getElement();
        }
        expect($changed)->toBeTrue();
        expect($resolved?->id)->toBe($expected?->id);
        expect($resolved?->siteId)->toBe($expected?->siteId);
        expect($node->getElement())->toBe($resolved);
        expect($node->getUrl())->toBe($expected?->getUrl());
        if ($change === 'assignment') {
            expect($resolved)->toBe($assigned);
        }
        expect(QueryProfiler::profile(fn() => $node->getUrl())['queries'])->toBe(0);
    } finally {
        Event::off(Entry::class, Entry::EVENT_INIT, $initialize);
    }
})->with(['standalone', 'nested'])->with(['site', 'selection', 'assignment', 'null']);

it('preserves custom element indexing while batching cached and uncached linked URLs', function(bool $cached, string $indexMode) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    foreach (range(1, 4) as $unused) {
        F::entryNode($menu, $entry);
    }
    IndexedLinkedEntry::$indexMode = $indexMode;
    IndexedLinkedEntry::$indexCalls = 0;
    $register = static function(RegisterNodeTypeEvent $event) { $event->types[] = IndexedLinkedNodeType::class; };
    $initialize = static function(Event $event) {
        $event->sender->on(Element::EVENT_DEFINE_URL, static function(DefineUrlEvent $event) {
            $event->url = 'https://indexed.example/' . $event->sender->id;
        });
    };
    Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    Event::on(IndexedLinkedEntry::class, Entry::EVENT_INIT, $initialize);
    try {
        $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
        foreach (array_slice($nodes, 1) as $node) {
            $node->type = IndexedLinkedNodeType::class;
        }
        if ($cached) {
            $cache = Navigation::$plugin->getNavigationCache();
            $nodes = $cache->hydrateNodes($cache->exportNodes($nodes, NavigationCache::PROFILE_STANDARD));
        }
        $profile = QueryProfiler::profile(function() use ($nodes, $entry) {
            expect($nodes[0]->getUrl())->toBe($entry->getUrl());
            foreach (array_slice($nodes, 1) as $node) {
                expect($node->getUrl())->toBe('https://indexed.example/' . $entry->id);
            }
            expect($nodes[1]->getElement())->not->toBe($nodes[2]->getElement());
        });
        expect($profile['queries'])->toBe(2);
        expect(IndexedLinkedEntry::$indexCalls)->toBe($indexMode === 'callback' ? 3 : 0);
        expect(QueryProfiler::profile(fn() => array_map(fn(Node $node) => $node->getUrl(), $nodes))['queries'])->toBe(0);
    } finally {
        Event::off(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
        Event::off(IndexedLinkedEntry::class, Entry::EVENT_INIT, $initialize);
        IndexedLinkedEntry::$indexMode = 'callback';
        IndexedLinkedEntry::$indexCalls = 0;
    }
})->with([false, true])->with(['callback', 'property']);
