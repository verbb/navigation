<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation;

it('creates and tracks a build session for staged node adds', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'Pending', '/pending');
    $node->enabled = false;
    $node->setEnabledForSite(false);
    $node->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException('Failed saving pending node fixture.');
    }

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->addAddedNode($session, (int)$node->id);

    expect($session->addedNodeIds)->toContain($node->id);
    expect($buildSessions->getChangeCount($nav->id, $siteId))->toBeGreaterThan(0);
});

it('publishes staged nodes through the build session', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'Pending', '/pending');
    $node->enabled = false;
    $node->setEnabledForSite(false);
    $node->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException('Failed saving pending node fixture.');
    }

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->addAddedNode($session, (int)$node->id);

    $result = $buildSessions->publish($session);

    expect($result['publishedCount'])->toBe(1);
    expect($buildSessions->getSession($nav->id, $siteId))->toBeNull();

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded->enabled)->toBeTrue();
    expect($reloaded->getEnabledForSite())->toBeTrue();
    expect($reloaded->getIsPendingPublish())->toBeFalse();
});

it('removes pending-add nodes immediately when staged for deletion', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'Unsaved add', '/unsaved-add');
    $node->enabled = false;
    $node->setEnabledForSite(false);
    $node->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException('Failed saving pending-add fixture.');
    }

    $nodeId = (int)$node->id;
    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->addAddedNode($session, $nodeId);

    $session = $buildSessions->getSession($nav->id, $siteId);
    $buildSessions->stageDelete($session, $node);

    expect(Node::find()->id($nodeId)->status(null)->one())->toBeNull();
    expect($buildSessions->getSession($nav->id, $siteId)?->addedNodeIds ?? [])->not->toContain($nodeId);
});

it('hard-deletes orphaned pending-add nodes that are missing from addedNodeIds', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'Orphan pending', '/orphan-pending');
    $node->enabled = false;
    $node->setEnabledForSite(false);
    $node->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException('Failed saving orphan pending-add fixture.');
    }

    $nodeId = (int)$node->id;
    $session = $buildSessions->getOrCreate($nav->id, $siteId);

    // Session exists but this node was never recorded in addedNodeIds.
    $buildSessions->stageDelete($session, $node);

    expect(Node::find()->id($nodeId)->status(null)->one())->toBeNull();
});

it('stages and publishes node deletions through the build session', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'Delete me', '/delete-me');
    $nodeId = (int)$node->id;

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->stageDelete($session, $node);

    $reloaded = Node::find()->id($nodeId)->status(null)->one();

    expect($reloaded->getIsPendingDelete())->toBeTrue();
    expect($reloaded->enabled)->toBeFalse();

    $session = $buildSessions->getSession($nav->id, $siteId);
    $result = $buildSessions->publish($session);

    expect($result['deletedCount'])->toBe(1);
    expect(Node::find()->id($nodeId)->status(null)->one())->toBeNull();
});

it('discards a build session and restores staged deletes', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'Restore me', '/restore-me');
    $nodeId = (int)$node->id;

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->stageDelete($session, $node);

    $session = $buildSessions->getSession($nav->id, $siteId);
    $buildSessions->discard($session);

    expect($buildSessions->getSession($nav->id, $siteId))->toBeNull();

    $reloaded = Node::find()->id($nodeId)->status(null)->one();

    expect($reloaded)->not->toBeNull();
    expect($reloaded->getIsPendingDelete())->toBeFalse();
    expect($reloaded->enabled)->toBeTrue();
});

it('unstages a single pending delete without discarding the whole session', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $nodeA = NavigationFixtureFactory::customNode($nav, 'Keep staged', '/keep-staged');
    $nodeB = NavigationFixtureFactory::customNode($nav, 'Restore me', '/restore-me');
    $nodeBId = (int)$nodeB->id;

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->stageDelete($session, $nodeA);
    $buildSessions->stageDelete($session, $nodeB);

    $session = $buildSessions->getSession($nav->id, $siteId);
    $buildSessions->unstageDelete($session, $nodeB);

    expect($buildSessions->getSession($nav->id, $siteId))->not->toBeNull();
    expect($buildSessions->getChangeCount($nav->id, $siteId))->toBe(1);

    $restored = Node::find()->id($nodeBId)->status(null)->one();
    $stillStaged = Node::find()->id($nodeA->id)->status(null)->one();

    expect($restored->getIsPendingDelete())->toBeFalse();
    expect($restored->enabled)->toBeTrue();
    expect($stillStaged->getIsPendingDelete())->toBeTrue();
});

it('blocks saving pending delete nodes in the builder', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'No edits', '/no-edits');
    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->stageDelete($session, $node);

    $reloaded = Node::find()->id($node->id)->status(null)->one();
    $reloaded->title = 'Changed title';

    expect(Craft::$app->getElements()->saveElement($reloaded))->toBeFalse();

    $dbNode = Node::find()->id($node->id)->status(null)->one();

    expect($dbNode->title)->toBe('No edits');
});

it('tracks pending publish nodes by internal data flag', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Pending', '/pending');
    $node->enabled = false;
    $node->setEnabledForSite(false);
    $node->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException('Failed saving pending node fixture.');
    }

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded)->not->toBeNull();
    expect($reloaded->getIsPendingPublish())->toBeTrue();
    expect(Navigation::$plugin->getNodes()->getPendingPublishCount($nav->id, $node->siteId))->toBe(1);
});

it('reports draft status for pending publish nodes in the builder', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Pending', '/pending');
    $node->enabled = false;
    $node->setEnabledForSite(false);
    $node->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException('Failed saving pending node fixture.');
    }

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded->getStatus())->toBe(Node::STATUS_PENDING_ADD);
});

it('stages duplicated nodes for publish like newly added nodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();
    $nodesService = Navigation::$plugin->getNodes();

    $node = NavigationFixtureFactory::customNode($nav, 'Original', '/original');
    $nodeId = (int)$node->id;

    $result = $nodesService->duplicateNodesForBuilder($nav->id, $siteId, [$nodeId], false, true);

    expect($result['successCount'])->toBe(1);
    expect($result['duplicatedNodeIds'])->toHaveCount(1);

    $duplicateId = $result['duplicatedNodeIds'][0];
    $duplicate = Node::find()->id($duplicateId)->status(null)->one();

    expect($duplicate->getIsPendingPublish())->toBeTrue();
    expect($duplicate->enabled)->toBeFalse();
    expect($duplicate->getEnabledForSite())->toBeFalse();
    expect($duplicate->getStatus())->toBe(Node::STATUS_PENDING_ADD);

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->stageAddedNodes($session, $result['duplicatedNodeIds']);

    expect($buildSessions->getChangeCount($nav->id, $siteId))->toBeGreaterThan(0);

    $published = $buildSessions->publish($buildSessions->getSession($nav->id, $siteId));

    expect($published['publishedCount'])->toBe(1);

    $publishedDuplicate = Node::find()->id($duplicateId)->status(null)->one();

    expect($publishedDuplicate->enabled)->toBeTrue();
    expect($publishedDuplicate->getEnabledForSite())->toBeTrue();
    expect($publishedDuplicate->getIsPendingPublish())->toBeFalse();
});

it('publishes a pending child moved to root while its parent is staged for delete', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();
    $group = NavigationFixtureFactory::categoryGroup();

    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    $child = NavigationFixtureFactory::dynamicCategoryGroupNode($nav, $group, $parent);
    $child->enabled = false;
    $child->setEnabledForSite(false);
    $child->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($child)) {
        throw new RuntimeException('Failed saving pending dynamic child: ' . json_encode($child->getErrors()));
    }

    $parentId = (int)$parent->id;
    $childId = (int)$child->id;

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->addAddedNode($session, $childId);
    $session = $buildSessions->getSession($nav->id, $siteId);
    $buildSessions->stageDelete($session, $parent);

    // Mimic collectStructureMoves after excluding pending-delete parents: child at root.
    $moves = [
        ['elementId' => $childId, 'parentId' => null, 'prevId' => null],
    ];

    $session = $buildSessions->getSession($nav->id, $siteId);
    $result = $buildSessions->publish($session, true, $moves);

    expect($result['publishedCount'])->toBe(1)
        ->and($result['deletedCount'])->toBe(1);

    expect(Node::find()->id($parentId)->status(null)->one())->toBeNull();

    $publishedChild = Node::find()
        ->id($childId)
        ->status(null)
        ->structureId($nav->structureId)
        ->one();

    expect($publishedChild)->not->toBeNull()
        ->and($publishedChild->getIsPendingPublish())->toBeFalse()
        ->and((int)$publishedChild->level)->toBe(1);
});

it('ignores structure moves for staged-delete nodes when publishing', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $parent = NavigationFixtureFactory::customNode($nav, 'Legacy parent', '/legacy-parent');
    $child = NavigationFixtureFactory::customNode($nav, 'Legacy child', '/legacy-child', $parent);
    $child->enabled = false;
    $child->setEnabledForSite(false);
    $child->setPendingPublish(true);

    if (!Craft::$app->getElements()->saveElement($child)) {
        throw new RuntimeException('Failed saving pending child: ' . json_encode($child->getErrors()));
    }

    $parentId = (int)$parent->id;
    $childId = (int)$child->id;

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->addAddedNode($session, $childId);
    $session = $buildSessions->getSession($nav->id, $siteId);
    $buildSessions->stageDelete($session, $parent);

    // Older clients may still emit moves for the pending-delete parent.
    $moves = [
        ['elementId' => $parentId, 'parentId' => null, 'prevId' => null],
        ['elementId' => $childId, 'parentId' => null, 'prevId' => $parentId],
    ];

    $session = $buildSessions->getSession($nav->id, $siteId);
    $result = $buildSessions->publish($session, true, $moves);

    expect($result['publishedCount'])->toBe(1)
        ->and($result['deletedCount'])->toBe(1)
        ->and(Node::find()->id($parentId)->status(null)->one())->toBeNull()
        ->and(Node::find()->id($childId)->status(null)->structureId($nav->structureId)->one())->not->toBeNull();
});

it('reports disabled status for explicitly disabled nodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::disabledCustomNode($nav, 'Disabled', '/disabled');

    expect($node->getStatus())->toBe(Node::STATUS_DISABLED);
});

it('restores orphaned pending delete nodes through unstage delete', function() {
    $nav = NavigationFixtureFactory::menu();
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;
    $buildSessions = Navigation::$plugin->getBuildSessions();

    $node = NavigationFixtureFactory::customNode($nav, 'Orphaned restore', '/orphaned-restore');
    $node->setPendingDeleteRestoreState(true, true);
    $node->setPendingDelete(true);
    $node->enabled = false;
    $node->setEnabledForSite(false);

    if (!Craft::$app->getElements()->saveElement($node)) {
        throw new RuntimeException('Failed saving orphaned pending delete fixture.');
    }

    $session = $buildSessions->getOrCreate($nav->id, $siteId);
    $buildSessions->unstageDelete($session, $node);

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded->getIsPendingDelete())->toBeFalse();
    expect($reloaded->enabled)->toBeTrue();
    expect($reloaded->getEnabledForSite())->toBeTrue();
});
