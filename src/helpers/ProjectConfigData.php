<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;

use Craft;

class ProjectConfigData
{
    // Static Methods
    // =========================================================================

    /**
     * Whether migrations may write to project config.
     *
     * Craft sets project config to read-only when `allowAdminChanges` is false (production).
     * Console commands such as `entrify/*` skip project config writes in that case; YAML is
     * deployed and applied via `project-config/apply` instead. Plugin migrations should follow
     * the same pattern for one-time config renames/transforms.
     */
    public static function canUpdateProjectConfig(): bool
    {
        return Craft::$app->getConfig()->getGeneral()->allowAdminChanges;
    }

    public static function rebuildProjectConfig(): array
    {
        $configData = [];

        $configData['menus'] = self::_getMenusData();

        return array_filter($configData);
    }


    // Private Methods
    // =========================================================================

    private static function _getMenusData(): array
    {
        $data = [];

        foreach (Navigation::$plugin->getMenus()->getAllMenus() as $nav) {
            $data[$nav->uid] = $nav->getConfig();
        }

        return $data;
    }
}
