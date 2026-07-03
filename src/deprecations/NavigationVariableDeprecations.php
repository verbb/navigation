<?php
namespace verbb\navigation\deprecations;

use verbb\navigation\elements\db\MenuQuery;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\NavigationContext;
use verbb\navigation\Navigation;
use verbb\navigation\base\ElementNodeType;

use Craft;

trait NavigationVariableDeprecations
{
    // Public Methods
    // =========================================================================

    public function nav($handle): MenuQuery
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.nav()` has been deprecated. Use `craft.navigation.menu()` instead.');

        return $this->menu($handle);
    }

    public function getRegisteredElements(): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.getRegisteredElements()` has been deprecated. Use `craft.navigation.getRegisteredNodeTypes()` instead.');

        $elements = [];

        foreach (Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes() as $nodeType) {
            if (!$nodeType instanceof ElementNodeType) {
                continue;
            }

            $config = $nodeType::getBuilderConfig();

            $elements[] = [
                'label' => $config['label'],
                'button' => $config['button'],
                'type' => $nodeType::class,
                'elementType' => $nodeType::getElementType(),
                'default' => $nodeType->getPermissionEnabledDefault(),
                'color' => $nodeType::getColor(),
            ];
        }

        return $elements;
    }

    public function breadcrumbs(array $options = []): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.breadcrumbs()` has been deprecated. Use `craft.navigation.urlBreadcrumbs()` instead.');

        return $this->urlBreadcrumbs($options);
    }

    public function getNavByHandle($handle): ?MenuSettings
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.getNavByHandle()` has been deprecated. Use `craft.navigation.getMenuByHandle()` or `craft.navigation.menu()` instead.');

        return Navigation::$plugin->getMenus()->getMenuByHandle($handle);
    }

    public function getNavById($id): ?MenuSettings
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.getNavById()` has been deprecated. Use `craft.navigation.getMenuById()` instead.');

        return Navigation::$plugin->getMenus()->getMenuById((int)$id);
    }

    public function getAllNavs(): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.getAllNavs()` has been deprecated. Use `craft.navigation.getAllMenus()` instead.');

        return Navigation::$plugin->getMenus()->getAllMenus();
    }

    public function navBreadcrumbs(string $navHandle): array
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.navBreadcrumbs()` has been deprecated. Use `craft.navigation.menuBreadcrumbs()` instead.');

        return $this->menuBreadcrumbs($navHandle);
    }

    public function navContext(string $navHandle, mixed $criteria = null): NavigationContext
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, '`craft.navigation.navContext()` has been deprecated. Use `craft.navigation.context()` instead.');

        return $this->context($navHandle, $criteria);
    }


    // Protected Methods
    // =========================================================================

    protected function normalizeDeprecatedNodeCriteria(array $criteria): array
    {
        // Deprecated in 4.0.0
        return DeprecationHelper::normalizeNodeQueryCriteria($criteria);
    }
}
