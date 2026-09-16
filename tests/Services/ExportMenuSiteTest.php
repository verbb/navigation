<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\controllers\ImportExportController;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\models\MenuSettings;

it('exports a menu whose only enabled site is secondary', function() {
    $site = F::secondarySite();
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    $settings = $menu->getSiteSettings();
    $settings[Craft::$app->sites->getPrimarySite()->id]->enabled = false;
    $menu->setSiteSettings($settings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $node = F::customNode($menu, 'Secondary menu link', '/secondary-only', null, $site->id);
    $menu = N::$plugin->getMenus()->getMenuById($menu->id);
    $payload = ImportExportHelper::generateMenuExport($menu);
    expect($payload['sourceSiteHandle'])->toBe($site->handle);
    expect(array_column($payload['nodes'], 'title'))->toBe([$node->title]);
    $result = ImportExportHelper::importMenuFromJson($payload);
    expect($result->errors)->toBe([]);
    expect(Node::find()->menuId($result->menu->id)->siteId($site->id)->one()?->getUrl())->toBe('/secondary-only');
});

it('offers secondary-only menus in the control panel export selector', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $settings = $menu->getSiteSettings();
    $settings[Craft::$app->sites->getPrimarySite()->id]->enabled = false;
    $menu->setSiteSettings($settings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    boundaryRequest(function() use ($menu) {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Craft::$app->request->cookieValidationKey = 'isolated-export-options';
        $controller = new class('import-export', N::$plugin) extends ImportExportController {
            public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): yii\web\Response {
                $source = file_get_contents(dirname(__DIR__, 2) . '/src/templates/settings/import-export/index.html');
                // Render the real form and macros without unrelated CP layout requirements.
                $source = str_replace("{% extends 'navigation/_layouts/settings' %}", '', $source);
                return $this->asRaw(Craft::$app->view->getTwig()->createTemplate($source)->render($variables));
            }
        };
        $html = $controller->runAction('index')->data;
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $options = (new DOMXPath($dom))->query('//select[@name="menuId"]/option[@value="' . $menu->id . '"]');
        expect($options->length)->toBe(1);
    });
});

it('preserves independent site branches when updating a menu from its default export', function() {
    $site = F::secondarySite();
    $primary = Craft::$app->sites->getPrimarySite();
    $menu = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    F::customNode($menu, 'Primary branch', '/primary', null, $primary->id);
    $parent = F::customNode($menu, 'Secondary branch', '/secondary', null, $site->id);
    F::customNode($menu, 'Secondary child', '/secondary/child', $parent, $site->id);
    $payload = ImportExportHelper::generateMenuExport($menu);
    $result = ImportExportHelper::importMenuFromJson($payload, 'update');
    expect($result->errors)->toBe([]);
    expect(array_column(Node::find()->menuId($menu->id)->siteId($primary->id)->all(), 'title'))->toBe(['Primary branch']);
    $secondary = Node::find()->menuId($menu->id)->siteId($site->id)->all();
    expect(array_column($secondary, 'title'))->toBe(['Secondary branch', 'Secondary child']);
    expect($secondary[1]->getParentId())->toBe($secondary[0]->id);
    expect($secondary[1]->getUrl())->toBe('/secondary/child');
});

it('exports shared nodes once and keeps explicit site exports scoped', function() {
    $site = F::secondarySite();
    $shared = F::menu();
    $parent = F::customNode($shared, 'Shared parent', '/shared');
    F::customNode($shared, 'Shared child', '/shared/child', $parent);
    $payload = ImportExportHelper::generateMenuExport($shared);
    expect($payload['nodes'])->toHaveCount(1);
    expect($payload['nodes'][0]['children'])->toHaveCount(1);
    $result = ImportExportHelper::importMenuFromJson($payload);
    expect($result->errors)->toBe([]);
    expect($result->nodesCreated)->toBe(2);
    expect(Node::find()->menuId($result->menu->id)->siteId($site->id)->all())->toHaveCount(2);

    $independent = F::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);
    F::customNode($independent, 'Primary only', '/primary');
    F::customNode($independent, 'Secondary only', '/secondary', null, $site->id);
    $scoped = ImportExportHelper::generateMenuExport($independent, $site->id);
    expect(array_column($scoped['nodes'], 'title'))->toBe(['Secondary only']);
});
