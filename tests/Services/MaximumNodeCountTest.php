<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\BuilderController;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\Navigation;
use verbb\navigation\nodetypes\Custom;

it('keeps builder duplication within the total node limit', function(bool $live) {
    $menu = F::menu();
    $menu->maxNodes = 1;
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $node = F::customNode($menu, 'Original', '/original');
    $settings = Navigation::$plugin->getSettings();
    $before = $settings->builderLiveStructure;
    $settings->builderLiveStructure = $live;
    try {
        boundaryRequest(function() use ($menu, $node) {
            Craft::$app->request->setBodyParams(['menuId' => $menu->id, 'siteId' => $node->siteId, 'nodeIds' => [$node->id]]);
            $response = (new BuilderController('builder', Navigation::$plugin))->actionDuplicateNodes();
            expect($response->statusCode)->toBe(400);
            expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe([$node->id]);
        });
    } finally {
        $settings->builderLiveStructure = $before;
    }
})->with([false, true]);

it('rolls back imports that exceed the total node limit', function(string $action) {
    $menu = F::menu();
    $menu->maxNodes = 1;
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $node = F::customNode($menu, 'Original', '/original');
    $payload = ImportExportHelper::generateMenuExport($menu);
    $payload['nodes'][] = ['title' => 'Extra', 'type' => Custom::class, 'url' => '/extra'];
    $config = Craft::$app->projectConfig->get('navigation');
    $result = ImportExportHelper::importMenuFromJson($payload, $action);
    expect($result->hasImportErrors())->toBeTrue();
    expect($result->nodesCreated)->toBe(0);
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe([$node->id]);
    expect(Craft::$app->projectConfig->get('navigation'))->toBe($config);
})->with(['create', 'update']);

it('counts disabled stored nodes and permits edits at the total limit', function() {
    $menu = F::menu();
    $menu->maxNodes = 1;
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $node = F::customNode($menu, 'Original', '/original');
    $node->enabled = false;
    $node->title = 'Edited';
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $extra = new Node(['menuId' => $menu->id, 'siteId' => $node->siteId, 'type' => Custom::class, 'title' => 'Extra', 'url' => '/extra']);
    expect(fn() => Craft::$app->elements->saveElement($extra))->toThrow(yii\base\UserException::class);
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe([$node->id]);
    expect(Node::find()->id($node->id)->status(null)->one()->title)->toBe('Edited');
});

it('keeps an over-limit native restore trashed and permits retry after removing the replacement', function() {
    $menu = F::menu();
    $menu->maxNodes = 1;
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $node = F::customNode($menu, 'Original', '/original');
    expect(Craft::$app->elements->deleteElement($node))->toBeTrue();
    $replacement = F::customNode($menu, 'Replacement', '/replacement');
    $read = static fn() => Node::find()->id($node->id)->status(null)->trashed()->one();
    expect(fn() => Craft::$app->elements->restoreElement($read()))->toThrow(yii\base\UserException::class);
    expect($read())->not->toBeNull();
    expect(Craft::$app->elements->deleteElement($replacement))->toBeTrue();
    expect(Craft::$app->elements->restoreElement($read()))->toBeTrue();
    expect(Node::find()->menuId($menu->id)->status(null)->ids())->toBe([$node->id]);
});
