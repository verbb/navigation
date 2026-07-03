<?php
namespace verbb\navigation\migrations\plugins;

use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\Navigation;

use Craft;

use Throwable;

use yii\base\Component;

abstract class BasePluginMigrator extends Component
{
    // Static Methods
    // =========================================================================

    abstract public static function sourceLabel(): string;
    abstract public static function pluginHandle(): string;
    abstract public static function sourceTable(): string;

    public static function getMenus(): array
    {
        return [];
    }


    // Properties
    // =========================================================================

    public ?string $handle = null;
    public bool $skipExisting = false;

    protected MigrationResult $result;


    // Public Methods
    // =========================================================================

    public function run(): MigrationResult
    {
        $this->result = new MigrationResult();

        try {
            if (!$this->safeUp()) {
                $this->result->ok = false;
            }
        } catch (Throwable $e) {
            $this->result->ok = false;
            $this->error($e->getMessage());
            Craft::error($e->getMessage() . PHP_EOL . $e->getTraceAsString(), __METHOD__);
        }

        return $this->result;
    }

    abstract public function safeUp(): bool;


    // Protected Methods
    // =========================================================================

    protected function importExportPayload(array $payload, string $menuLabel): bool
    {
        $handle = (string)($payload['menu']['handle'] ?? '');
        $name = (string)($payload['menu']['name'] ?? $menuLabel);

        $this->info("Menu: Preparing to migrate menu “{$name}”.");

        if ($handle !== '' && $this->skipExisting && PluginMigrationHelper::menuHandleExists($handle)) {
            $this->warning("Skipped menu “{$menuLabel}”: handle “{$handle}” already exists.", 1);
            $this->incrementStat('menusSkipped');

            return true;
        }

        $import = ImportExportHelper::importMenuFromJson($payload, 'create');
        PluginMigrationHelper::mergeImportResult($this->result, $import);

        if ($import->hasImportErrors()) {
            return false;
        }

        $savedHandle = $import->menu?->handle ?? $handle;

        if ($handle !== '' && $savedHandle !== $handle) {
            $this->info("Handle “{$handle}” is taken, saved as “{$savedHandle}” instead.", 1);
        }

        $this->success("Menu “{$savedHandle}” migrated ({$import->nodesCreated} nodes).", 1);
        $this->incrementStat('menusMigrated');

        return true;
    }

    protected function handlesFilter(): ?array
    {
        if ($this->handle === null || trim($this->handle) === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->handle))));
    }

    protected function matchesHandle(string $handle, ?array $filter): bool
    {
        if ($filter === null) {
            return true;
        }

        return in_array($handle, $filter, true);
    }

    protected function addLine(MigrationLine $line): void
    {
        $this->result->addLine($line);

        if ($line->level === 'error') {
            $this->result->ok = false;
            $this->incrementStat('errors');
        }
    }

    protected function setStat(string $key, mixed $value): void
    {
        $this->result->setStat($key, $value);
    }

    protected function incrementStat(string $key, int $value = 1): void
    {
        $this->result->incrementStat($key, $value);
    }

    protected function info(string $message, int $depth = 0): void
    {
        $this->addLine(Line::info($this->_normalizeMessage($message), $this->_normalizeDepth($message, $depth)));
    }

    protected function success(string $message, int $depth = 0): void
    {
        $this->addLine(Line::success($this->_normalizeMessage($message), $this->_normalizeDepth($message, $depth)));
    }

    protected function warning(string $message, int $depth = 0): void
    {
        $this->addLine(Line::warning($this->_normalizeMessage($message), $this->_normalizeDepth($message, $depth)));
        $this->incrementStat('warnings');
    }

    protected function error(string $message, int $depth = 0): void
    {
        $this->addLine(Line::error($this->_normalizeMessage($message), $this->_normalizeDepth($message, $depth)));
    }


    // Private Methods
    // =========================================================================

    private function _normalizeMessage(string $message): string
    {
        $message = trim(strip_tags($message));
        $message = preg_replace('/^\s*>\s*/', '', $message) ?? $message;

        return preg_replace('/^\s+/', '', $message) ?? $message;
    }

    private function _normalizeDepth(string $message, int $depth): int
    {
        if ($depth > 0 || preg_match('/^\s*>\s*/', $message)) {
            return 1;
        }

        return 0;
    }
}
