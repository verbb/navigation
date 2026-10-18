<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\BuilderController;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\BuilderStructureRevision;
use verbb\navigation\Navigation as N;
use yii\web\ConflictHttpException;

it('rejects duplicate retries from an uncertain response without creating more copies', function(bool $live, bool $deep) {
    $settings = N::$plugin->getSettings();
    $original = $settings->builderLiveStructure;
    $settings->builderLiveStructure = $live;
    try {
        $menu = F::menu();
        $parent = F::customNode($menu, 'Parent', '/parent');
        F::customNode($menu, 'Child', '/child', $parent);
        $body = ['menuId' => $menu->id, 'siteId' => $parent->siteId,
            'nodeIds' => [$parent->id], 'deep' => $deep,
            'structureRevision' => BuilderStructureRevision::get($menu)];
        $send = fn(array $payload) => boundaryRequest(function() use ($payload) {
            Craft::$app->getRequest()->setBodyParams($payload);
            return (new BuilderController('builder', N::$plugin))->actionDuplicateNodes();
        });
        $first = $send($body);
        expect($first->statusCode)->toBe(200);
        $ids = Node::find()->menuId($menu->id)->status(null)->ids();
        expect($ids)->toHaveCount($deep ? 4 : 3);
        // The browser never received the first response and still has its old baseline.
        expect(fn() => $send($body))->toThrow(ConflictHttpException::class);
        expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe($ids);
        // Reloading adopts the current tree; a deliberate new duplicate still works.
        $body['structureRevision'] = BuilderStructureRevision::get($menu);
        expect($send($body)->statusCode)->toBe(200);
        expect(Node::find()->menuId($menu->id)->status(null)->ids())->toHaveCount($deep ? 6 : 4);
    } finally {
        $settings->builderLiveStructure = $original;
    }
})->with([false, true])->with([false, true]);

it('rejects duplication from an editor whose menu was changed by someone else', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Original', '/original');
    $revision = BuilderStructureRevision::get($menu);
    F::customNode($menu, 'Other editor addition', '/other');
    $ids = Node::find()->menuId($menu->id)->status(null)->ids();
    boundaryRequest(function() use ($menu, $node, $revision, $ids) {
        Craft::$app->getRequest()->setBodyParams(['menuId' => $menu->id,
            'siteId' => $node->siteId, 'nodeIds' => [$node->id], 'structureRevision' => $revision]);
        expect(fn() => (new BuilderController('builder', N::$plugin))->actionDuplicateNodes())
            ->toThrow(ConflictHttpException::class);
        expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe($ids);
        expect(N::$plugin->getBuildSessions()->getSession($menu->id, $node->siteId))->toBeNull();
    });
});
