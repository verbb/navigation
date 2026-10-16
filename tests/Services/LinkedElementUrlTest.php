<?php

declare(strict_types=1);

use craft\base\Element;
use craft\elements\Entry;
use craft\events\DefineUrlEvent;
use craft\events\PopulateElementEvent;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\elements\Node;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

class LinkedUrlOverrideNodeType extends verbb\navigation\nodetypes\Entry
{
    public function getUrl(): ?string
    {
        return 'https://custom.example/' . $this->node->elementId;
    }
}

it('batches linked URLs lazily for uncached and cached frontend hierarchies', function(string $mode) {
    $menu = F::menu();
    $entries = F::entries(12);
    $parent = F::entryNode($menu, $entries[0]);
    foreach (array_slice($entries, 1) as $entry) {
        F::entryNode($menu, $entry, $parent);
    }

    // Warm Craft metadata, not the node instances under test.
    Entry::find()->id(array_column($entries, 'id'))->all();
    $settings = Navigation::$plugin->getSettings();
    $previousMode = $settings->cacheMode;
    $previousProfile = $settings->cacheProfile;
    $settings->cacheMode = $mode === 'uncached' ? NavigationCache::MODE_OFF : NavigationCache::MODE_AUTO;
    $settings->cacheProfile = NavigationCache::PROFILE_STANDARD;
    $user = Craft::$app->getUser();
    $identity = $user->getIdentity();
    $user->setIdentity(null);
    $loaded = 0;
    $onInit = function() use (&$loaded) { $loaded++; };
    Event::on(Entry::class, Entry::EVENT_INIT, $onInit);

    try {
        WebRequestSimulator::withAbsoluteUrl(Craft::$app->sites->getPrimarySite()->getBaseUrl(), function() use ($menu, $mode, &$loaded) {
            $query = fn() => Node::find()->menuId($menu->id);
            if ($mode !== 'uncached') {
                $query()->all();
            }
            $nodes = $mode === 'cached roots' ? $query()->level(1)->all() : $query()->all();
            expect($loaded)->toBe(0, 'Fetching nodes must not hydrate linked entries just for their titles.');

            if ($mode === 'cached roots') {
                expect($nodes)->toHaveCount(1);
                $nodes = array_merge($nodes, $nodes[0]->getChildren()->all());
            }
            expect($nodes)->toHaveCount(12);
            $expected = array_map(fn(Node $node) => $node->getElementUrl(), $nodes);
            $urls = [];
            $profile = QueryProfiler::profile(function() use ($nodes, &$urls) {
                return $urls = array_map(fn(Node $node) => $node->getUrl(), $nodes);
            });
            expect($urls)->toBe($expected);
            expect($profile['queries'])->toBe(1);
            expect($loaded)->toBe(12);
            expect(QueryProfiler::profile(fn() => array_map(fn(Node $node) => $node->getUrl(), $nodes))['queries'])->toBe(0);
        });
    } finally {
        Event::off(Entry::class, Entry::EVENT_INIT, $onInit);
        $settings->cacheMode = $previousMode;
        $settings->cacheProfile = $previousProfile;
        $user->setIdentity($identity);
    }
})->with(['uncached', 'cached', 'cached roots']);

it('preserves linked URL events and reruns them on every URL read', function(string $eventName) {
    $menu = F::menu();
    $entries = F::entries(3);
    foreach ($entries as $entry) {
        F::entryNode($menu, $entry);
    }
    $nodes = Node::find()->menuId($menu->id)->all();
    $nodes[0]->urlSuffix = '#details';
    $calls = 0;
    $handler = function(DefineUrlEvent $event) use (&$calls) {
        $event->url = 'https://event.example/' . $event->sender->id . '/' . ++$calls;
    };
    Event::on(Entry::class, $eventName, $handler);

    try {
        expect($nodes[0]->getUrl())->toBe('https://event.example/' . $entries[0]->id . '/1#details');
        expect($nodes[0]->getUrl(false))->toBe('https://event.example/' . $entries[0]->id . '/2');
        expect($nodes[1]->getUrl())->toBe('https://event.example/' . $entries[1]->id . '/3');
    } finally {
        Event::off(Entry::class, $eventName, $handler);
    }
})->with([Element::EVENT_BEFORE_DEFINE_URL, Element::EVENT_DEFINE_URL]);

it('preserves instance URL handlers attached while linked elements initialize', function() {
    $menu = F::menu();
    foreach (F::entries(3) as $entry) {
        F::entryNode($menu, $entry);
    }
    $nodes = Node::find()->menuId($menu->id)->all();
    $onInit = function(Event $event) {
        $event->sender->on(Element::EVENT_DEFINE_URL, function(DefineUrlEvent $urlEvent) {
            $urlEvent->url = 'https://instance.example/' . $urlEvent->sender->id;
        });
    };
    Event::on(Entry::class, Entry::EVENT_INIT, $onInit);

    try {
        foreach ($nodes as $node) {
            expect($node->getUrl())->toBe('https://instance.example/' . $node->elementId);
        }
    } finally {
        Event::off(Entry::class, Entry::EVENT_INIT, $onInit);
    }
});

it('preserves null and sanitized linked URLs', function() {
    $menu = F::menu();
    F::entryNode($menu, F::entries(1)[0]);
    $node = Node::find()->menuId($menu->id)->all()[0];
    $handler = function(DefineUrlEvent $event) {
        $event->url = null;
        $event->handled = true;
    };
    Event::on(Entry::class, Element::EVENT_DEFINE_URL, $handler);
    try {
        expect($node->getUrl())->toBeNull();
    } finally {
        Event::off(Entry::class, Element::EVENT_DEFINE_URL, $handler);
    }
    $handler = function(DefineUrlEvent $event) { $event->url = 'javascript:alert(1)'; };
    Event::on(Entry::class, Element::EVENT_DEFINE_URL, $handler);
    try {
        expect($node->getUrl())->toBeNull();
    } finally {
        Event::off(Entry::class, Element::EVENT_DEFINE_URL, $handler);
    }
});

it('preserves custom linked node URL overrides without forcing an element lookup', function() {
    $register = function(RegisterNodeTypeEvent $event) { $event->types[] = LinkedUrlOverrideNodeType::class; };
    Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    try {
        $menu = F::menu();
        $node = F::entryNode($menu, F::entries(1)[0]);
        $node->type = LinkedUrlOverrideNodeType::class;
        expect(Craft::$app->elements->saveElement($node))->toBeTrue();
        $read = Node::find()->menuId($menu->id)->all()[0];
        $read->urlSuffix = '#custom';
        $profile = QueryProfiler::profile(function() use ($read) {
            expect($read->getUrl())->toBe('https://custom.example/' . $read->elementId . '#custom');
        });
        expect($profile['queries'])->toBe(0);
    } finally {
        Event::off(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    }
});

it('batches linked URLs by element type and linked site', function() {
    $secondary = F::secondarySite();
    $menu = F::menu();
    $entries = F::entries(4);
    foreach ($entries as $entry) {
        F::entryNode($menu, $entry);
    }
    foreach (F::categories(3) as $category) {
        F::categoryNode($menu, $category);
    }
    // Warm metadata for both element types and sites before counting data queries.
    Entry::find()->id(array_column($entries, 'id'))->siteId($secondary->id)->all();
    $nodes = Node::find()->menuId($menu->id)->siteId(Craft::$app->sites->getPrimarySite()->id)->all();
    $nodes[0]->setElementSiteId($secondary->id);
    $profile = QueryProfiler::profile(function() use ($nodes, $secondary) {
        foreach ($nodes as $node) {
            expect($node->getUrl())->toBe($node->getElement()->getUrl());
        }
        expect($nodes[0]->getElement()->siteId)->toBe($secondary->id);
    });
    expect($profile['queries'])->toBe(3);
});

it('preserves assigned elements and resolves changed or missing linked selections', function() {
    $menu = F::menu();
    $entries = F::entries(4);
    foreach ($entries as $entry) {
        F::entryNode($menu, $entry);
    }
    $nodes = Node::find()->menuId($menu->id)->all();
    $assigned = clone $entries[0];
    $assigned->uri = 'unsaved-assigned-url';
    $nodes[0]->setElement($assigned);
    $nodes[1]->elementId = $entries[3]->id;
    $nodes[2]->elementId = PHP_INT_MAX;

    expect($nodes[3]->getUrl())->toBe($entries[3]->getUrl());
    expect($nodes[0]->getElement())->toBe($assigned);
    expect($nodes[0]->getUrl())->toBe($assigned->getUrl());
    expect($nodes[1]->getElement()->id)->toBe($entries[3]->id);
    expect($nodes[2]->getUrl())->toBeNull();
    expect(QueryProfiler::profile(fn() => $nodes[2]->getUrl())['queries'])->toBe(0);

    $nodes[1]->elementId = $entries[1]->id;
    expect($nodes[1]->getUrl())->toBe($entries[1]->getUrl());
});

it('preserves preview placeholder URLs during linked URL batching', function() {
    $menu = F::menu();
    $entries = F::entries(2);
    foreach ($entries as $entry) {
        F::entryNode($menu, $entry);
    }
    $nodes = Node::find()->menuId($menu->id)->all();
    $placeholder = clone $entries[0];
    $placeholder->uri = 'preview-only-url';
    $elements = Craft::$app->elements;
    $property = new ReflectionProperty($elements, '_placeholderElements');
    $previous = $property->getValue($elements);
    $uris = new ReflectionProperty($elements, '_placeholderUris');
    $previousUris = $uris->isInitialized($elements) ? $uris->getValue($elements) : [];
    $elements->setPlaceholderElement($placeholder);
    try {
        expect($nodes[0]->getUrl())->toBe($placeholder->getUrl());
        expect($nodes[0]->getElement())->toBe($placeholder);
        expect($nodes[1]->getUrl())->toBe($entries[1]->getUrl());
    } finally {
        $property->setValue($elements, $previous);
        $uris->setValue($elements, $previousUris);
    }
});

it('preserves elements assigned by node population handlers before batching is registered', function() {
    $menu = F::menu();
    $entries = F::entries(2);
    foreach ($entries as $entry) {
        F::entryNode($menu, $entry);
    }
    $assigned = clone $entries[0];
    $assigned->uri = 'population-handler-url';
    $handler = function(PopulateElementEvent $event) use ($assigned) {
        if ($event->element->elementId === $assigned->id) {
            $event->element->setElementSiteId($assigned->siteId);
            $event->element->setElement($assigned);
        }
    };
    Event::on(NodeQuery::class, NodeQuery::EVENT_AFTER_POPULATE_ELEMENT, $handler);
    try {
        $nodes = Node::find()->menuId($menu->id)->all();
        expect($nodes[1]->getUrl())->toBe($entries[1]->getUrl());
        expect($nodes[0]->getElement())->toBe($assigned);
        expect($nodes[0]->getUrl())->toBe($assigned->getUrl());
    } finally {
        Event::off(NodeQuery::class, NodeQuery::EVENT_AFTER_POPULATE_ELEMENT, $handler);
    }
});

it('does not retain unused linked URL query results in the deferred batch', function() {
    // Craft retains initialized elements independently of batching. Skip initialization
    // here so this assertion only measures ownership by the new deferred batch.
    $node = (new ReflectionClass(Node::class))->newInstanceWithoutConstructor();
    $node->elementId = 1;
    $nodes = [$node];
    unset($node);
    Navigation::$plugin->getNodeRead()->deferLinkedElements($nodes);
    $reference = WeakReference::create($nodes[0]);
    unset($nodes);
    gc_collect_cycles();
    expect($reference->get())->toBeNull();
});

it('keeps cloned and serialized linked nodes independent of pending batches', function() {
    $menu = F::menu();
    $entries = F::entries(2);
    foreach ($entries as $entry) {
        F::entryNode($menu, $entry);
    }
    $nodes = Node::find()->menuId($menu->id)->all();
    $clone = clone $nodes[0];
    $clone->elementId = $entries[1]->id;
    $restored = unserialize(serialize($nodes[0]));
    expect($clone->getUrl())->toBe($entries[1]->getUrl());
    expect($restored->getUrl())->toBe($entries[0]->getUrl());
    expect($nodes[0]->getUrl())->toBe($entries[0]->getUrl());
});
