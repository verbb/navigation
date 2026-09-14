<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision as Revision;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

it('rejects alternate native Navigation site selectors before moving anything', function(mixed $selector) {
    $menu = F::menu();
    $node = F::customNode($menu, 'Node', '/node');
    boundaryRequest(function() use ($menu, $node, $selector) {
        Craft::$app->request->setBodyParams(['structureId' => $menu->structureId,
            'elementId' => $node->id, 'siteId' => $selector === 'array' ? [$node->siteId] : $selector]);
        $controller = new craft\controllers\StructuresController('structures', Craft::$app);
        $event = new yii\base\ActionEvent($controller->createAction('move-element'));
        expect(fn() => $controller->trigger($controller::EVENT_BEFORE_ACTION, $event))->toThrow(BadRequestHttpException::class);
    });
    expect(Node::find()->id($node->id)->one()->getParentId())->toBeNull();
})->with(['*', 'array', null, 0, '1e0', 1.5, true]);

it('rejects a missing Navigation site variant rather than skipping authorization', function() {
    $site = F::secondarySite();
    $menu = F::menu(null, verbb\navigation\models\MenuSettings::PROPAGATION_METHOD_NONE);
    $node = F::customNode($menu, 'Node', '/node');
    boundaryRequest(function() use ($menu, $node, $site) {
        Craft::$app->request->setBodyParams(['structureId' => $menu->structureId, 'elementId' => $node->id, 'siteId' => $site->id]);
        $controller = new craft\controllers\StructuresController('structures', Craft::$app);
        expect(fn() => $controller->trigger($controller::EVENT_BEFORE_ACTION, new yii\base\ActionEvent($controller->createAction('move-element'))))
            ->toThrow(BadRequestHttpException::class);
    });
});

it('leaves other element types to Crafts native move authorization', function() {
    $user = craft\elements\User::find()->admin()->one();
    boundaryRequest(function() use ($user) {
        Craft::$app->request->setBodyParams(['elementId' => $user->id, 'siteId' => '*']);
        $controller = new craft\controllers\StructuresController('structures', Craft::$app);
        $controller->trigger($controller::EVENT_BEFORE_ACTION, new yii\base\ActionEvent($controller->createAction('move-element')));
        expect(true)->toBeTrue();
    });
});

it('rejects builder publication that moves another owners pending descendant', function(bool $legacy) {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Child', '/child', $parent);
    $target = F::customNode($menu, 'Target', '/target');
    $child->setPendingPublish(true); $child->enabled = false;
    Craft::$app->elements->saveElement($child);
    $owner = new craft\elements\User(['username' => uniqid('owner'), 'email' => uniqid('owner').'@example.test']);
    Craft::$app->elements->saveElement($owner);
    $sessions = N::$plugin->getBuildSessions();
    $sessions->stageAddedNodes($sessions->getOrCreate($menu->id, $child->siteId, $owner->id), [$child->id]);
    $before = Revision::get($menu);
    boundaryRequest(function() use ($menu, $parent, $target, $legacy, $before, $sessions) {
        $session = $sessions->getOrCreate($menu->id, $parent->siteId);
        $baseline = $session->getStorageRevision();
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $parent->siteId,
            'applyStructure' => true, 'structureRevision' => $before,
            'moves' => [['elementId' => $parent->id, 'parentId' => $target->id, 'prevId' => null]]]);
        $controller = $legacy ? new verbb\navigation\controllers\NodesController('nodes', N::$plugin)
            : new verbb\navigation\controllers\BuildSessionsController('build-sessions', N::$plugin);
        expect(fn() => $legacy ? $controller->actionSaveStructure() : $controller->actionPublish())->toThrow(ForbiddenHttpException::class);
        expect($sessions->getSession($menu->id, $parent->siteId)->getStorageRevision())->toBe($baseline);
    });
    expect(Revision::get($menu))->toBe($before);
    expect(Node::find()->id($child->id)->status(null)->one()->level)->toBe(2);
})->with([false, true]);

it('allows an unchanged private subtree alongside an authorized reorder', function() {
    $menu = F::menu();
    $private = F::customNode($menu, 'Private', '/private');
    $a = F::customNode($menu, 'A', '/a');
    $b = F::customNode($menu, 'B', '/b');
    $private->setPendingPublish(true); $private->enabled = false;
    Craft::$app->elements->saveElement($private);
    $owner = new craft\elements\User(['username' => uniqid('owner'), 'email' => uniqid('owner').'@example.test']);
    Craft::$app->elements->saveElement($owner);
    $sessions = N::$plugin->getBuildSessions();
    $sessions->stageAddedNodes($sessions->getOrCreate($menu->id, $private->siteId, $owner->id), [$private->id]);
    // Leave the private node last so moving authorized nodes needs no private anchor.
    Craft::$app->structures->appendToRoot($menu->structureId, $private);
    boundaryRequest(function() use ($menu, $private, $a, $b) {
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $a->siteId,
            'applyStructure' => true, 'structureRevision' => Revision::get($menu), 'moves' => [
                ['elementId' => $b->id, 'parentId' => null, 'prevId' => null],
                ['elementId' => $a->id, 'parentId' => null, 'prevId' => $b->id],
                ['elementId' => $private->id, 'parentId' => null, 'prevId' => $a->id],
            ]]);
        $response = (new verbb\navigation\controllers\BuildSessionsController('build-sessions', N::$plugin))->actionPublish();
        expect($response->statusCode)->toBe(200);
    });
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe([$b->id, $a->id, $private->id]);
    expect(Node::find()->id($private->id)->status(null)->one()->getIsPendingPublish())->toBeTrue();
});

it('allows the pending owner to publish a subtree move', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Pending', '/pending', $parent);
    $target = F::customNode($menu, 'Target', '/target');
    $child->setPendingPublish(true); $child->enabled = false;
    Craft::$app->elements->saveElement($child);
    boundaryRequest(function() use ($menu, $parent, $child, $target) {
        $sessions = N::$plugin->getBuildSessions();
        $sessions->stageAddedNodes($sessions->getOrCreate($menu->id, $parent->siteId), [$child->id]);
        Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $parent->siteId,
            'applyStructure' => true, 'structureRevision' => Revision::get($menu),
            'moves' => [['elementId' => $parent->id, 'parentId' => $target->id, 'prevId' => null]]]);
        $response = (new verbb\navigation\controllers\BuildSessionsController('build-sessions', N::$plugin))->actionPublish();
        expect($response->statusCode)->toBe(200);
        expect($response->data['publishedCount'])->toBe(1);
    });
    expect(Node::find()->id($parent->id)->one()->getParentId())->toBe($target->id);
});

it('enforces type permissions in the live move preflight without changing the tree', function() {
    $menu = F::menu();
    $a = F::customNode($menu, 'A', '/a');
    $b = F::customNode($menu, 'B', '/b');
    $menu->permissions = [verbb\navigation\nodetypes\Custom::class => ['enabled' => false]];
    N::$plugin->getMenus()->saveMenu($menu);
    $before = Revision::get($menu);
    boundaryRequest(function() use ($menu, $a, $b) {
        expect(fn() => verbb\navigation\helpers\MenuAuth::requireStructureMoves($menu, $a->siteId,
            [['elementId' => $b->id, 'parentId' => null, 'prevId' => null]]))
            ->toThrow(ForbiddenHttpException::class);
    });
    expect(Revision::get($menu))->toBe($before);
});

it('bounds subtree authorization reads and restores the cache after rejection', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $target = F::customNode($menu, 'Target', '/target');
    for ($i = 0; $i < 25; $i++) F::customNode($menu, 'Child '.$i, '/child-'.$i, $parent);
    boundaryRequest(function() use ($menu, $parent, $target) {
        $db = Craft::$app->db;
        $cache = $db->queryCache; $enabled = $db->enableQueryCache;
        $moves = [['elementId' => $parent->id, 'parentId' => $target->id, 'prevId' => null]];
        $check = fn() => verbb\navigation\helpers\MenuAuth::requireStructureMoves($menu, $parent->siteId, $moves);
        $profile = Tests\Support\Performance\QueryProfiler::profile($check);
        expect($profile['queries'])->toBeLessThanOrEqual(8);
        expect($db->queryCache)->toBe($cache);
        expect($db->enableQueryCache)->toBe($enabled);
        // A later preflight must observe newly staged ownership, not an earlier cached allow.
        $owner = new craft\elements\User(['username' => uniqid('owner'), 'email' => uniqid('owner').'@example.test']);
        Craft::$app->elements->saveElement($owner);
        $sessions = N::$plugin->getBuildSessions();
        $sessions->stageAddedNodes($sessions->getOrCreate($menu->id, $parent->siteId, $owner->id), [$parent->id]);
        expect($check)->toThrow(ForbiddenHttpException::class);
        expect($db->queryCache)->toBe($cache);
        expect($db->enableQueryCache)->toBe($enabled);
    });
});
