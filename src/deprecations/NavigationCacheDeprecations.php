<?php
namespace verbb\navigation\deprecations;

use Craft;

trait NavigationCacheDeprecations
{
    // Public Methods
    // =========================================================================

    /** @deprecated in 4.0.0. Use {@see menuTag()} instead. */
    public function navTag(string $menuUid): string
    {
        Craft::$app->getDeprecator()->log(__METHOD__, '`navTag()` has been deprecated. Use `menuTag()` instead.');

        return $this->menuTag($menuUid);
    }

    /** @deprecated in 4.0.0. Use {@see menuSiteTag()} instead. */
    public function navSiteTag(string $menuUid, int $siteId): string
    {
        Craft::$app->getDeprecator()->log(__METHOD__, '`navSiteTag()` has been deprecated. Use `menuSiteTag()` instead.');

        return $this->menuSiteTag($menuUid, $siteId);
    }

    /** @deprecated in 4.0.0. Use {@see invalidateMenu()} instead. */
    public function invalidateNav(string $menuUid): void
    {
        Craft::$app->getDeprecator()->log(__METHOD__, '`invalidateNav()` has been deprecated. Use `invalidateMenu()` instead.');

        $this->invalidateMenu($menuUid);
    }

    /** @deprecated in 4.0.0. Use {@see invalidateMenuSite()} instead. */
    public function invalidateNavSite(string $menuUid, int $siteId): void
    {
        Craft::$app->getDeprecator()->log(__METHOD__, '`invalidateNavSite()` has been deprecated. Use `invalidateMenuSite()` instead.');

        $this->invalidateMenuSite($menuUid, $siteId);
    }
}
