<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;

it('rejects a posted dynamic section excluded from the editors source options', function() {
    $menu = F::menu(); $section = F::entrySection();
    $menu->permissions = [verbb\navigation\nodetypes\Dynamic::class => ['enabled' => true]];
    N::$plugin->getMenus()->saveMenu($menu);
    boundaryRequest(function() use ($menu, $section) {
        $editor = new craft\elements\User(['username' => uniqid('dynamicEditor'), 'email' => uniqid('dynamic').'@example.test']);
        expect(Craft::$app->elements->saveElement($editor))->toBeTrue();
        Craft::$app->userPermissions->saveUserPermissions($editor->id, ['accessCp', 'navigation-manageMenu:'.$menu->uid]);
        Craft::$app->user->setIdentity($editor);
        expect($editor->can('viewEntries:'.$section->uid))->toBeFalse();
        expect(array_column(verbb\navigation\helpers\EntrySectionSettings::sectionOptions(), 'value'))->not->toContain((string)$section->id);
        Craft::$app->request->setBodyParams(['nodes' => [[
            'menuId' => $menu->id, 'siteId' => Craft::$app->sites->getPrimarySite()->id,
            'type' => verbb\navigation\nodetypes\Dynamic::class, 'title' => 'Unauthorized source',
            'data' => ['dynamicSource' => 'entrySection', 'sectionId' => $section->id],
        ]]]);
        $controller = new verbb\navigation\controllers\NodesController('nodes', N::$plugin);
        expect(fn() => $controller->actionAddNodes())->toThrow(yii\web\ForbiddenHttpException::class);
        expect(Node::find()->menuId($menu->id)->status(null)->count())->toBe(0);
    });
});

it('allows an explicitly granted dynamic section and rejects a later source switch', function() {
    $menu = F::menu(); $allowed = F::entrySection(); $denied = F::entrySection();
    $node = F::dynamicSectionNode($menu, $allowed);
    boundaryRequest(function() use ($menu, $allowed, $denied, $node) {
        $editor = new craft\elements\User(['username' => uniqid('dynamicOwner'), 'email' => uniqid('dynamicOwner').'@example.test']);
        Craft::$app->elements->saveElement($editor);
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->userPermissions->saveUserPermissions($editor->id, ['accessCp', 'navigation-manageMenu:'.$menu->uid, 'viewEntries:'.$allowed->uid]);
        Craft::$app->user->setIdentity($editor);
        expect($editor->can('viewEntries:'.$allowed->uid))->toBeTrue();
        expect($node->canSave($editor))->toBeTrue();
        $node->data = array_merge($node->data, ['sectionId' => $denied->id]);
        expect(Craft::$app->elements->saveElement($node))->toBeFalse();
        expect(Node::find()->id($node->id)->status(null)->one()->data['sectionId'])->toBe((string)$allowed->id);
    });
});

it('enforces category and asset dynamic source grants', function(string $kind) {
    $menu = F::menu();
    if ($kind === 'category') {
        $source = F::categoryGroup();
        $data = ['dynamicSource' => 'categoryGroup', 'groupId' => $source->id];
        $permission = 'viewCategories:'.$source->uid;
    } else {
        $handle = uniqid('auditVolume');
        craft\helpers\FileHelper::createDirectory(Craft::getAlias('@webroot/uploads/'.$handle));
        $fs = new craft\fs\Local(['name' => $handle, 'handle' => $handle, 'path' => '@webroot/uploads/'.$handle, 'hasUrls' => false]);
        if (!Craft::$app->fs->saveFilesystem($fs)) throw new RuntimeException(json_encode($fs->getErrors()));
        $source = new craft\models\Volume(['name' => $handle, 'handle' => $handle, 'fsHandle' => $handle]);
        expect(Craft::$app->volumes->saveVolume($source))->toBeTrue();
        $data = ['dynamicSource' => 'assetVolume', 'volumeId' => $source->id];
        $permission = 'viewAssets:'.$source->uid;
    }
    $node = new Node(['menuId' => $menu->id, 'siteId' => Craft::$app->sites->getPrimarySite()->id, 'type' => verbb\navigation\nodetypes\Dynamic::class, 'data' => $data]);
    boundaryRequest(function() use ($menu, $node, $permission) {
        $editor = new craft\elements\User(['username' => uniqid('sourceEditor'), 'email' => uniqid('sourceEditor').'@example.test']);
        expect(Craft::$app->elements->saveElement($editor))->toBeTrue();
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->userPermissions->saveUserPermissions($editor->id, ['navigation-manageMenu:'.$menu->uid]);
        expect(verbb\navigation\helpers\MenuAuth::canAuthorNode($editor, $node))->toBeFalse();
        Craft::$app->userPermissions->saveUserPermissions($editor->id, ['navigation-manageMenu:'.$menu->uid, $permission]);
        expect($editor->can($permission))->toBeTrue();
        expect(verbb\navigation\helpers\MenuAuth::canAuthorNode($editor, $node))->toBeTrue();
    });
})->with(['category', 'asset']);
