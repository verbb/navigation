<?php
namespace verbb\navigation\deprecations;

use Craft;

trait NodeQueryDeprecations
{
    // Public Methods
    // =========================================================================

    public function nav($value): static
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'NodeQuery `nav()` has been deprecated. Use `menu()` or `menuHandle()` instead.');

        return $this->menu($value);
    }

    public function navHandle($value): static
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'NodeQuery `navHandle()` has been deprecated. Use `menuHandle()` instead.');

        return $this->handle($value);
    }

    public function navId($value): static
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'NodeQuery `navId()` has been deprecated. Use `menuId()` instead.');

        $this->menuId = $value;

        return $this;
    }

    public function elementSiteId($value): static
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'NodeQuery `elementSiteId()` has been deprecated. Set linked element site via node site settings instead.');

        $this->slug = $value;

        return $this;
    }
}
