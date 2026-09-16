<?php

use verbb\navigation\Navigation;
use verbb\navigation\controllers\ImportExportController;

it('returns a recoverable error for malformed import JSON', function(string $contents, string $action) {
    boundaryRequest(function() use ($contents, $action) {
        $_SERVER['REQUEST_METHOD'] = $action === 'import-configure' ? 'GET' : 'POST';
        Craft::$app->request->cookieValidationKey = 'isolated-import-invalid-test';
        $filename = 'navigation-import-991229_235954.json';
        $path = Craft::$app->path->getTempPath() . '/' . $filename;
        if (file_exists($path)) throw new RuntimeException('Test import path already exists');
        file_put_contents($path, $contents);
        Craft::$app->request->setBodyParams(['filename' => $filename]);
        $controller = new class('import-export', Navigation::$plugin) extends ImportExportController {
            public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): yii\web\Response {
                return $this->asRaw($variables['importError'] ?? '');
            }
        };
        try {
            $controller->enableCsrfValidation = false;
            $response = $controller->runAction($action, $action === 'import-configure' ? ['filename' => $filename] : []);
            expect($response->data)->toContain('Invalid JSON');
        } finally {
            unlink($path);
        }
    });
})->with([
    'invalid syntax' => ['{invalid'],
    'scalar JSON' => ['42'],
    'invalid children' => ['{"menu":{"handle":"invalid"},"nodes":[{"children":"bad"}]}'],
    'invalid metadata' => ['{"menu":{"handle":[]},"nodes":[]}'],
])->with(['import-configure', 'import-complete']);
