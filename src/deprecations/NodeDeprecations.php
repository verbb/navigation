<?php
namespace verbb\navigation\deprecations;

use Craft;

trait NodeDeprecations
{
    // Constants
    // =========================================================================

    private const LEGACY_UNSET = '__navigation_legacy_unset__';


    // Public Methods
    // =========================================================================

    public function getNav(): \verbb\navigation\models\MenuSettings
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'Node `getNav()` has been deprecated. Use `getMenu()` instead.');

        return $this->_getMenu();
    }

    public function getIsActive(): ?bool
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'Node `getIsActive()` has been deprecated. Use `getActive()` or `getActiveState()` instead.');

        return $this->hasResolvedActiveState() ? $this->getActiveState()->isActive : null;
    }

    public function setIsActive($value): void
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'Node `setIsActive()` has been deprecated. Active state is resolved by ActiveMatcher.');

        if ($this->_activeState === null) {
            $this->_activeState = new \verbb\navigation\models\NodeActiveState();
            $this->_activeStateResolved = true;
        }

        $this->_activeState->isActive = (bool)$value;
    }

    public function __get($name)
    {
        $legacy = $this->_resolveLegacyPropertyGet($name);

        if ($legacy !== self::LEGACY_UNSET) {
            return $legacy;
        }

        return parent::__get($name);
    }

    public function __set($name, $value): void
    {
        if ($this->_resolveLegacyPropertySet($name, $value)) {
            return;
        }

        parent::__set($name, $value);
    }


    // Private Methods
    // =========================================================================

    private function _resolveLegacyPropertyGet(string $name): mixed
    {
        if ($name === 'navId') {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'verbb\\navigation\\elements\\Node::navId',
                'Node property `navId` has been deprecated. Use `menuId` instead.',
            );

            return $this->menuId;
        }

        if ($name === 'deletedWithNav') {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'verbb\\navigation\\elements\\Node::deletedWithNav',
                'Node property `deletedWithNav` has been deprecated. Use `deletedWithMenu` instead.',
            );

            return $this->deletedWithMenu;
        }

        return self::LEGACY_UNSET;
    }

    private function _resolveLegacyPropertySet(string $name, mixed $value): bool
    {
        if ($name === 'navId') {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'verbb\\navigation\\elements\\Node::navId',
                'Node property `navId` has been deprecated. Use `menuId` instead.',
            );

            $this->menuId = $value !== null && $value !== '' ? (int)$value : null;

            return true;
        }

        if ($name === 'deletedWithNav') {
            // Deprecated in 4.0.0
            Craft::$app->getDeprecator()->log(
                'verbb\\navigation\\elements\\Node::deletedWithNav',
                'Node property `deletedWithNav` has been deprecated. Use `deletedWithMenu` instead.',
            );

            $this->deletedWithMenu = (bool)$value;

            return true;
        }

        return false;
    }
}
