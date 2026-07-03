<?php
namespace verbb\navigation\deprecations;

use verbb\navigation\models\MenuSettings;

use Craft;

trait MenusDeprecations
{
    // Public Methods
    // =========================================================================

    /** @deprecated in 4.0.0. Use {@see getAllMenus()} instead. */
    public function getAllNavs(): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getAllNavs()` has been deprecated. Use `getAllMenus()` instead.');

        return $this->getAllMenus();
    }

    /** @deprecated in 4.0.0. Use {@see getEditableMenus()} instead. */
    public function getEditableNavs(): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getEditableNavs()` has been deprecated. Use `getEditableMenus()` instead.');

        return $this->getEditableMenus();
    }

    /** @deprecated in 4.0.0. Use {@see getEditableMenusForSite()} instead. */
    public function getEditableNavsForSite($site): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getEditableNavsForSite()` has been deprecated. Use `getEditableMenusForSite()` instead.');

        return $this->getEditableMenusForSite($site);
    }

    /** @deprecated in 4.0.0. Use {@see getEditableMenuIds()} instead. */
    public function getEditableNavIds(): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getEditableNavIds()` has been deprecated. Use `getEditableMenuIds()` instead.');

        return $this->getEditableMenuIds();
    }

    /** @deprecated in 4.0.0. Use {@see getMenuByHandle()} instead. */
    public function getNavByHandle(string $handle): ?MenuSettings
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getNavByHandle()` has been deprecated. Use `getMenuByHandle()` instead.');

        return $this->getMenuByHandle($handle);
    }

    /** @deprecated in 4.0.0. Use {@see getMenuById()} instead. */
    public function getNavById(int $id): ?MenuSettings
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getNavById()` has been deprecated. Use `getMenuById()` instead.');

        return $this->getMenuById($id);
    }

    /** @deprecated in 4.0.0. Use {@see getMenuByUid()} instead. */
    public function getNavByUid(string $uid): ?MenuSettings
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getNavByUid()` has been deprecated. Use `getMenuByUid()` instead.');

        return $this->getMenuByUid($uid);
    }

    /** @deprecated in 4.0.0. Use {@see getMenuSiteSettings()} instead. */
    public function getNavSiteSettings(int $navId): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`getNavSiteSettings()` has been deprecated. Use `getMenuSiteSettings()` instead.');

        return $this->getMenuSiteSettings($navId);
    }

    /** @deprecated in 4.0.0. Use {@see saveMenu()} instead. */
    public function saveNav(MenuSettings $nav, bool $runValidation = true): bool
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`saveNav()` has been deprecated. Use `saveMenu()` instead.');

        return $this->saveMenu($nav, $runValidation);
    }

    /** @deprecated in 4.0.0. Use {@see deleteMenuById()} instead. */
    public function deleteNavById(int $navId): bool
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`deleteNavById()` has been deprecated. Use `deleteMenuById()` instead.');

        return $this->deleteMenuById($navId);
    }

    /** @deprecated in 4.0.0. Use {@see deleteMenu()} instead. */
    public function deleteNav(MenuSettings $nav): bool
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`deleteNav()` has been deprecated. Use `deleteMenu()` instead.');

        return $this->deleteMenu($nav);
    }

    /** @deprecated in 4.0.0. Use {@see reorderMenus()} instead. */
    public function reorderNavs(array $navIds): bool
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`reorderNavs()` has been deprecated. Use `reorderMenus()` instead.');

        return $this->reorderMenus($navIds);
    }
}
