<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use yii\web\ConflictHttpException;

it('rejects stale session operations without losing a pending addition', function(string $operation) {
    $menu = F::menu();
    $node = F::customNode($menu, 'Pending', '/pending');
    $node->setPendingPublish(true);
    $node->enabled = false;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $sessions = N::$plugin->getBuildSessions();
    $old = $sessions->getOrCreate($menu->id, $node->siteId);
    $current = $sessions->getSession($menu->id, $node->siteId);
    $sessions->stageAddedNodes($current, [$node->id]);
    $revision = verbb\navigation\helpers\BuilderStructureRevision::get($menu);
    expect(fn() => match ($operation) {
        'draft' => $sessions->saveDraft($old, [['elementId' => $node->id]], null, $revision),
        'discard' => $sessions->discard($old),
        'publish' => $sessions->publish($old),
        'delete-session' => $sessions->deleteSession($old),
        'stage-delete' => $sessions->stageDelete($old, $node),
    })->toThrow(ConflictHttpException::class);
    expect($sessions->getSession($menu->id, $node->siteId)->addedNodeIds)->toBe([$node->id]);
    expect($sessions->canAuthorPendingNode($node, $current->userId + 100000))->toBeFalse();
    expect(Node::find()->id($node->id)->status(null)->one()->getIsPendingPublish())->toBeTrue();
    expect(verbb\navigation\helpers\BuilderStructureRevision::get($menu))->toBe($revision);
    $sessions->discard($sessions->getSession($menu->id, $node->siteId));
    expect(Node::find()->id($node->id)->status(null)->exists())->toBeFalse();
})->with(['draft', 'discard', 'publish', 'delete-session', 'stage-delete']);

it('keeps a staged deletion when a stale draft arrives', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Live', '/live');
    $sessions = N::$plugin->getBuildSessions();
    $old = $sessions->getOrCreate($menu->id, $node->siteId);
    $current = $sessions->getSession($menu->id, $node->siteId);
    $sessions->stageDelete($current, $node);
    expect(fn() => $sessions->saveDraft($old, [], ['title' => 'Older draft']))->toThrow(ConflictHttpException::class);
    expect($sessions->getSession($menu->id, $node->siteId)->stagedDeletes[0]['nodeId'])->toBe($node->id);
    expect(Node::find()->id($node->id)->one()->getIsPendingDelete())->toBeTrue();
    $sessions->unstageDelete($current, $node);
    expect(Node::find()->id($node->id)->one()->getIsPendingDelete())->toBeFalse();
});

it('rejects a stale addition after discard without resurrecting its session', function(bool $replace) {
    $menu = F::menu();
    $siteId = Craft::$app->sites->getPrimarySite()->id;
    $sessions = N::$plugin->getBuildSessions();
    $old = $sessions->getOrCreate($menu->id, $siteId);
    $sessions->discard($sessions->getSession($menu->id, $siteId));
    $replacement = $replace ? $sessions->getOrCreate($menu->id, $siteId) : null;
    expect(fn() => $sessions->stageAddedNodes($old, [12345]))->toThrow(ConflictHttpException::class);
    $actual = $sessions->getSession($menu->id, $siteId);
    expect($actual?->id)->toBe($replacement?->id);
    expect($actual?->addedNodeIds ?? [])->toBe([]);
})->with([false, true]);

it('allows sequential draft edits and publication on the same session model', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Pending', '/pending');
    $node->setPendingPublish(true);
    $node->enabled = false;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $sessions = N::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $node->siteId);
    $sessions->stageAddedNodes($session, [$node->id]);
    $sessions->saveDraft($session, [['elementId' => $node->id, 'parentId' => null, 'prevId' => null]]);
    $sessions->setMenuContentDraft($session, ['title' => 'Draft']);
    $sessions->clearMenuContentDraft($session);
    expect($sessions->publish($session, true)['publishedCount'])->toBe(1);
    expect(Node::find()->id($node->id)->one()->getIsPendingPublish())->toBeFalse();
});

it('keeps a current session baseline after a no-op save inside an older snapshot', function() {
    $menu = F::menu();
    $siteId = Craft::$app->sites->getPrimarySite()->id;
    $sessions = N::$plugin->getBuildSessions();
    $initial = $sessions->getOrCreate($menu->id, $siteId);
    $db = Craft::$app->db;
    $writer = new craft\db\Connection(['dsn' => $db->dsn, 'username' => $db->username,
        'password' => $db->password, 'tablePrefix' => $db->tablePrefix]);
    $tx = $db->beginTransaction(yii\db\Transaction::REPEATABLE_READ);
    try {
        $old = $sessions->getSession($menu->id, $siteId);
        // The second connection represents a committed request after the first read.
        $writer->createCommand()->update('{{%navigation_build_sessions}}',
            ['menuContentDraft' => '{"title":"Other request"}'], ['id' => $initial->id])->execute();
        $current = $sessions->getOrCreate($menu->id, $siteId);
        expect($old->menuContentDraft)->toBe([]);
        expect($current->menuContentDraft)->toBe(['title' => 'Other request']);
        $sessions->saveDraft($current, null, ['title' => 'Other request']);
        $sessions->setStructureMoves($current, [['elementId' => 123]]);
        expect($sessions->getSession($menu->id, $siteId)->menuContentDraft)->toBe(['title' => 'Other request']);
    } finally {
        $tx->rollBack();
        $writer->close();
    }
});
