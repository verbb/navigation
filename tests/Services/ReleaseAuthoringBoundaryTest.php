<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation;
use verbb\navigation\controllers\NodesController;
use verbb\navigation\elements\Node;
use yii\web\ForbiddenHttpException;

it('only lets the session owner add children beneath a private pending parent', function() {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Private parent', '/parent');
    $parent->setPendingPublish(true);
    $parent->enabled = false;
    expect(Craft::$app->elements->saveElement($parent))->toBeTrue();
    $owner = new craft\elements\User(['username' => uniqid('parent-owner-'), 'email' => uniqid('parent-') . '@example.test']);
    expect(Craft::$app->elements->saveElement($owner))->toBeTrue();
    $sessions = Navigation::$plugin->getBuildSessions();
    $session = $sessions->getOrCreate($menu->id, $parent->siteId, $owner->id);
    $sessions->stageAddedNodes($session, [$parent->id]);

    boundaryRequest(function() use ($menu, $parent, $owner) {
        $site = Craft::$app->sites->getSiteById($parent->siteId);
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        expect(Craft::$app->userPermissions->saveUserPermissions($owner->id,
            ['accessCp', 'editSite:' . $site->uid, 'navigation-manageMenu:' . $menu->uid]))->toBeTrue();
        $payload = boundaryAddPayload($menu, 'New child');
        $payload['parentId'] = $parent->id;
        Craft::$app->request->setBodyParams(['nodes' => [$payload]]);
        expect(Craft::$app->user->id)->not->toBe($owner->id);
        $controller = new NodesController('nodes', Navigation::$plugin);
        expect(fn() => $controller->actionAddNodes())->toThrow(ForbiddenHttpException::class);
        expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe([$parent->id]);

        Craft::$app->user->setIdentity($owner);
        expect($owner->can('navigation-manageMenu:' . $menu->uid))->toBeTrue();
        $response = $controller->actionAddNodes();
        expect($response->getStatusCode())->toBe(200);
        $nodes = Node::find()->menuId($menu->id)->status(null)->all();
        expect($nodes)->toHaveCount(2);
        expect($nodes[1]->getParentId())->toBe($parent->id);
    });
});
