<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\controllers\MenusController;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\services\Menus;
use verbb\navigation\Navigation;

function menuSettingsPayload(MenuSettings $menu, array $sites): array
{
    return [
        'menuId' => $menu->id,
        'name' => $menu->name,
        'handle' => $menu->handle,
        'instructions' => $menu->instructions,
        'propagationMethod' => $menu->propagationMethod,
        'titleTranslationMethod' => $menu->titleTranslationMethod,
        'defaultEnabledForPropagatedSites' => $menu->defaultEnabledForPropagatedSites,
        'maxLevels' => $menu->maxLevels,
        'maxNodes' => $menu->maxNodes,
        'maxNodesSettings' => $menu->maxNodesSettings,
        'permissions' => $menu->permissions,
        'defaultPlacement' => $menu->defaultPlacement,
        'showSiteMenu' => $menu->showSiteMenu,
        'sites' => $sites,
    ];
}

function menuSettingsController(): MenusController
{
    return new class('menus', Navigation::$plugin) extends MenusController {
        public function setSuccessFlash(?string $default = null, array $settings = []): void
        {
        }

        public function redirectToPostedUrl(?object $object = null, ?string $default = null): yii\web\Response
        {
            return Craft::$app->getResponse();
        }
    };
}

it('preserves site settings outside the editors site permissions', function() {
    $secondary = NavigationFixtureFactory::secondarySite();
    $primary = Craft::$app->getSites()->getPrimarySite();
    $menu = NavigationFixtureFactory::menu();
    $siteSettings = $menu->getSiteSettings();
    $siteSettings[$secondary->id]->enabled = false;
    $menu->setSiteSettings($siteSettings);
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    boundaryRequest(function() use ($menu, $primary, $secondary) {
        $editor = new craft\elements\User([
            'username' => uniqid('menuSiteEditor'),
            'email' => uniqid('menuSiteEditor') . '@example.test',
        ]);
        expect(Craft::$app->getElements()->saveElement($editor))->toBeTrue();
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->getUserPermissions()->saveUserPermissions($editor->id, [
            'navigation-manageMenu:' . $menu->uid,
            'navigation-editMenu:' . $menu->uid,
            'editSite:' . $primary->uid,
        ]);
        Craft::$app->getUser()->setIdentity($editor);
        Craft::$app->getRequest()->setBodyParams(menuSettingsPayload($menu, [
            $primary->handle => ['enabled' => true],
            $secondary->handle => ['enabled' => true],
        ]));

        menuSettingsController()->actionSaveMenu();
    });

    Navigation::$plugin->getMenus()->resetCache();
    $saved = Navigation::$plugin->getMenus()->getMenuById((int)$menu->id);

    expect($saved->getSiteSettings()[$primary->id]->enabled)->toBeTrue()
        ->and($saved->getSiteSettings()[$secondary->id]->enabled)->toBeFalse();
});

it('does not accept menu content fields through the settings action', function() {
    $menu = NavigationFixtureFactory::menu();
    $originalMenus = Navigation::$plugin->getMenus();
    $menus = new class extends Menus {
        public bool $requestContentSaveCalled = false;

        public function saveMenuContentFromRequest(int $menuId, ?int $siteId = null): bool
        {
            $this->requestContentSaveCalled = true;

            return parent::saveMenuContentFromRequest($menuId, $siteId);
        }
    };
    Navigation::$plugin->set('menus', $menus);

    try {
        boundaryRequest(function() use ($menu) {
            $payload = menuSettingsPayload($menu, []);
            $payload['fieldsLocation'] = 'fields';
            $payload['siteId'] = Craft::$app->getSites()->getPrimarySite()->id;
            $payload['fields'] = ['forged' => 'content'];
            Craft::$app->getRequest()->setBodyParams($payload);

            menuSettingsController()->actionSaveMenu();
        });

        expect($menus->requestContentSaveCalled)->toBeFalse();
    } finally {
        Navigation::$plugin->set('menus', $originalMenus);
    }
});

it('keeps new menus disabled on sites the creator cannot edit', function() {
    $secondary = NavigationFixtureFactory::secondarySite();
    $primary = Craft::$app->getSites()->getPrimarySite();
    $handle = 'siteScoped' . uniqid();

    boundaryRequest(function() use ($handle, $primary, $secondary) {
        $editor = new craft\elements\User([
            'username' => uniqid('menuCreator'),
            'email' => uniqid('menuCreator') . '@example.test',
        ]);
        expect(Craft::$app->getElements()->saveElement($editor))->toBeTrue();
        Craft::$app->set('userPermissions', new craft\services\UserPermissions());
        Craft::$app->getUserPermissions()->saveUserPermissions($editor->id, [
            'navigation-createMenus',
            'editSite:' . $primary->uid,
        ]);
        Craft::$app->getUser()->setIdentity($editor);
        Craft::$app->getRequest()->setBodyParams([
            'name' => 'Site Scoped',
            'handle' => $handle,
            'propagationMethod' => MenuSettings::PROPAGATION_METHOD_ALL,
            'titleTranslationMethod' => craft\base\Field::TRANSLATION_METHOD_SITE,
            'defaultEnabledForPropagatedSites' => true,
            'maxNodesSettings' => [],
            'permissions' => [],
            'defaultPlacement' => MenuSettings::DEFAULT_PLACEMENT_END,
            'showSiteMenu' => true,
            'sites' => [
                $primary->handle => ['enabled' => true],
                $secondary->handle => ['enabled' => true],
            ],
        ]);

        menuSettingsController()->actionSaveMenu();
    });

    Navigation::$plugin->getMenus()->resetCache();
    $saved = Navigation::$plugin->getMenus()->getMenuByHandle($handle);

    expect($saved)->not->toBeNull()
        ->and($saved->getSiteSettings()[$primary->id]->enabled)->toBeTrue()
        ->and($saved->getSiteSettings()[$secondary->id]->enabled)->toBeFalse();
});
