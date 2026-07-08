<?php
namespace verbb\navigation\console\controllers;

use verbb\navigation\helpers\MigrationRenderer;
use verbb\navigation\migrations\plugins\MigrateFromFreeNav;
use verbb\navigation\migrations\plugins\MigrateFromNavigate;
use verbb\navigation\migrations\plugins\MigrateFromNavkit;
use verbb\navigation\migrations\plugins\MigrateFromOlivemenus;
use verbb\navigation\migrations\plugins\MigrateFromTkaNavigation;
use verbb\navigation\Navigation;

use craft\console\Controller;
use craft\helpers\Console;

use yii\console\ExitCode;

/**
 * Migrates menus from third-party navigation plugins.
 */
class MigrateController extends Controller
{
    // Properties
    // =========================================================================

    public ?string $handle = null;
    public bool $skipExisting = false;


    // Public Methods
    // =========================================================================

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), [
            'handle',
            'skipExisting',
        ]);
    }

    public function optionAliases(): array
    {
        return array_merge(parent::optionAliases(), [
            'skip-existing' => 'skipExisting',
        ]);
    }

    public function actionFreeNav(): int
    {
        return $this->_run(MigrateFromFreeNav::class);
    }

    public function actionNavigate(): int
    {
        return $this->_run(MigrateFromNavigate::class);
    }

    public function actionNavkit(): int
    {
        return $this->_run(MigrateFromNavkit::class);
    }

    public function actionOlivemenus(): int
    {
        return $this->_run(MigrateFromOlivemenus::class);
    }

    public function actionTkaNavigation(): int
    {
        return $this->_run(MigrateFromTkaNavigation::class);
    }


    // Private Methods
    // =========================================================================

    private function _run(string $migratorClass): int
    {
        $this->stdout('Migrating from ' . $migratorClass::sourceLabel() . '…' . PHP_EOL, Console::FG_GREEN);

        /* @var MigrateFromFreeNav $migration */
        $migration = Navigation::$plugin->createMigrator($migratorClass, [
            'handle' => $this->handle,
            'skipExisting' => $this->skipExisting,
        ]);

        $result = $migration->run();
        MigrationRenderer::renderResultToConsole($result);

        return $result->ok ? ExitCode::OK : ExitCode::UNSPECIFIED_ERROR;
    }
}
