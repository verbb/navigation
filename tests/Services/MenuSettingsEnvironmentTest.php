<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\controllers\MenusController;
use verbb\navigation\helpers\MenuAuth;
use verbb\navigation\Navigation;
use yii\web\ForbiddenHttpException;

it('keeps menu settings unavailable when administrative changes are disabled', function() {
    $menu = NavigationFixtureFactory::menu();
    $generalConfig = Craft::$app->getConfig()->getGeneral();
    $settings = Navigation::$plugin->getSettings();
    $previousAllowAdminChanges = $generalConfig->allowAdminChanges;
    $previousBypassProjectConfig = $settings->bypassProjectConfig;

    try {
        $generalConfig->allowAdminChanges = false;
        $settings->bypassProjectConfig = false;

        boundaryRequest(function() use ($menu) {
            $user = Craft::$app->getUser()->getIdentity();
            $state = Navigation::$plugin->getBuilderState()->getState(
                (int)$menu->id,
                (int)Craft::$app->getSites()->getPrimarySite()->id,
            );

            expect(MenuAuth::canEditMenu($user, $menu))->toBeTrue()
                ->and(MenuAuth::canEditMenuSettings($user, $menu))->toBeFalse()
                ->and($state['permissions']['canEditSettings'])->toBeFalse();

            expect(fn() => (new MenusController('menus', Navigation::$plugin))->actionEditMenu((int)$menu->id))
                ->toThrow(ForbiddenHttpException::class, 'Menu settings changes are disallowed in this environment.');

            Craft::$app->getRequest()->setBodyParams(['menuId' => $menu->id]);

            expect(fn() => (new MenusController('menus', Navigation::$plugin))->actionSaveMenu())
                ->toThrow(ForbiddenHttpException::class, 'Menu settings changes are disallowed in this environment.');
        });
    } finally {
        $generalConfig->allowAdminChanges = $previousAllowAdminChanges;
        $settings->bypassProjectConfig = $previousBypassProjectConfig;
    }
});

it('allows the project config bypass to keep menu settings editable', function() {
    $menu = NavigationFixtureFactory::menu();
    $generalConfig = Craft::$app->getConfig()->getGeneral();
    $settings = Navigation::$plugin->getSettings();
    $previousAllowAdminChanges = $generalConfig->allowAdminChanges;
    $previousBypassProjectConfig = $settings->bypassProjectConfig;

    try {
        $generalConfig->allowAdminChanges = false;
        $settings->bypassProjectConfig = true;

        boundaryRequest(function() use ($menu) {
            $user = Craft::$app->getUser()->getIdentity();
            $state = Navigation::$plugin->getBuilderState()->getState(
                (int)$menu->id,
                (int)Craft::$app->getSites()->getPrimarySite()->id,
            );

            expect(MenuAuth::canEditMenuSettings($user, $menu))->toBeTrue()
                ->and($state['permissions']['canEditSettings'])->toBeTrue();

            expect(fn() => MenuAuth::requireMenuSettingsChanges())->not->toThrow(ForbiddenHttpException::class);
        });
    } finally {
        $generalConfig->allowAdminChanges = $previousAllowAdminChanges;
        $settings->bypassProjectConfig = $previousBypassProjectConfig;
    }
});
