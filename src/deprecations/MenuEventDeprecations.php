<?php
namespace verbb\navigation\deprecations;

use Craft;

trait MenuEventDeprecations
{
    // Public Methods
    // =========================================================================

    public function __get($name)
    {
        if ($name === 'nav') {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'verbb\\navigation\\events\\NavEvent::nav',
                'NavEvent `$nav` has been deprecated. Use `$menu` instead.',
            );

            return $this->menu;
        }

        return parent::__get($name);
    }

    public function __set($name, $value): void
    {
        if ($name === 'nav') {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'verbb\\navigation\\events\\NavEvent::nav',
                'NavEvent `$nav` has been deprecated. Use `$menu` instead.',
            );

            $this->menu = $value;

            return;
        }

        parent::__set($name, $value);
    }
}
