<?php

use craft\helpers\StringHelper;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\NodesController;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation as N;

it('replays an acknowledged or lost-response add without duplicating its batch', function(bool $live) {
    $settings = N::$plugin->getSettings();
    $original = $settings->builderLiveStructure;
    $settings->builderLiveStructure = $live;
    try {
        $menu = F::menu();
        $payload = ['requestId' => StringHelper::UUID(), 'nodes' => [boundaryAddPayload($menu, 'First'), boundaryAddPayload($menu, 'Second')]];
        $send = function(array $body) {
            return boundaryRequest(function() use ($body) {
                Craft::$app->getRequest()->setBodyParams($body);
                return (new NodesController('nodes', N::$plugin))->actionAddNodes();
            });
        };
        expect($send($payload)->statusCode)->toBe(200);
        $ids = Node::find()->menuId($menu->id)->status(null)->ids();
        expect($ids)->toHaveCount(2);
        expect($send($payload)->statusCode)->toBe(200);
        expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe($ids);
        if (!$live) {
            boundaryRequest(function() use ($menu, $ids) {
                expect(N::$plugin->getBuildSessions()->getSession($menu->id, Craft::$app->getSites()->getPrimarySite()->id)->addedNodeIds)->toBe($ids);
            });
        }
        // A new deliberate submission of the same values must still create new nodes.
        $payload['requestId'] = StringHelper::UUID();
        expect($send($payload)->statusCode)->toBe(200);
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(4);
    } finally {
        $settings->builderLiveStructure = $original;
    }
})->with([false, true]);

it('does not restore deleted nodes when an old add is retried', function() {
    $menu = F::menu();
    $body = ['requestId' => StringHelper::UUID(), 'nodes' => [boundaryAddPayload($menu, 'Removed')]];
    boundaryRequest(function() use ($menu, $body) {
        Craft::$app->getRequest()->setBodyParams($body);
        $controller = new NodesController('nodes', N::$plugin);
        expect($controller->actionAddNodes()->statusCode)->toBe(200);
        $node = Node::find()->menuId($menu->id)->status(null)->one();
        expect(Craft::$app->getElements()->deleteElement($node))->toBeTrue();
        expect(fn() => $controller->actionAddNodes())->toThrow(yii\web\ConflictHttpException::class);
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(0);
    });
});

it('keeps legacy submissions independent when they have no retry identity', function() {
    $menu = F::menu();
    boundaryRequest(function() use ($menu) {
        Craft::$app->getRequest()->setBodyParams(['nodes' => [boundaryAddPayload($menu, 'Legacy')]]);
        $controller = new NodesController('nodes', N::$plugin);
        expect($controller->actionAddNodes()->statusCode)->toBe(200);
        expect($controller->actionAddNodes()->statusCode)->toBe(200);
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(2);
    });
});

it('scopes add retry identities to the editor', function() {
    $menu = F::menu();
    $other = new craft\elements\User(['username' => uniqid('retry-editor-'), 'email' => uniqid('retry-editor-') . '@example.test', 'admin' => true]);
    expect(Craft::$app->getElements()->saveElement($other))->toBeTrue();
    boundaryRequest(function() use ($menu, $other) {
        Craft::$app->getRequest()->setBodyParams(['requestId' => StringHelper::UUID(), 'nodes' => [boundaryAddPayload($menu, 'Separate editors')]]);
        $controller = new NodesController('nodes', N::$plugin);
        expect($controller->actionAddNodes()->statusCode)->toBe(200);
        Craft::$app->getUser()->setIdentity($other);
        expect($controller->actionAddNodes()->statusCode)->toBe(200);
        expect((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(2);
    });
});
