<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\controllers\MenusController;
use verbb\navigation\services\Menus;
use verbb\navigation\Navigation;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

it('reorders menus through complete project config payloads', function() {
    $first = NavigationFixtureFactory::menu();
    $second = NavigationFixtureFactory::menu();
    $menus = Navigation::$plugin->getMenus();

    expect($menus->reorderMenus([$second->id, $first->id]))->toBeTrue();

    $menus->resetCache();
    $first = $menus->getMenuById($first->id);
    $second = $menus->getMenuById($second->id);

    expect($second->sortOrder)->toBe(1)
        ->and($first->sortOrder)->toBe(2);

    $projectConfig = Craft::$app->getProjectConfig();
    $firstConfig = $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $first->uid);
    $secondConfig = $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $second->uid);

    expect($secondConfig['sortOrder'])->toBe(1)
        ->and($firstConfig['sortOrder'])->toBe(2)
        ->and($firstConfig)->toHaveKeys(['name', 'handle', 'structure', 'siteSettings'])
        ->and($secondConfig)->toHaveKeys(['name', 'handle', 'structure', 'siteSettings']);
});

it('requires edit permission for every menu before reordering any of them', function() {
    $first = NavigationFixtureFactory::menu();
    $second = NavigationFixtureFactory::menu();
    $menus = Navigation::$plugin->getMenus();
    $projectConfig = Craft::$app->getProjectConfig();
    $before = [
        $first->uid => $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $first->uid),
        $second->uid => $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $second->uid),
    ];

    boundaryRequest(function() use ($first, $second, $menus, $projectConfig, $before) {
        $user = new craft\elements\User([
            'username' => uniqid('menuReorder'),
            'email' => uniqid('menuReorder') . '@example.test',
        ]);
        expect(Craft::$app->getElements()->saveElement($user))->toBeTrue();
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->getUserPermissions()->saveUserPermissions($user->id, [
            'navigation-createMenus',
            'navigation-manageMenu:' . $first->uid,
            'navigation-editMenu:' . $first->uid,
        ]);
        Craft::$app->getUser()->setIdentity($user);
        Craft::$app->getRequest()->setBodyParams([
            'ids' => craft\helpers\Json::encode([$second->id, $first->id]),
        ]);

        expect(fn() => (new MenusController('menus', Navigation::$plugin))->actionReorderMenu())
            ->toThrow(ForbiddenHttpException::class);

        $menus->resetCache();
        expect($projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $first->uid))->toBe($before[$first->uid])
            ->and($projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $second->uid))->toBe($before[$second->uid]);
    });
});

it('allows reordering Craft AdminTable string IDs when the user can edit every submitted menu', function() {
    $first = NavigationFixtureFactory::menu();
    $second = NavigationFixtureFactory::menu();

    boundaryRequest(function() use ($first, $second) {
        $user = new craft\elements\User([
            'username' => uniqid('menuReorder'),
            'email' => uniqid('menuReorder') . '@example.test',
        ]);
        expect(Craft::$app->getElements()->saveElement($user))->toBeTrue();
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->getUserPermissions()->saveUserPermissions($user->id, [
            'navigation-manageMenu:' . $first->uid,
            'navigation-editMenu:' . $first->uid,
            'navigation-manageMenu:' . $second->uid,
            'navigation-editMenu:' . $second->uid,
        ]);
        Craft::$app->getUser()->setIdentity($user);
        Craft::$app->getRequest()->setBodyParams([
            'ids' => craft\helpers\Json::encode([(string)$second->id, (string)$first->id]),
        ]);

        $response = (new MenusController('menus', Navigation::$plugin))->actionReorderMenu();
        expect($response->getStatusCode())->toBe(200);
    });

    $menus = Navigation::$plugin->getMenus();
    $menus->resetCache();
    expect($menus->getMenuById($second->id)->sortOrder)->toBe(1)
        ->and($menus->getMenuById($first->id)->sortOrder)->toBe(2);
});

it('rejects malformed incomplete or duplicate reorder payloads before writing', function(array $ids) {
    $first = NavigationFixtureFactory::menu();
    $second = NavigationFixtureFactory::menu();
    $projectConfig = Craft::$app->getProjectConfig();
    $before = [
        $first->uid => $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $first->uid),
        $second->uid => $projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $second->uid),
    ];

    boundaryRequest(function() use ($first, $second, $projectConfig, $before, $ids) {
        Craft::$app->getRequest()->setBodyParams(['ids' => craft\helpers\Json::encode($ids)]);

        expect(fn() => (new MenusController('menus', Navigation::$plugin))->actionReorderMenu())
            ->toThrow(BadRequestHttpException::class);
        expect($projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $first->uid))->toBe($before[$first->uid])
            ->and($projectConfig->get(Menus::CONFIG_MENU_KEY . '.' . $second->uid))->toBe($before[$second->uid]);
    });
})->with([
    'empty list' => [[]],
    'duplicate IDs' => [[1, 1]],
    'non-integer ID' => [['not-an-id']],
    'boolean ID' => [[true]],
    'decimal ID' => [[1.5]],
    'unknown ID' => [[PHP_INT_MAX]],
]);
