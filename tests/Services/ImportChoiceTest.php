<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation as N;
use verbb\navigation\controllers\ImportExportController;
use verbb\navigation\helpers\ImportExportHelper;

it('requires an explicit duplicate-menu choice for wrapped imports', function() {
    $menu = F::menu();
    F::customNode($menu, 'Original', '/original');
    $payload = [ImportExportHelper::generateMenuExport($menu)];
    boundaryRequest(function() use ($menu, $payload) {
        $filename = 'navigation-import-991229_235955.json';
        $path = Craft::$app->path->getTempPath() . '/' . $filename;
        if (file_exists($path)) throw new RuntimeException('Test import path already exists');
        file_put_contents($path, json_encode($payload));
        $before = count(N::$plugin->getMenus()->getAllMenus());
        Craft::$app->request->setBodyParams(['filename' => $filename, 'menuAction' => '']);
        $controller = new class('import-export', N::$plugin) extends ImportExportController {
            // Flash transport requires a web app; keep real controller and persistence logic.
            public function setSuccessFlash(?string $default = null, array $settings = []): void {}
            public function setFailFlash(?string $default = null, array $settings = []): void {}
            public function redirectToPostedUrl(mixed $object = null, ?string $default = null): yii\web\Response {
                return $this->asRaw('imported');
            }
        };
        try {
            $controller->enableCsrfValidation = false;
            $response = $controller->runAction('import-complete');
            expect(count(N::$plugin->getMenus()->getAllMenus()))->toBe($before);
            expect($response->getStatusCode())->toBe(302);
            expect($response->headers->get('Location'))->toContain('import-configure/' . $filename);
            expect(N::$plugin->getMenus()->getMenuById($menu->id)->handle)->toBe($menu->handle);
        } finally {
            unlink($path);
        }
    });
});
