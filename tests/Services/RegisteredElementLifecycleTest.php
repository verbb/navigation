<?php

use craft\elements\Entry;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\Navigation;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

class RegisteredLifecycleEntryType extends verbb\navigation\nodetypes\Entry
{
    public static function displayName(): string
    {
        return 'Registered lifecycle entry';
    }
}

it('applies source lifecycle changes to registered element node types', function(string $operation) {
    $register = static function(RegisterNodeTypeEvent $event) {
        $event->types[] = RegisteredLifecycleEntryType::class;
    };
    Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);

    try {
        $menu = F::menu();
        $entry = F::entries(1)[0];
        $node = F::entryNode($menu, $entry);
        $node->type = RegisteredLifecycleEntryType::class;
        expect(Craft::$app->elements->saveElement($node))->toBeTrue();
        $read = static fn() => Node::find()->id($node->id)->status(null)->one();
        $visible = static fn(Node $n) => $n->enabled && $n->getEnabledForSite();
        expect($read()->type)->toBe(RegisteredLifecycleEntryType::class);

        if ($operation === 'rename') {
            $overridden = F::entryNode($menu, $entry);
            $overridden->type = RegisteredLifecycleEntryType::class;
            $overridden->title = 'Authored label';
            expect(Craft::$app->elements->saveElement($overridden))->toBeTrue();
            $entry->title = 'Renamed source';
            expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
            expect($read()->title)->toBe('Renamed source');
            expect(Node::find()->id($overridden->id)->one()->title)->toBe('Authored label');
        } elseif ($operation === 'status') {
            $entry->enabled = false;
            expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
            expect($visible($read()))->toBeFalse();
            $entry->enabled = true;
            expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
            expect($visible($read()))->toBeTrue();
        } elseif ($operation === 'soft-delete') {
            expect(Craft::$app->elements->deleteElement($entry))->toBeTrue();
            expect($visible($read()))->toBeFalse();
            expect($read()->getIsDisabledByLinkedElement())->toBeTrue();
        } elseif ($operation === 'restore') {
            expect(Craft::$app->elements->deleteElement($entry))->toBeTrue();
            // Establish the stored restore state independently of delete-event dispatch.
            Navigation::$plugin->getNodes()->disableNodeForLinkedElement($read());
            expect($visible($read()))->toBeFalse();
            expect(Craft::$app->elements->restoreElement($entry))->toBeTrue();
            expect($visible($read()))->toBeTrue();
            expect($read()->getIsDisabledByLinkedElement())->toBeFalse();
        } else {
            $veto = static function($event) use ($entry) {
                if ($event->sender->id === $entry->id) {
                    $event->isValid = false;
                }
            };
            Event::on(Entry::class, Entry::EVENT_BEFORE_DELETE, $veto);
            try {
                expect(Craft::$app->elements->deleteElement($entry, true))->toBeFalse();
            } finally {
                Event::off(Entry::class, Entry::EVENT_BEFORE_DELETE, $veto);
            }
            expect($read()->elementId)->toBe($entry->id);
            expect(Craft::$app->elements->deleteElement($entry, true))->toBeTrue();
            expect($read())->toBeNull();
        }
    } finally {
        Event::off(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, $register);
    }
})->with(['rename', 'status', 'soft-delete', 'restore', 'hard-delete']);
