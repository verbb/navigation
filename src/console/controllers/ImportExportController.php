<?php
namespace verbb\navigation\console\controllers;

use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\Navigation;

use Craft;
use craft\console\Controller;
use craft\helpers\Console;
use craft\helpers\FileHelper;
use craft\helpers\Json;

use Throwable;

use yii\console\ExitCode;

/**
 * Import and export Navigation menus as JSON.
 */
class ImportExportController extends Controller
{
    // Properties
    // =========================================================================

    public ?string $handle = null;
    public ?string $path = null;
    public bool $create = false;
    public bool $update = false;


    // Public Methods
    // =========================================================================

    public function options($actionID): array
    {
        $options = parent::options($actionID);

        switch ($actionID) {
            case 'export-json':
                $options[] = 'handle';
                $options[] = 'path';
                break;
            case 'import-json':
                $options[] = 'path';
                $options[] = 'create';
                $options[] = 'update';
                break;
        }

        return $options;
    }

    /**
     * List exportable menus and JSON files in the export folder.
     */
    public function actionList(?string $folderPath = null): int
    {
        $path = $folderPath ?? $this->getExportPath();

        $this->stdout('Existing menus:' . PHP_EOL, Console::FG_YELLOW);

        foreach (Navigation::$plugin->getMenus()->getAllMenus() as $menu) {
            $this->stdout("- [{$menu->id}] {$menu->handle} — {$menu->name}" . PHP_EOL, Console::FG_GREEN);
        }

        $this->stdout(PHP_EOL . 'JSON files in export folder:' . PHP_EOL, Console::FG_YELLOW);
        $this->stdout($path . PHP_EOL);

        try {
            $files = FileHelper::findFiles($path, ['only' => ['*.json']]);
        } catch (Throwable) {
            $this->stdout('(none — folder missing or empty)' . PHP_EOL);

            return ExitCode::OK;
        }

        if ($files === []) {
            $this->stdout('(none)' . PHP_EOL);
        }

        foreach ($files as $file) {
            $this->stdout('- ' . $file . PHP_EOL, Console::FG_GREEN);
        }

        return ExitCode::OK;
    }

    /**
     * Export a menu to JSON.
     */
    public function actionExportJson(?string $handle = null): int
    {
        $handle ??= $this->handle;

        if (!$handle) {
            $this->stderr('Provide a menu handle: ./craft navigation/import-export/export-json mainMenu' . PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($handle);

        if (!$menu) {
            $this->stderr("No menu found with handle “{$handle}”." . PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        try {
            $export = ImportExportHelper::generateMenuExport($menu);
            $json = Json::encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $exportPath = $this->path ?? $this->getExportPath() . DIRECTORY_SEPARATOR . "navigation-{$handle}.json";

            FileHelper::writeToFile($exportPath, $json);
            $this->stdout("Exported “{$handle}” to {$exportPath}" . PHP_EOL, Console::FG_GREEN);
        } catch (Throwable $e) {
            $this->stderr('Export failed: ' . $e->getMessage() . PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Import a menu from JSON.
     */
    public function actionImportJson(?string $path = null): int
    {
        $path ??= $this->path;

        if (!$path) {
            $this->stderr('Provide a JSON path: ./craft navigation/import-export/import-json navigation-mainMenu.json' . PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!is_file($path)) {
            $relative = $this->getExportPath() . DIRECTORY_SEPARATOR . $path;

            if (is_file($relative)) {
                $path = $relative;
            }
        }

        if (!is_file($path)) {
            $this->stderr("No file exists at “{$path}”." . PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'json') {
            $this->stderr('The file must be a .json file.' . PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $menuAction = $this->update ? 'update' : 'create';

        try {
            $json = file_get_contents($path);
            $result = ImportExportHelper::importMenuFromJson($json, $menuAction);
        } catch (Throwable $e) {
            $this->stderr('Import failed: ' . $e->getMessage() . PHP_EOL, Console::FG_RED);

            return ExitCode::UNSPECIFIED_ERROR;
        }

        foreach ($result->warnings as $warning) {
            $this->stdout('Warning: ' . $warning . PHP_EOL, Console::FG_YELLOW);
        }

        if ($result->hasImportErrors()) {
            foreach ($result->errors as $error) {
                $this->stderr($error . PHP_EOL, Console::FG_RED);
            }

            return ExitCode::UNSPECIFIED_ERROR;
        }

        $menu = $result->menu;
        $this->stdout(
            "Imported menu “{$menu->handle}” — {$result->nodesCreated} nodes created, {$result->nodesSkipped} skipped." . PHP_EOL,
            Console::FG_GREEN,
        );

        return ExitCode::OK;
    }


    // Protected Methods
    // =========================================================================

    protected function getExportPath(): string
    {
        $path = Craft::getAlias('@storage/navigation-exports', false);

        if (!$path) {
            $path = Craft::$app->getPath()->getStoragePath() . DIRECTORY_SEPARATOR . 'navigation-exports';
        }

        FileHelper::createDirectory($path);

        return $path;
    }
}
