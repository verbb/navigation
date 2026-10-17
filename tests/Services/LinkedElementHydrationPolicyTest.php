<?php

declare(strict_types=1);

use craft\elements\Entry;
use craft\elements\db\EntryQuery;
use craft\events\PopulateElementsEvent;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;
use yii\base\Event;

it('terminates hydration assignments and retains drafts of the selected entry', function(string $mode, bool $matchingDraft) {
    $menu = F::menu();
    [$first, $selected, $other] = F::entries(3);
    F::entryNode($menu, $first);
    F::entryNode($menu, $selected);
    $assigned = Craft::$app->getDrafts()->createDraft($matchingDraft ? $selected : $other);
    $assigned->uri = 'callback-draft';
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    $node = $mode === 'standalone' ? clone $nodes[1] : $nodes[1];
    $calls = 0;
    $nested = null;
    $initialize = function(Event $event) use ($mode, $node, $first, $selected, $assigned, &$calls, &$nested) {
        if ($mode === 'nested' && $event->sender->id === $first->id) {
            $nested = $node->getElement();
        }
        if ($event->sender->id === $selected->id) {
            // Fail safely against a recursive implementation without exhausting the test process.
            if (++$calls > 4) {
                throw new RuntimeException('Repeated the same rejected hydration assignment');
            }
            $node->setElement($assigned);
        }
    };
    Event::on(Entry::class, Entry::EVENT_INIT, $initialize);
    try {
        if ($mode === 'nested') {
            $nodes[0]->getElement();
            $resolved = $nested;
        } else {
            $resolved = $node->getElement();
        }
        expect($resolved?->id)->toBe($matchingDraft ? $assigned->id : $selected->id);
        if ($matchingDraft) {
            expect($resolved)->toBe($assigned);
        }
        expect($calls)->toBeLessThanOrEqual(2);
        expect($node->elementId)->toBe($selected->id);
        expect($node->getElement())->toBe($resolved);
        expect($node->getUrl())->toBe(($matchingDraft ? $assigned : $selected)->getUrl());
        expect(QueryProfiler::profile(fn() => $node->getUrl())['queries'])->toBe(0);
        // A later selection change must still invalidate the accepted draft.
        $node->elementId = $other->id;
        expect($node->getElement()?->id)->toBe($other->id);
    } finally {
        Event::off(Entry::class, Entry::EVENT_INIT, $initialize);
    }
})->with(['standalone', 'batch', 'nested'])->with([true, false]);

it('retains indexes established during query preparation for element population', function(string $prepareEvent, string $indexMode, bool $cached) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    F::entryNode($menu, $entry);
    F::entryNode($menu, $entry);
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    if ($cached) {
        $cache = Navigation::$plugin->getNavigationCache();
        $nodes = $cache->hydrateNodes($cache->exportNodes($nodes, NavigationCache::PROFILE_STANDARD));
    }
    $indexCalls = 0;
    $prepare = function(Event $event) use ($indexMode, &$indexCalls) {
        $event->sender->indexBy(match ($indexMode) {
            'id' => 'id',
            'url' => 'url',
            'callback' => static function(Entry $element) use (&$indexCalls) {
                $indexCalls++;
                return $element->id;
            },
        });
    };
    $keys = [];
    $populate = function(PopulateElementsEvent $event) use ($entry, $indexMode, &$keys) {
        $key = $indexMode === 'url' ? $entry->getUrl() : $entry->id;
        $keys[] = array_keys($event->elements);
        expect($event->elements)->toHaveKey($key);
        $event->elements[$key]->uri = 'prepared-index-url';
    };
    Event::on(EntryQuery::class, $prepareEvent, $prepare);
    Event::on(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENTS, $populate);
    try {
        $profile = QueryProfiler::profile(function() use ($nodes) {
            foreach ($nodes as $node) {
                expect($node->getUrl())->toContain('prepared-index-url');
            }
            expect($nodes[0]->getElement())->not->toBe($nodes[1]->getElement());
        });
        expect($keys)->toHaveCount(2);
        expect($indexCalls)->toBe($indexMode === 'callback' ? 2 : 0);
        expect($profile['queries'])->toBe(1);
        expect(QueryProfiler::profile(fn() => $nodes[0]->getUrl())['queries'])->toBe(0);
    } finally {
        Event::off(EntryQuery::class, $prepareEvent, $prepare);
        Event::off(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENTS, $populate);
    }
})->with([EntryQuery::EVENT_BEFORE_PREPARE, EntryQuery::EVENT_AFTER_PREPARE])->with(['id', 'callback', 'url'])->with([false, true]);

it('keeps cancellation and emulated execution as empty linked query results', function(string $mode) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    F::entryNode($menu, $entry);
    $node = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all()[0];
    $eventName = match ($mode) {
        'emulated' => EntryQuery::EVENT_INIT,
        'before' => EntryQuery::EVENT_BEFORE_PREPARE,
        'after' => EntryQuery::EVENT_AFTER_PREPARE,
    };
    $handler = static function(Event $event) use ($mode) {
        if ($mode === 'emulated') {
            $event->sender->emulateExecution(true);
        } else {
            $event->isValid = false;
        }
    };
    Event::on(EntryQuery::class, $eventName, $handler);
    try {
        $profile = QueryProfiler::profile(function() use ($node) {
            expect($node->getElement())->toBeNull();
            expect($node->getUrl())->toBeNull();
        });
        expect($profile['queries'])->toBe(0);
    } finally {
        Event::off(EntryQuery::class, $eventName, $handler);
    }
})->with(['emulated', 'before', 'after']);

it('honors preparation callbacks that clear an existing element index', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    F::entryNode($menu, $entry);
    $node = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all()[0];
    $initialize = static function(Event $event) { $event->sender->indexBy('id'); };
    $prepare = static function(Event $event) { $event->sender->indexBy(null); };
    $populate = static function(PopulateElementsEvent $event) {
        expect(array_keys($event->elements))->toBe([0]);
        $event->elements[0]->uri = 'cleared-index-url';
    };
    Event::on(EntryQuery::class, EntryQuery::EVENT_INIT, $initialize);
    Event::on(EntryQuery::class, EntryQuery::EVENT_AFTER_PREPARE, $prepare);
    Event::on(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENTS, $populate);
    try {
        expect($node->getUrl())->toContain('cleared-index-url');
    } finally {
        Event::off(EntryQuery::class, EntryQuery::EVENT_INIT, $initialize);
        Event::off(EntryQuery::class, EntryQuery::EVENT_AFTER_PREPARE, $prepare);
        Event::off(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENTS, $populate);
    }
});
