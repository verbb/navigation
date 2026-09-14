<?php

use verbb\navigation\Navigation as N;
use verbb\navigation\controllers\ImportExportController;

it('renders uploaded metadata as literal text in the import preview', function(string $value) {
    boundaryRequest(function() use ($value) {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Craft::$app->request->cookieValidationKey = 'isolated-import-output-test';
        $filename = 'navigation-import-991229_235956.json';
        $path = Craft::$app->path->getTempPath() . '/' . $filename;
        if (file_exists($path)) throw new RuntimeException('Test import path already exists');
        file_put_contents($path, json_encode(['menu' => ['name' => $value, 'handle' => $value],
            'nodes' => [['title' => $value, 'type' => $value]]]));
        try {
            $controller = new class('import-export', N::$plugin) extends ImportExportController {
                public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): yii\web\Response {
                    return $this->asRaw($variables['summary']);
                }
            };
            $summary = $controller->runAction('import-configure', ['filename' => $filename])->data;
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $summary);
            expect((new DOMXPath($dom))->query('//img | //script | //a | //*[@onerror]')->length)->toBe(0);
            expect($dom->textContent)->toContain($value);
            expect($summary)->toContain('Menu contains 1 node.');
        } finally {
            unlink($path);
        }
    });
})->with([
    '<img src="data:," onerror="document.documentElement.dataset.navigationAudit=1">',
    '[Open](javascript:alert(1)) ![Image](data:text/html,example)',
    'R&D <Brand> "Quotes" **Menu**',
]);

it('escapes the imported menu name while retaining the completion edit link', function() {
    boundaryRequest(function() {
        $name = '<img src="data:," onerror="document.documentElement.dataset.navigationAudit=1"> & "Menu"';
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/templates/settings/import-export/import-completed.html');
        $template = Craft::$app->view->getTwig()->createTemplate($source);
        $html = $template->renderBlock('blockContent', ['menu' => ['id' => 123, 'name' => $name]]);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        $xpath = new DOMXPath($dom);
        expect($xpath->query('//img | //script | //*[@onerror]')->length)->toBe(0);
        expect($dom->textContent)->toContain($name);
        expect($xpath->query('//a')->length)->toBe(1);
        expect($xpath->query('//a')->item(0)->getAttribute('href'))->toContain('navigation/menus/build/123');
    });
});
