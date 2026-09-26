<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Asset as AssetNodeType;

it('authorizes asset nodes throughout an allowed volume folder tree', function() {
    $menu = F::menu();
    $handle = 'navigationAssetAuth' . uniqid();
    $path = Craft::getAlias('@webroot') . '/' . $handle;
    $filesystem = new craft\fs\Local([
        'name' => 'Asset authorization files',
        'handle' => $handle,
        'path' => $path,
        'hasUrls' => true,
        'url' => 'https://files.test/',
    ]);
    expect(Craft::$app->getFs()->saveFilesystem($filesystem))->toBeTrue();
    $volume = new craft\models\Volume([
        'name' => 'Asset authorization volume',
        'handle' => $handle,
        'fsHandle' => $handle,
    ]);

    try {
        expect(Craft::$app->getVolumes()->saveVolume($volume))->toBeTrue();
        $menu->permissions = [
            AssetNodeType::class => [
                'enabled' => true,
                'permissions' => ['volume:' . $volume->uid],
            ],
        ];
        expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

        $folder = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume('downloads/guides', $volume, false);
        $temporary = tempnam(sys_get_temp_dir(), 'navigation-asset-auth-');
        file_put_contents($temporary, 'Nested asset');
        $asset = new craft\elements\Asset([
            'volumeId' => $volume->id,
            'newFolderId' => $folder->id,
            'tempFilePath' => $temporary,
            'filename' => 'guide.txt',
            'title' => 'Nested guide',
        ]);
        expect(Craft::$app->getElements()->saveElement($asset))->toBeTrue();

        boundaryRequest(function() use ($asset, $menu) {
            Craft::$app->getRequest()->setBodyParams(['nodes' => [[
                'menuId' => $menu->id,
                'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
                'type' => AssetNodeType::class,
                'elementId' => $asset->id,
                'elementSiteId' => $asset->siteId,
                'title' => $asset->title,
                'enabled' => true,
                'enabledForSite' => true,
            ]]]);
            $addResponse = (new verbb\navigation\controllers\NodesController('nodes', Navigation::$plugin))->actionAddNodes();
            expect($addResponse->statusCode)->toBe(200);

            $node = Node::find()->menuId($menu->id)->status(null)->one();
            $user = Craft::$app->getUser()->getIdentity();
            expect($node)->not->toBeNull()
                ->and($node->canSave($user))->toBeTrue();

            Craft::$app->getRequest()->setBodyParams([
                'menuId' => $menu->id,
                'siteId' => $node->siteId,
                'nodeIds' => [$node->id],
            ]);
            $response = (new verbb\navigation\controllers\BuilderController('builder', Navigation::$plugin))->actionDuplicateNodes();

            expect($response->statusCode)->toBe(200)
                ->and($response->data['duplicatedNodeIds'])->toHaveCount(1)
                ->and((int)Node::find()->menuId($menu->id)->status(null)->count())->toBe(2);
        });
    } finally {
        if ($volume->id) {
            Craft::$app->getVolumes()->deleteVolume($volume);
        }
        Craft::$app->getFs()->removeFilesystem($filesystem);
        craft\helpers\FileHelper::removeDirectory($path);
    }
});
