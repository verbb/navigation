<?php
namespace verbb\navigation\deprecations;

use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation;

use Craft;

trait MenuDeprecations
{
    // Public Methods
    // =========================================================================

    public function getNav(): MenuSettings
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'Menu `getNav()` has been deprecated. Use the Menu element API or `getMenuHandle()` instead.');

        $menu = Navigation::$plugin->getMenus()->getMenuById($this->id);

        if (!$menu) {
            throw new \yii\base\InvalidConfigException('Invalid menu ID: ' . $this->id);
        }

        return $menu;
    }
}
