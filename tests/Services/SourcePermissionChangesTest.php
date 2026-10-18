<?php

use craft\elements\Asset;
use craft\elements\User;
use craft\helpers\StringHelper;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\BuildSessionsController;
use verbb\navigation\controllers\NodesController;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation as N;
use verbb\navigation\nodetypes\Asset as AssetType;
use verbb\navigation\nodetypes\Category as CategoryType;
use yii\web\ForbiddenHttpException;

it('rechecks revoked source permissions on add retries and publication, then recovers without duplicates', function(string $source) {
    $menu = F::menu();
    $editor = new User(['username' => uniqid('source-change-'), 'email' => uniqid('source-change-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($editor))->toBeTrue();
    $site = Craft::$app->getSites()->getPrimarySite();
    $baseGrants = ['accessCp', 'editSite:' . $site->uid, 'navigation-manageMenu:' . $menu->uid];
    $filesystem = $volume = $group = null;
    $path = null;
    try {
        if ($source === 'category') {
            $group = F::categoryGroup();
            $element = F::categories(1, $group)[0];
            $type = CategoryType::class;
            $viewGrants = ['viewCategories:' . $group->uid];
            $retainedGrants = [];
        } else {
            $handle = 'sourceChange' . uniqid();
            $path = Craft::getAlias('@webroot') . '/' . $handle;
            $filesystem = new craft\fs\Local(['name' => $handle, 'handle' => $handle,
                'path' => $path, 'hasUrls' => true, 'url' => 'https://files.test/']);
            expect(Craft::$app->getFs()->saveFilesystem($filesystem))->toBeTrue();
            $volume = new craft\models\Volume(['name' => $handle, 'handle' => $handle, 'fsHandle' => $handle]);
            expect(Craft::$app->getVolumes()->saveVolume($volume))->toBeTrue();
            $folder = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume('downloads/guides', $volume, false);
            $temporary = tempnam(sys_get_temp_dir(), 'navigation-source-change-');
            file_put_contents($temporary, 'Permission change fixture');
            $element = new Asset(['volumeId' => $volume->id, 'newFolderId' => $folder->id,
                'tempFilePath' => $temporary, 'filename' => 'guide.txt',
                'uploaderId' => $source === 'own-asset' ? $editor->id : User::find()->admin()->one()->id]);
            expect(Craft::$app->getElements()->saveElement($element))->toBeTrue();
            $type = AssetType::class;
            $viewGrants = ['viewAssets:' . $volume->uid];
            $retainedGrants = [];
            if ($source === 'peer-asset') {
                $retainedGrants = $viewGrants;
                $viewGrants[] = 'viewPeerAssets:' . $volume->uid;
            }
        }
        $body = ['requestId' => StringHelper::UUID(), 'nodes' => [[
            'menuId' => $menu->id, 'siteId' => $site->id, 'type' => $type,
            'elementId' => $element->id, 'elementSiteId' => $element->siteId,
        ]]];
        $request = function(array $grants, callable $callback) use ($editor, $baseGrants) {
            return boundaryRequest(function() use ($grants, $callback, $editor, $baseGrants) {
                Craft::$app->set('userPermissions', new craft\services\UserPermissions());
                expect(Craft::$app->getUserPermissions()->saveUserPermissions($editor->id, [...$baseGrants, ...$grants]))->toBeTrue();
                Craft::$app->getUser()->setIdentity(User::find()->id($editor->id)->one());
                return $callback();
            });
        };
        $add = function() use ($body) {
            Craft::$app->getRequest()->setBodyParams($body);
            return (new NodesController('nodes', N::$plugin))->actionAddNodes();
        };
        $publish = function() use ($menu, $site) {
            Craft::$app->getRequest()->setBodyParams(['menuId' => $menu->id, 'siteId' => $site->id]);
            return (new BuildSessionsController('build-sessions', N::$plugin))->actionPublish();
        };
        expect($request($viewGrants, $add)->statusCode)->toBe(200);
        expect(fn() => $request($retainedGrants, $add))->toThrow(ForbiddenHttpException::class);
        expect(fn() => $request($retainedGrants, $publish))->toThrow(ForbiddenHttpException::class);
        $node = Node::find()->menuId($menu->id)->status(null)->one();
        expect($node->getIsPendingPublish())->toBeTrue();
        expect(Node::find()->menuId($menu->id)->all())->toBe([]);
        expect($request($viewGrants, $add)->statusCode)->toBe(200);
        expect($request($viewGrants, $publish)->data['publishedCount'])->toBe(1);
        expect(Node::find()->menuId($menu->id)->ids())->toBe([$node->id]);
    } finally {
        if ($group) Craft::$app->getCategories()->deleteGroupById($group->id);
        if ($volume?->id) Craft::$app->getVolumes()->deleteVolume($volume);
        if ($filesystem) Craft::$app->getFs()->removeFilesystem($filesystem);
        if ($path) craft\helpers\FileHelper::removeDirectory($path);
        Craft::$app->getElements()->deleteElement($editor);
    }
})->with(['category', 'own-asset', 'peer-asset']);
