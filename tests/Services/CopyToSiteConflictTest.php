<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\NodesController;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation as N;
use yii\web\ConflictHttpException;

it('rejects an uncertain cross-site copy retry and allows a deliberate copy after reload', function(bool $deep) {
    $target = F::existingSecondarySite();
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $parent = F::customNode($menu, 'Parent', '/parent');
    F::customNode($menu, 'Child', '/child', $parent);
    $body = ['menuId' => $menu->id, 'sourceSiteId' => $parent->siteId, 'siteId' => $target->id,
        'nodeIds' => [$parent->id], 'deep' => $deep, 'structureRevision' => BuilderStructureRevision::get($menu)];
    $send = fn(array $payload) => boundaryRequest(function() use ($payload) {
        Craft::$app->getRequest()->setBodyParams($payload);
        return (new NodesController('nodes', N::$plugin))->actionCopyToSite();
    });
    $first = $send($body);
    expect($first->statusCode)->toBe(200);
    $ids = Node::find()->menuId($menu->id)->siteId($target->id)->ids();
    expect($ids)->toHaveCount($deep ? 2 : 1);
    expect(fn() => $send($body))->toThrow(ConflictHttpException::class);
    expect(Node::find()->menuId($menu->id)->siteId($target->id)->ids())->toBe($ids);
    expect($first->data['previousStructureRevision'])->toBe($body['structureRevision']);
    $body['structureRevision'] = $first->data['structureRevision'];
    expect($send($body)->statusCode)->toBe(200);
    expect(Node::find()->menuId($menu->id)->siteId($target->id)->ids())->toHaveCount($deep ? 4 : 2);
    expect(Node::find()->menuId($menu->id)->siteId($parent->siteId)->ids())->toHaveCount(2);
})->with([false, true]);

it('rejects a stale cross-site copy after another editor changes the target tree', function() {
    $target = F::existingSecondarySite();
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $node = F::customNode($menu, 'Original', '/original');
    $revision = BuilderStructureRevision::get($menu);
    $other = F::customNode($menu, 'Other editor', '/other', null, $target->id);
    boundaryRequest(function() use ($menu, $node, $target, $revision) {
        Craft::$app->getRequest()->setBodyParams(['menuId' => $menu->id, 'sourceSiteId' => $node->siteId,
            'siteId' => $target->id, 'nodeIds' => [$node->id], 'structureRevision' => $revision]);
        expect(fn() => (new NodesController('nodes', N::$plugin))->actionCopyToSite())->toThrow(ConflictHttpException::class);
    });
    expect(Node::find()->menuId($menu->id)->siteId($target->id)->ids())->toBe([$other->id]);
});

it('continues to accept legacy cross-site copies without a revision', function() {
    $target = F::existingSecondarySite();
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $node = F::customNode($menu, 'Legacy', '/legacy');
    boundaryRequest(function() use ($node, $target) {
        Craft::$app->getRequest()->setBodyParams(['nodeId' => $node->id, 'siteId' => $target->id]);
        expect((new NodesController('nodes', N::$plugin))->actionCopyToSite()->statusCode)->toBe(200);
    });
    expect(Node::find()->menuId($menu->id)->siteId($target->id)->ids())->toHaveCount(1);
});
