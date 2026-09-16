<?php

use craft\elements\User;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\controllers\BuildSessionsController;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation as N;
use verbb\navigation\nodetypes\Custom;
use yii\web\ForbiddenHttpException;

it('rechecks pending additions before publishing any session changes', function(string $restriction) {
    $menu = F::menu();
    $entry = $restriction === 'missing-source' ? F::entries(1)[0] : null;
    $allowed = F::passiveNode($menu, 'Still allowed');
    $node = $entry ? F::entryNode($menu, $entry) : F::customNode($menu, 'Pending authorization check', '/pending-authorization');
    $deleted = F::passiveNode($menu, 'Pending removal');
    $owner = new User(['username' => uniqid('publish-owner-'), 'email' => uniqid('publish-owner-') . '@example.test']);
    expect(Craft::$app->getElements()->saveElement($owner))->toBeTrue();

    boundaryRequest(function() use ($menu, $allowed, $node, $deleted, $owner, $entry) {
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        $site = Craft::$app->getSites()->getSiteById($node->siteId);
        $permissions = ['accessCp', 'editSite:' . $site->uid, 'navigation-manageMenu:' . $menu->uid];
        if ($entry) {
            $permissions[] = 'viewEntries:' . $entry->getSection()->uid;
            $permissions[] = 'viewPeerEntries:' . $entry->getSection()->uid;
        }
        expect(Craft::$app->getUserPermissions()->saveUserPermissions($owner->id, $permissions))->toBeTrue();
        Craft::$app->getUser()->setIdentity($owner);
        expect($node->canSave($owner))->toBeTrue();
        $sessions = N::$plugin->getBuildSessions();
        $session = $sessions->getOrCreate($menu->id, $node->siteId);
        foreach ([$allowed, $node] as $addition) {
            $addition->enabled = false;
            $addition->setPendingPublish(true);
            expect(Craft::$app->getElements()->saveElement($addition))->toBeTrue();
        }
        $sessions->stageAddedNodes($session, [$allowed->id, $node->id]);
        $sessions->stageDelete($session, $deleted);

        // Changes made elsewhere after the editor staged these nodes.
        if ($entry) {
            expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
        } else {
            $menu->permissions = [Custom::class => ['enabled' => false]];
            expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
        }
        $current = Node::find()->id($node->id)->status(null)->one();
        expect($current->canSave($owner))->toBeFalse();
        Craft::$app->getRequest()->setBodyParams(['menuId' => $menu->id, 'siteId' => $node->siteId]);
        $controller = new BuildSessionsController('build-sessions', N::$plugin);
        expect(fn() => $controller->actionPublish())->toThrow(ForbiddenHttpException::class);

        foreach ([$allowed, $node] as $addition) {
            $unchanged = Node::find()->id($addition->id)->status(null)->one();
            expect($unchanged->getIsPendingPublish())->toBeTrue();
            expect($unchanged->enabled)->toBeFalse();
        }
        expect(Node::find()->id($deleted->id)->status(null)->one()->getIsPendingDelete())->toBeTrue();
        expect($sessions->getSession($menu->id, $node->siteId))->not->toBeNull();

        if ($entry) {
            expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
        } else {
            $menu->permissions = [];
            expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
        }
        $response = $controller->actionPublish();
        expect($response->statusCode)->toBe(200);
        expect($response->data['publishedCount'])->toBe(2);
        expect($response->data['deletedCount'])->toBe(1);
        expect(Node::find()->id([$allowed->id, $node->id])->all())->toHaveCount(2);
        expect(Node::find()->id($deleted->id)->status(null)->one())->toBeNull();
        expect($sessions->getSession($menu->id, $node->siteId))->toBeNull();
    });
})->with(['disabled-type', 'missing-source']);

it('still lets an editor remove a previously staged node when its authoring type is disabled', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Remove disabled type', '/remove-disabled');
    boundaryRequest(function() use ($menu, $node) {
        $sessions = N::$plugin->getBuildSessions();
        $session = $sessions->getOrCreate($menu->id, $node->siteId);
        $sessions->stageDelete($session, $node);
        $menu->permissions = [Custom::class => ['enabled' => false]];
        expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
        Craft::$app->getRequest()->setBodyParams(['menuId' => $menu->id, 'siteId' => $node->siteId]);
        $controller = new BuildSessionsController('build-sessions', N::$plugin);
        $response = $controller->actionPublish();
        expect($response->statusCode)->toBe(200);
        expect($response->data['deletedCount'])->toBe(1);
        expect(Node::find()->id($node->id)->status(null)->one())->toBeNull();
    });
});
