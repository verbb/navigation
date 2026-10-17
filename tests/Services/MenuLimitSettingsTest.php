<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\controllers\MenusController;
use verbb\navigation\Navigation;

it('rejects invalid menu limits without changing saved settings', function(string $attribute, mixed $value) {
    $menu = NavigationFixtureFactory::menu();
    $menu->maxLevels = 3;
    $menu->maxNodes = 10;
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    boundaryRequest(function() use ($menu, $attribute, $value) {
        $payload = $menu->getAttributes();
        $payload['menuId'] = $menu->id;
        $payload[$attribute] = $value;
        Craft::$app->getRequest()->setBodyParams($payload);
        $controller = new class('menus', Navigation::$plugin) extends MenusController {
            public function setFailFlash(?string $default = null, array $settings = []): void {}
            public function setSuccessFlash(?string $default = null, array $settings = []): void {}
            public function redirectToPostedUrl(?object $object = null, ?string $default = null): yii\web\Response
            {
                return Craft::$app->getResponse();
            }
        };

        expect($controller->actionSaveMenu())->toBeNull();
        $params = Craft::$app->getUrlManager()->getRouteParams();
        expect($params['nav']->hasErrors($attribute))->toBeTrue()
            ->and($params['limitValues'][$attribute])->toBe(is_scalar($value) ? $value : '');
    });

    Navigation::$plugin->getMenus()->resetCache();
    $saved = Navigation::$plugin->getMenus()->getMenuById($menu->id);
    expect($saved->maxLevels)->toBe(3)->and($saved->maxNodes)->toBe(10);
})->with(['maxLevels', 'maxNodes'])->with(['invalid', '-1', '1.5', '2abc', '999999999999999999999', [['invalid']]]);

it('validates negative menu limits supplied by integrations', function(string $attribute) {
    $menu = NavigationFixtureFactory::menu();
    $menu->$attribute = -1;
    expect($menu->validate([$attribute]))->toBeFalse()
        ->and($menu->hasErrors($attribute))->toBeTrue();
})->with(['maxLevels', 'maxNodes']);

it('saves valid menu limits and preserves unlimited inputs', function(mixed $value, ?int $expected) {
    $menu = NavigationFixtureFactory::menu();

    boundaryRequest(function() use ($menu, $value) {
        $payload = $menu->getAttributes();
        $payload['menuId'] = $menu->id;
        $payload['maxLevels'] = $value;
        $payload['maxNodes'] = $value;
        Craft::$app->getRequest()->setBodyParams($payload);
        $controller = new class('menus', Navigation::$plugin) extends MenusController {
            public function setSuccessFlash(?string $default = null, array $settings = []): void {}
            public function redirectToPostedUrl(?object $object = null, ?string $default = null): yii\web\Response
            {
                return Craft::$app->getResponse();
            }
        };
        expect($controller->actionSaveMenu())->toBeInstanceOf(yii\web\Response::class);
    });

    Navigation::$plugin->getMenus()->resetCache();
    $saved = Navigation::$plugin->getMenus()->getMenuById($menu->id);
    expect($saved->maxLevels)->toBe($expected)->and($saved->maxNodes)->toBe($expected);
})->with([['3', 3], ['', null], ['0', null], [null, null]]);
