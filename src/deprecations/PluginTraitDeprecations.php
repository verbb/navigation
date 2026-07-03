<?php
namespace verbb\navigation\deprecations;

use verbb\navigation\services\Menus;

use Craft;

trait PluginTraitDeprecations
{
    // Public Methods
    // =========================================================================

    /**
     * @deprecated in 4.0.0. Added in Navigation 4.0.0 for v3 compatibility. Use {@see getMenus()} instead.
     */
    public function getNavs(): Menus
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(
            'verbb\\navigation\\Navigation::getNavs',
            '`getNavs()` has been deprecated. Use `getMenus()` instead.',
        );

        return $this->getMenus();
    }
}
