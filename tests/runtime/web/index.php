<?php

// Serve only the repository-owned disposable app, after provisioning has finished.
$runtime = dirname(__DIR__, 3) . '/.cache/verbb-tests';
$lock = @fopen($runtime . '/run.lock', 'r');
if (!$lock || !flock($lock, LOCK_SH | LOCK_NB)) {
    http_response_code(503);
    exit('Run ddev test and wait for it to finish before opening the test app.');
}

require dirname(__DIR__) . '/bootstrap.php';
$app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/web.php';
// Pest fixtures may leave jobs for elements they intentionally deleted.
$app->getConfig()->getGeneral()->runQueueAutomatically = false;
// Publish CP resources into DDEV's actual document root.
Craft::setAlias('@webroot', __DIR__);
\craft\helpers\FileHelper::createDirectory(__DIR__ . '/cpresources');
$app->getAssetManager()->basePath = __DIR__ . '/cpresources';
$app->getAssetManager()->baseUrl = '/cpresources';
$app->run();
