<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\services\NavigationCache;

use craft\events\MoveElementEvent;
use craft\services\Structures;

use yii\base\Event;
use yii\web\BadRequestHttpException;

it('keeps capacity checks independent after a limited menu move', function() {
    $menu = F::menu();
    $first = F::customNode($menu, 'First', '/first');
    $second = F::customNode($menu, 'Second', '/second');
    $menu->maxNodesSettings = [['level' => 1, 'max' => 2]];
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    $nodes = Navigation::$plugin->getNodes();
    $previousTempNodes = $nodes->getTempNodes();

    try {
        expect(Craft::$app->getStructures()->moveBefore($menu->structureId, $second, $first))->toBeTrue();

        $otherMenu = F::menu();
        $other = F::customNode($otherMenu, 'Other', '/other');
        $otherMenu->maxNodes = 1;
        $otherMenu->maxNodesSettings = [['level' => 1, 'max' => 1]];
        expect(Navigation::$plugin->getMenus()->saveMenu($otherMenu))->toBeTrue();

        $other->title = 'Edited';
        expect(Craft::$app->getElements()->saveElement($other))->toBeTrue();

        $sessions = Navigation::$plugin->getBuildSessions();
        $session = $sessions->getOrCreate($otherMenu->id, $other->siteId);
        $sessions->stageDelete($session, $other);

        expect(Node::find()->id($other->id)->status(null)->one()->getIsPendingDelete())->toBeTrue();
        expect($nodes->getTempNodes())->toBe($previousTempNodes);
    } finally {
        $nodes->setTempNodes($previousTempNodes);
    }
});

it('publishes a full tree snapshot with one real move and one cache invalidation', function() {
    $menu = F::menu();
    $nodes = [];

    for ($i = 1; $i <= 20; $i++) {
        $nodes[] = F::customNode($menu, "Node {$i}", "/node-{$i}");
    }

    $ordered = array_merge([end($nodes)], array_slice($nodes, 0, -1));
    $moves = [];
    $previousId = null;

    foreach ($ordered as $node) {
        $moves[] = ['elementId' => $node->id, 'parentId' => null, 'prevId' => $previousId];
        $previousId = $node->id;
    }

    $structureEvents = 0;
    $structureHandler = function(MoveElementEvent $event) use ($menu, &$structureEvents) {
        if ($event->element instanceof Node && $event->element->menuId === $menu->id) {
            $structureEvents++;
        }
    };
    $cacheEvents = [];
    $cache = Navigation::$plugin->getNavigationCache();
    $cacheHandler = function($event) use (&$cacheEvents) {
        $cacheEvents[] = $event;
    };

    Event::on(Structures::class, Structures::EVENT_AFTER_UPDATE_ELEMENT, $structureHandler);
    $cache->on(NavigationCache::EVENT_INVALIDATE, $cacheHandler);

    try {
        $session = Navigation::$plugin->getBuildSessions()->getOrCreate($menu->id, $nodes[0]->siteId);
        Navigation::$plugin->getBuildSessions()->publish($session, true, $moves);
    } finally {
        Event::off(Structures::class, Structures::EVENT_AFTER_UPDATE_ELEMENT, $structureHandler);
        $cache->off(NavigationCache::EVENT_INVALIDATE, $cacheHandler);
    }

    $savedIds = Node::find()
        ->menuId($menu->id)
        ->siteId($nodes[0]->siteId)
        ->status(null)
        ->orderBy(['structureelements.lft' => SORT_ASC])
        ->ids();

    expect($savedIds)->toBe(array_map(fn(Node $node) => $node->id, $ordered));
    expect($structureEvents)->toBe(1);
    expect($cacheEvents)->toHaveCount(1);
    expect($cacheEvents[0]->menuUid)->toBe($menu->uid);
    expect($cacheEvents[0]->siteId)->toBe($nodes[0]->siteId);
    expect($cacheEvents[0]->nodeId)->toBeNull();
});

it('does not emit a consolidated invalidation when structure publication rolls back', function() {
    $menu = F::menu();
    $first = F::customNode($menu, 'First', '/first');
    $second = F::customNode($menu, 'Second', '/second');
    $moves = [
        ['elementId' => $second->id, 'parentId' => null, 'prevId' => null],
        ['elementId' => $first->id, 'parentId' => null, 'prevId' => $second->id],
    ];
    $veto = function(MoveElementEvent $event) use ($menu) {
        if ($event->element instanceof Node && $event->element->menuId === $menu->id) {
            $event->isValid = false;
        }
    };
    $cacheEvents = 0;
    $cache = Navigation::$plugin->getNavigationCache();
    $cacheHandler = function() use (&$cacheEvents) {
        $cacheEvents++;
    };

    Event::on(Structures::class, Structures::EVENT_BEFORE_UPDATE_ELEMENT, $veto);
    $cache->on(NavigationCache::EVENT_INVALIDATE, $cacheHandler);

    try {
        $session = Navigation::$plugin->getBuildSessions()->getOrCreate($menu->id, $first->siteId);
        expect(fn() => Navigation::$plugin->getBuildSessions()->publish($session, true, $moves))->toThrow(BadRequestHttpException::class);
    } finally {
        Event::off(Structures::class, Structures::EVENT_BEFORE_UPDATE_ELEMENT, $veto);
        $cache->off(NavigationCache::EVENT_INVALIDATE, $cacheHandler);
    }

    expect($cacheEvents)->toBe(0);
    expect(Node::find()->menuId($menu->id)->siteId($first->siteId)->ids())->toBe([$first->id, $second->id]);
});
