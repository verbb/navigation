<?php
namespace verbb\navigation\services;

use verbb\navigation\helpers\PluginMigrationHelper;
use verbb\navigation\migrations\plugins\MigrateFromFreeNav;
use verbb\navigation\migrations\plugins\MigrateFromNavigate;
use verbb\navigation\migrations\plugins\MigrateFromOlivemenus;
use verbb\navigation\migrations\plugins\MigrateFromTkaNavigation;

use craft\base\Component;

class Migrations extends Component
{
    // Public Methods
    // =========================================================================

    public function getSources(): array
    {
        return [
            'free-nav' => $this->_sourceInfo(MigrateFromFreeNav::class, 'free-nav'),
            'navigate' => $this->_sourceInfo(MigrateFromNavigate::class, 'navigate'),
            'olivemenus' => $this->_sourceInfo(MigrateFromOlivemenus::class, 'olivemenus'),
            'tka-navigation' => $this->_sourceInfo(MigrateFromTkaNavigation::class, 'tka-navigation'),
        ];
    }

    public function getMigratorClass(string $sourceId): ?string
    {
        return match ($sourceId) {
            'free-nav' => MigrateFromFreeNav::class,
            'navigate' => MigrateFromNavigate::class,
            'olivemenus' => MigrateFromOlivemenus::class,
            'tka-navigation' => MigrateFromTkaNavigation::class,
            default => null,
        };
    }

    public function getSourceMenus(string $sourceId): array
    {
        $class = $this->getMigratorClass($sourceId);

        if (!$class) {
            return [];
        }

        return $class::getMenus();
    }

    public function isSourceReady(string $sourceId): bool
    {
        $sources = $this->getSources();

        return (bool)($sources[$sourceId]['ready'] ?? false);
    }


    // Private Methods
    // =========================================================================

    private function _sourceInfo(string $class, string $sourceId): array
    {
        $pluginHandle = $class::pluginHandle();
        $table = $class::sourceTable();
        $installed = PluginMigrationHelper::isPluginInstalled($pluginHandle);
        $tableExists = PluginMigrationHelper::tableExists($table);
        $count = $tableExists ? PluginMigrationHelper::countTableRows($table) : 0;

        return [
            'id' => $sourceId,
            'label' => $class::sourceLabel(),
            'pluginHandle' => $pluginHandle,
            'table' => $table,
            'installed' => $installed,
            'tableExists' => $tableExists,
            'ready' => $installed && $tableExists,
            'menuCount' => $count,
            'consoleCommand' => 'navigation/migrate/' . $sourceId,
        ];
    }
}
