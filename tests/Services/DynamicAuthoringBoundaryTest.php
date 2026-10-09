<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Entry as EntryNodeType;

it('applies node authoring policy to native duplication', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Existing link', '/existing-link');
    $menu->permissions = [Custom::class => ['enabled' => false]];
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    boundaryRequest(function() use ($menu, $node) {
        $editor = new craft\elements\User(['username' => uniqid('duplicateEditor'), 'email' => uniqid('duplicateEditor') . '@example.test']);
        expect(Craft::$app->elements->saveElement($editor))->toBeTrue();
        Craft::$app->userPermissions->saveUserPermissions($editor->id, ['navigation-manageMenu:' . $menu->uid]);

        expect($node->canDuplicate($editor))->toBeFalse();
    });
});

it('applies Craft site authorization to linked elements', function() {
    $secondarySite = F::secondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $menu = F::menu();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => $primarySite->id,
        'type' => EntryNodeType::class,
        'elementId' => $entry->id,
    ]);
    $node->setElementSiteId($secondarySite->id);

    boundaryRequest(function() use ($menu, $node, $primarySite, $secondarySite, $section) {
        $editor = new class extends craft\elements\User {
            public array $grants = [];

            public function can(string $permission): bool
            {
                return in_array($permission, $this->grants, true);
            }
        };
        $editor->id = Craft::$app->getUser()->id;
        $editor->grants = [
            'navigation-manageMenu:' . $menu->uid,
            'editSite:' . $primarySite->uid,
            'viewEntries:' . $section->uid,
            'viewPeerEntries:' . $section->uid,
        ];
        Craft::$app->getUser()->setIdentity($editor);

        $linkedElement = $node->getElement();
        expect($linkedElement->siteId)->toBe($secondarySite->id)
            ->and($linkedElement->canView($editor))->toBeTrue()
            ->and(Craft::$app->getElements()->canView($linkedElement, $editor))->toBeFalse()
            ->and(verbb\navigation\helpers\MenuAuth::canAuthorNode($editor, $node))->toBeFalse();
    });
});

it('authorizes linked element refreshes against selection changes', function() {
    $menu = F::menu();
    $section = F::entrySection();
    [$original, $replacement] = F::entries(2, $section);
    $node = F::entryNode($menu, $original);
    $site = Craft::$app->getSites()->getSiteById($node->siteId);

    boundaryRequest(function() use ($menu, $section, $original, $replacement, $node, $site) {
        $editor = new craft\elements\User([
            'username' => uniqid('linkedRefreshEditor'),
            'email' => uniqid('linkedRefreshEditor') . '@example.test',
        ]);
        expect(Craft::$app->getElements()->saveElement($editor))->toBeTrue();
        Craft::$app->getUserPermissions()->saveUserPermissions($editor->id, [
            'accessCp',
            'navigation-manageMenu:' . $menu->uid,
            'editSite:' . $site->uid,
        ]);
        Craft::$app->getUser()->setIdentity($editor);

        expect($editor->can('viewEntries:' . $section->uid))->toBeFalse()
            ->and(verbb\navigation\helpers\MenuAuth::canUseLinkedElement($editor, $node))->toBeFalse()
            ->and(verbb\navigation\helpers\MenuAuth::linkedElementSelectionIsUnchanged($node))->toBeTrue();

        $field = new verbb\navigation\fieldlayoutelements\NodeTypeElements();
        expect($field->formHtml($node))->toContain($original->title);

        $node->setLinkedElementId($replacement->id);
        $node->setElementSiteId($replacement->siteId);

        expect(verbb\navigation\helpers\MenuAuth::canUseLinkedElement($editor, $node))->toBeFalse()
            ->and(verbb\navigation\helpers\MenuAuth::linkedElementSelectionIsUnchanged($node))->toBeFalse()
            ->and($field->formHtml($node))->not->toContain($replacement->title);
    });
});

it('adds a dynamic entry section below an entry node when the editor can view it', function() {
    $menu = F::menu(); $section = F::entrySection();
    $parent = F::entryNode($menu, F::entries(1, $section)[0]);
    $menu->permissions = [verbb\navigation\nodetypes\Dynamic::class => ['enabled' => true]];
    N::$plugin->getMenus()->saveMenu($menu);
    boundaryRequest(function() use ($menu, $parent, $section) {
        $editor = new craft\elements\User(['username' => uniqid('dynamicBuilder'), 'email' => uniqid('dynamicBuilder').'@example.test']);
        expect(Craft::$app->elements->saveElement($editor))->toBeTrue();
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->userPermissions->saveUserPermissions($editor->id, [
            'accessCp',
            'navigation-manageMenu:'.$menu->uid,
            'viewEntries:'.$section->uid,
        ]);
        Craft::$app->user->setIdentity($editor);
        Craft::$app->request->setBodyParams(['nodes' => [[
            'menuId' => $menu->id, 'siteId' => Craft::$app->sites->getPrimarySite()->id,
            'type' => verbb\navigation\nodetypes\Dynamic::class, 'title' => 'Dynamic section',
            'parentId' => $parent->id,
            'data' => ['dynamicSource' => 'entrySection', 'sectionId' => $section->id],
        ]]]);

        $controller = new verbb\navigation\controllers\NodesController('nodes', N::$plugin);
        $controller->actionAddNodes();

        $node = Node::find()
            ->menuId($menu->id)
            ->type(verbb\navigation\nodetypes\Dynamic::class)
            ->status(null)
            ->one();
        expect($node)->not->toBeNull()
            ->and($node->data['dynamicSource'])->toBe('entrySection')
            ->and((int)$node->data['sectionId'])->toBe((int)$section->id)
            ->and((int)$node->getParent()?->id)->toBe((int)$parent->id);
    });
});

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
