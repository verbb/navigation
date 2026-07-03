<?php

declare(strict_types=1);

use craft\db\Query;
use Tests\Support\ResetTestDatabase;

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Support/ResetTestDatabase.php';

try {
    $app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/console.php';
} catch (Throwable $e) {
    throw new RuntimeException(
        'Craft bootstrap failed for tests. Run `composer test:setup` and verify plugin `.env.testing` CRAFT_DB_* values.',
        0,
        $e
    );
}

if (!class_exists(Craft::class) || !Craft::$app) {
    throw new RuntimeException('Craft application failed to bootstrap for integration tests.');
}

$environment = getenv('ENVIRONMENT') ?: '';
if ($environment !== 'testing') {
    throw new RuntimeException('Refusing to run tests outside ENVIRONMENT=testing. Configure phpunit.craft.xml/.env.testing.');
}

$db = Craft::$app->getDb();
if (!$db->tableExists('{{%plugins}}')) {
    throw new RuntimeException(
        'Testing database is not installed yet. Run Craft install/migrations against .env.testing first, then re-run tests.'
    );
}

$projectConfig = Craft::$app->getProjectConfig();
$pluginRows = (new Query())
    ->select(['handle', 'schemaVersion'])
    ->from('{{%plugins}}')
    ->where(['handle' => ['navigation']])
    ->all();

foreach ($pluginRows as $pluginRow) {
    $handle = (string)($pluginRow['handle'] ?? '');
    if (!$handle) {
        continue;
    }

    $key = 'plugins.' . $handle;
    $pluginConfig = $projectConfig->get($key);

    if (!$pluginConfig || empty($pluginConfig['enabled'])) {
        $projectConfig->set($key, [
            ...($pluginConfig ?: []),
            'edition' => 'standard',
            'enabled' => true,
            'schemaVersion' => (string)($pluginConfig['schemaVersion'] ?? $pluginRow['schemaVersion'] ?? ''),
        ]);
    }
}

// Craft can load the plugin service before isolated project-config plugin keys
// exist, so refresh the plugin service after hydrating them.
$pluginsReflection = new ReflectionClass(Craft::$app->plugins);
foreach ([
    '_pluginsLoaded' => false,
    '_loadingPlugins' => false,
    '_plugins' => [],
] as $propertyName => $value) {
    if (!$pluginsReflection->hasProperty($propertyName)) {
        continue;
    }

    $property = $pluginsReflection->getProperty($propertyName);
    $property->setAccessible(true);
    $property->setValue(Craft::$app->plugins, $value);
}

$plugins = Craft::$app->plugins;

if (!$plugins->isPluginInstalled('navigation')) {
    $plugins->installPlugin('navigation');
} elseif (!$plugins->isPluginEnabled('navigation')) {
    $plugins->enablePlugin('navigation');
}

$navigation = $plugins->getPlugin('navigation');
if (!$navigation) {
    throw new RuntimeException('Navigation plugin failed to load for integration tests.');
}

$readOnly = $projectConfig->readOnly;
$projectConfig->readOnly = false;

try {
    $migrator = $navigation->getMigrator();

    foreach ($migrator->getNewMigrations() as $migration) {
        $migrator->migrateUp($migration);
    }

    if ($plugins->isPluginUpdatePending($navigation)) {
        $plugins->updatePluginVersionInfo($navigation);
    }
} finally {
    $projectConfig->readOnly = $readOnly;
}

ResetTestDatabase::resetNavigationData();
