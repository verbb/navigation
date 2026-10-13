<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;

it('does not lock a published node referenced by a stale pending-add session', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Published node', '/published');
    $sessions = N::$plugin->getBuildSessions();
    $ownerId = (int)craft\elements\User::find()->admin()->status(null)->one()->id;
    $session = $sessions->getOrCreate($menu->id, $node->siteId, $ownerId);
    $session->addedNodeIds = [(int)$node->id];
    $sessions->saveSession($session);

    expect($node->getIsPendingPublish())->toBeFalse()
        ->and($sessions->canAuthorPendingNode($node, $ownerId + 1))->toBeTrue();

    $reconciled = $sessions->getOrCreate($menu->id, $node->siteId, $ownerId);

    expect($reconciled->addedNodeIds)->toBe([])
        ->and($sessions->getSession($menu->id, $node->siteId, $ownerId)?->addedNodeIds)->toBe([]);
});

it('does not lock a restored node referenced by a stale staged delete', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Restored node', '/restored');
    $sessions = N::$plugin->getBuildSessions();
    $ownerId = (int)craft\elements\User::find()->admin()->status(null)->one()->id;
    $session = $sessions->getOrCreate($menu->id, $node->siteId, $ownerId);
    $session->stagedDeletes = [[
        'nodeId' => (int)$node->id,
        'enabled' => true,
        'enabledForSite' => true,
        'restoreEnabledState' => false,
    ]];
    $sessions->saveSession($session);

    expect($node->getIsPendingDelete())->toBeFalse()
        ->and($sessions->canAuthorPendingNode($node, $ownerId + 1))->toBeTrue();

    $reconciled = $sessions->getOrCreate($menu->id, $node->siteId, $ownerId);

    expect($reconciled->stagedDeletes)->toBe([])
        ->and($sessions->getSession($menu->id, $node->siteId, $ownerId)?->stagedDeletes)->toBe([]);
});

it('keeps genuine pending additions private to their owning session', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Pending node', '/pending');
    $node->setPendingPublish(true);
    $node->enabled = false;
    $node->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($node))->toBeTrue();

    $sessions = N::$plugin->getBuildSessions();
    $ownerId = (int)craft\elements\User::find()->admin()->status(null)->one()->id;
    $session = $sessions->getOrCreate($menu->id, $node->siteId, $ownerId);
    $sessions->stageAddedNodes($session, [(int)$node->id]);

    expect($sessions->canAuthorPendingNode($node, $ownerId + 1))->toBeFalse()
        ->and($sessions->getOrCreate($menu->id, $node->siteId, $ownerId)->addedNodeIds)->toBe([(int)$node->id]);

    $sessions->discard($sessions->getSession($menu->id, $node->siteId, $ownerId));
});
