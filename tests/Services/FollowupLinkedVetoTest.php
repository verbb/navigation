<?php

use craft\elements\Entry;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation as N;

it('preserves linked node titles when an entry save is cancelled', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $title = $entry->title;
    $entry->title = 'Rejected title';
    $veto = static function($event) use ($entry) {
        if ($event->sender->id === $entry->id) {
            $event->isValid = false;
        }
    };
    yii\base\Event::on(Entry::class, Entry::EVENT_BEFORE_SAVE, $veto);
    try {
        expect(Craft::$app->elements->saveElement($entry))->toBeFalse();
    } finally {
        yii\base\Event::off(Entry::class, Entry::EVENT_BEFORE_SAVE, $veto);
    }
    expect(Entry::find()->id($entry->id)->one()->title)->toBe($title);
    expect(Node::find()->id($node->id)->one()?->title)->toBe($title);

    $entry->title = 'Accepted title';
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->one()?->title)->toBe('Accepted title');
});

it('preserves linked nodes when an entry hard delete is cancelled', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $veto = static function($event) use ($entry) {
        if ($event->sender->id === $entry->id) {
            $event->isValid = false;
        }
    };
    yii\base\Event::on(Entry::class, Entry::EVENT_BEFORE_DELETE, $veto);
    try {
        expect(Craft::$app->elements->deleteElement($entry, true))->toBeFalse();
    } finally {
        yii\base\Event::off(Entry::class, Entry::EVENT_BEFORE_DELETE, $veto);
    }
    expect(Entry::find()->id($entry->id)->one()?->id)->toBe($entry->id);
    expect(Node::find()->id($node->id)->one()?->id)->toBe($node->id);

    expect(Craft::$app->elements->deleteElement($entry, true))->toBeTrue();
    expect(Node::find()->id($node->id)->status(null)->one())->toBeNull();
});

it('undoes source changes when a linked node rejects the operation', function(string $operation) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $title = $entry->title;
    $eventName = $operation === 'hard-delete' ? Node::EVENT_BEFORE_DELETE : Node::EVENT_BEFORE_SAVE;
    $veto = static function($event) use ($node) {
        if ($event->sender->id === $node->id) {
            $event->isValid = false;
        }
    };
    yii\base\Event::on(Node::class, $eventName, $veto);
    try {
        $action = function() use ($entry, $operation) {
            if ($operation === 'save') {
                $entry->title = 'Uncommitted title';
                Craft::$app->elements->saveElement($entry);
            } else {
                Craft::$app->elements->deleteElement($entry, $operation === 'hard-delete');
            }
        };
        expect($action)->toThrow(yii\base\UserException::class);
    } finally {
        yii\base\Event::off(Node::class, $eventName, $veto);
    }
    expect(Entry::find()->id($entry->id)->one()?->title)->toBe($title);
    $reloaded = Node::find()->id($node->id)->one();
    expect($reloaded?->title)->toBe($title);
    expect($reloaded?->elementId)->toBe($entry->id);
    expect($reloaded?->getIsDisabledByLinkedElement())->toBeFalse();
})->with(['save', 'soft-delete', 'hard-delete']);

it('synchronizes linked content while a menu deletion is staged', function(string $operation) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $node->siteId);
    $sessions->stageDelete($session, $node);
    $staged = Node::find()->id($node->id)->status(null)->one();
    expect($staged->getIsPendingDelete())->toBeTrue();
    $readPublic = static fn() => Node::find()->menuId($menu->id)->all();
    Tests\Support\WebRequestSimulator::withAbsoluteUrl('https://staged-sync.test/', $readPublic);

    if ($operation === 'save') {
        $entry->title = 'Updated while deletion is staged';
        expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    } else {
        expect(Craft::$app->elements->deleteElement($entry))->toBeTrue();
        expect(Node::find()->id($node->id)->status(null)->one()->getIsDisabledByLinkedElement())->toBeTrue();
        expect(Craft::$app->elements->restoreElement($entry))->toBeTrue();
    }
    $reloaded = Node::find()->id($node->id)->status(null)->one();
    expect($reloaded->getIsPendingDelete())->toBeTrue();
    expect($reloaded->title)->toBe($entry->title);
    Tests\Support\WebRequestSimulator::withAbsoluteUrl('https://staged-sync.test/', function() use ($readPublic, $entry) {
        expect($readPublic()[0]->title)->toBe($entry->title);
    });
    // This permission is only for internal synchronization; native staged-node edits remain blocked.
    $reloaded->title = 'Native staged edit';
    expect(Craft::$app->elements->saveElement($reloaded))->toBeFalse();
    $session = $sessions->getSession($menu->id, $node->siteId);
    $sessions->discard($session);
    $reloaded = Node::find()->id($node->id)->one();
    expect($reloaded?->title)->toBe($entry->title);
    expect($reloaded?->getIsPendingDelete())->toBeFalse();
})->with(['save', 'delete-restore']);

it('preserves linked source disabling when a staged menu deletion is cancelled', function(bool $discard) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $node->siteId);
    $sessions->stageDelete($session, $node);
    $entry->enabled = false;
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    $session = $sessions->getSession($menu->id, $node->siteId);
    if ($discard) {
        $sessions->discard($session);
    } else {
        $sessions->unstageDelete($session, Node::find()->id($node->id)->status(null)->one());
    }
    expect(Node::find()->id($node->id)->one())->toBeNull();
    expect(Node::find()->id($node->id)->status(null)->one()->getIsPendingDelete())->toBeFalse();
})->with([false, true]);

it('keeps pending additions private when their linked entry is enabled', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $entry->enabled = false;
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    $node = F::entryNode($menu, $entry);
    $node->enabled = false;
    $node->setPendingPublish(true);
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $entry->enabled = true;
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->one())->toBeNull();
    expect(Node::find()->id($node->id)->status(null)->one()->getIsPendingPublish())->toBeTrue();
});

it('hard deletes nodes linked only to another source locale', function() {
    $site = F::secondarySite();
    $entry = F::entries(1)[0];
    $localized = Entry::find()->id($entry->id)->siteId($site->id)->one();
    $menu = F::menu();
    $node = F::entryNode($menu, $localized);
    expect(Craft::$app->elements->deleteElement($entry, true))->toBeTrue();
    expect(Node::find()->id($node->id)->site('*')->status(null)->all())->toBe([]);
});
