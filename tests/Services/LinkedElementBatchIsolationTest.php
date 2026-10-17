<?php

declare(strict_types=1);

use craft\base\Element;
use craft\elements\Entry;
use craft\elements\db\EntryQuery;
use craft\events\DefineUrlEvent;
use craft\events\PopulateElementEvent;
use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Performance\QueryProfiler;
use verbb\navigation\elements\Node;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

class BatchMissingElementNodeType extends verbb\navigation\nodetypes\Entry
{
    public static function getElementType(): string
    {
        // Models a registered node type whose optional element provider was removed.
        return 'tests\\missing\\ElementProvider';
    }
}

class BatchMissingElementUrlOverride extends BatchMissingElementNodeType
{
    public function getUrl(): ?string
    {
        return 'https://custom.example/independent';
    }
}

it('keeps duplicate links and initialization handlers independent with one data query', function(bool $cached) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    F::entryNode($menu, $entry);
    F::entryNode($menu, $entry);
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    if ($cached) {
        $cache = Navigation::$plugin->getNavigationCache();
        $nodes = $cache->hydrateNodes($cache->exportNodes($nodes, NavigationCache::PROFILE_STANDARD));
    }

    $initialized = 0;
    $initialize = function(Event $event) use (&$initialized) {
        $instance = ++$initialized;
        $event->sender->on(Element::EVENT_DEFINE_URL, static function(DefineUrlEvent $event) use ($instance) {
            $event->url = 'https://initialized.example/' . $instance;
        });
    };
    Event::on(Entry::class, Entry::EVENT_INIT, $initialize);
    try {
        $profile = QueryProfiler::profile(function() use ($nodes) {
            expect($nodes[0]->getUrl())->toBe('https://initialized.example/1');
            expect($nodes[1]->getUrl())->toBe('https://initialized.example/2');
            expect($nodes[0]->getElement())->not->toBe($nodes[1]->getElement());
            $nodes[0]->getElement()->on(Element::EVENT_DEFINE_URL, static function(DefineUrlEvent $event) {
                $event->url = 'https://first-node.example/';
            });
            expect($nodes[0]->getUrl())->toBe('https://first-node.example/');
            expect($nodes[1]->getUrl())->toBe('https://initialized.example/2');
        });
        expect($profile['queries'])->toBe(1);
        expect($initialized)->toBe(2);
    } finally {
        Event::off(Entry::class, Entry::EVENT_INIT, $initialize);
    }
})->with([false, true]);

it('preserves linked custom fields and population callbacks for each duplicate link', function() {
    $menu = F::menu();
    $section = F::entrySection();
    $field = new PlainText(['name' => 'Batch field', 'handle' => 'batchField' . uniqid()]);
    expect(Craft::$app->getFields()->saveField($field))->toBeTrue();
    $type = $section->getEntryTypes()[0];
    $tab = $type->getFieldLayout()->getTabs()[0];
    $tab->setElements(array_merge($tab->getElements(), [['type' => CustomField::class, 'fieldUid' => $field->uid]]));
    expect(Craft::$app->getEntries()->saveEntryType($type))->toBeTrue();
    $entry = F::entries(1, $section)[0];
    $entry->setFieldValue($field->handle, 'Stored value');
    expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue();
    F::entryNode($menu, $entry);
    F::entryNode($menu, $entry);
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    $populated = 0;
    $populate = function(PopulateElementEvent $event) use ($field, &$populated) {
        $value = $event->element->getFieldValue($field->handle);
        $event->element->setFieldValue($field->handle, $value . ' populated ' . ++$populated);
    };
    Event::on(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENT, $populate);
    try {
        $profile = QueryProfiler::profile(function() use ($nodes, $field) {
            expect($nodes[0]->getElement()->getFieldValue($field->handle))->toBe('Stored value populated 1');
            expect($nodes[1]->getElement()->getFieldValue($field->handle))->toBe('Stored value populated 2');
        });
        expect($profile['queries'])->toBe(1);
        expect($populated)->toBe(2);
    } finally {
        Event::off(EntryQuery::class, EntryQuery::EVENT_AFTER_POPULATE_ELEMENT, $populate);
    }
});

it('honors node changes made during linked element hydration', function(string $change) {
    $menu = F::menu();
    [$first, $second, $replacement] = F::entries(3);
    F::entryNode($menu, $first);
    F::entryNode($menu, $second);
    $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
    $assigned = clone $second;
    $assigned->uri = 'assigned-during-hydration';
    $handled = false;
    $nestedElement = null;
    $initialize = function(Event $event) use ($nodes, $first, $replacement, $assigned, $change, &$handled, &$nestedElement) {
        if ($handled || $event->sender->id !== $first->id) {
            return;
        }
        $handled = true;
        match ($change) {
            'assignment' => $nodes[1]->setElement($assigned),
            'null' => $nodes[1]->setElement(null),
            'selection' => $nodes[1]->elementId = $replacement->id,
            'initiator selection' => $nodes[0]->elementId = $replacement->id,
            'nested read' => $nestedElement = $nodes[1]->getElement(),
        };
    };
    Event::on(Entry::class, Entry::EVENT_INIT, $initialize);
    try {
        expect($nodes[0]->getUrl())->toBe(($change === 'initiator selection' ? $replacement : $first)->getUrl());
        expect($handled)->toBeTrue();
        match ($change) {
            'assignment' => expect($nodes[1]->getElement())->toBe($assigned)
                ->and($nodes[1]->getUrl())->toBe($assigned->getUrl()),
            'null' => expect($nodes[1]->getElement())->toBeNull(),
            'selection' => expect($nodes[1]->getUrl())->toBe($replacement->getUrl()),
            'initiator selection' => expect($nodes[1]->getUrl())->toBe($second->getUrl()),
            'nested read' => expect($nodes[1]->getElement())->toBe($nestedElement),
        };
    } finally {
        Event::off(Entry::class, Entry::EVENT_INIT, $initialize);
    }
})->with(['assignment', 'null', 'selection', 'initiator selection', 'nested read']);

it('isolates unavailable custom element providers from valid sibling URLs', function(bool $missingFirst) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    foreach (range(1, 3) as $unused) {
        F::entryNode($menu, $entry);
    }
    $register = static function(RegisterNodeTypeEvent $event) {
        $event->types[] = BatchMissingElementNodeType::class;
        $event->types[] = BatchMissingElementUrlOverride::class;
    };
    Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    try {
        $nodes = Node::find()->menuId($menu->id)->withNodeHierarchy(false)->all();
        $nodes[$missingFirst ? 0 : 1]->type = BatchMissingElementNodeType::class;
        $nodes[2]->type = BatchMissingElementUrlOverride::class;
        $profile = QueryProfiler::profile(function() use ($nodes, $entry, $missingFirst) {
            expect($nodes[0]->getUrl())->toBe($missingFirst ? null : $entry->getUrl());
            expect($nodes[1]->getUrl())->toBe($missingFirst ? $entry->getUrl() : null);
            expect($nodes[2]->getUrl())->toBe('https://custom.example/independent');
            expect($nodes[$missingFirst ? 0 : 1]->getElement())->toBeNull();
        });
        expect($profile['queries'])->toBe(1);
    } finally {
        Event::off(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    }
})->with([false, true]);
