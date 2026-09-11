<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;
use verbb\navigation\base\ElementNodeType;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;

use Craft;
use craft\elements\User;
use craft\helpers\ElementHelper;
use craft\web\Controller;

use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Central CP authorization for menu configuration and node authoring.
 *
 * Craft authenticates the request; this helper enforces Navigation menu grants
 * so services/elements stay usable from console while controllers share one policy.
 */
class MenuAuth
{
    // Static Methods
    // =========================================================================

    public static function canManageMenu(?User $user, MenuSettings|string|null $menuOrUid): bool
    {
        if (!$user || !$menuOrUid) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        $uid = is_string($menuOrUid) ? $menuOrUid : $menuOrUid->uid;

        return $uid ? $user->can('navigation-manageMenu:' . $uid) : false;
    }

    public static function canEditMenu(?User $user, MenuSettings|string|null $menuOrUid): bool
    {
        if (!$user || !$menuOrUid) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        $uid = is_string($menuOrUid) ? $menuOrUid : $menuOrUid->uid;

        return $uid ? $user->can('navigation-editMenu:' . $uid) : false;
    }

    public static function canCreateMenus(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->admin || $user->can('navigation-createMenus');
    }

    public static function canDeleteMenu(?User $user, MenuSettings|string|null $menuOrUid): bool
    {
        if (!$user || !$menuOrUid) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        $uid = is_string($menuOrUid) ? $menuOrUid : $menuOrUid->uid;

        return $uid ? $user->can('navigation-deleteMenu:' . $uid) : false;
    }

    public static function requireManageMenu(Controller $controller, ?MenuSettings $menu): MenuSettings
    {
        $menu = self::_requireMenu($menu);
        $controller->requirePermission('navigation-manageMenu:' . $menu->uid);

        return $menu;
    }

    public static function requireEditMenu(Controller $controller, ?MenuSettings $menu): MenuSettings
    {
        $menu = self::_requireMenu($menu);
        $controller->requirePermission('navigation-editMenu:' . $menu->uid);

        return $menu;
    }

    public static function requireDeleteMenu(Controller $controller, ?MenuSettings $menu): MenuSettings
    {
        $menu = self::_requireMenu($menu);
        $controller->requirePermission('navigation-deleteMenu:' . $menu->uid);

        return $menu;
    }

    public static function requireCreateMenus(Controller $controller): void
    {
        $controller->requirePermission('navigation-createMenus');
    }

    /**
     * Resolves a menu by id and requires manage permission. Rejects missing menus
     * before any write so posted foreign IDs cannot skip authorization.
     */
    public static function requireManageMenuById(Controller $controller, ?int $menuId): MenuSettings
    {
        if (!$menuId) {
            throw new BadRequestHttpException('Invalid menu ID.');
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($menuId);

        return self::requireManageMenu($controller, $menu);
    }

    /**
     * Ensures the current user may manage the menu and that the site is enabled
     * for that menu when a site id is supplied.
     */
    public static function requireManageMenuSite(Controller $controller, ?MenuSettings $menu, ?int $siteId = null): MenuSettings
    {
        $menu = self::requireManageMenu($controller, $menu);

        $siteId ??= Craft::$app->getSites()->getCurrentSite()->id;
        if (!self::canManageMenuSite(Craft::$app->getUser()->getIdentity(), $menu, $siteId)) {
            throw new ForbiddenHttpException('User is not authorized to access this menu site.');
        }

        return $menu;
    }

    public static function canManageMenuSite(?User $user, ?MenuSettings $menu, int $siteId): bool
    {
        $site = Craft::$app->getSites()->getSiteById($siteId);
        return $site !== null && self::canManageMenu($user, $menu)
            && in_array($siteId, $menu->getSiteIds(), false)
            && (!Craft::$app->getIsMultiSite() || $user->admin || $user->can('editSite:' . $site->uid));
    }

    public static function currentUserCanManage(?MenuSettings $menu): bool
    {
        return self::canManageMenu(Craft::$app->getUser()->getIdentity(), $menu);
    }

    public static function canAuthorNode(?User $user, Node $node): bool
    {
        $menu = Navigation::$plugin->getMenus()->getMenuById($node->menuId);
        if (!self::canManageMenuSite($user, $menu, (int)$node->siteId)) {
            return false;
        }
        $type = $node->nodeType();
        if (!$type || !MenuPermissions::isTypeEnabled($menu->permissions ?? [], $type::class, $type->getPermissionEnabledDefault())) {
            return false;
        }
        if ($parentId = $node->getParentId()) {
            if (!Node::find()->id($parentId)->menuId($menu->id)->siteId($node->siteId)->status(null)->exists()) {
                return false;
            }
        }
        if (!$type instanceof ElementNodeType || !$node->elementId) {
            return true; // Missing required element IDs are reported by model validation.
        }
        $element = $node->getElement();
        if (!$element || !$element->canView($user)) {
            return false;
        }
        $elementType = $type::getElementType();
        $sources = ElementPickerHelper::filterSourcesForUser($elementType, MenuPermissions::getTypeSources($menu->permissions ?? [], $type::class));
        $picker = ElementPickerHelper::getPickerConfig($menu->permissions ?? [], $type::class, $elementType);
        foreach ($sources as $key) {
            $source = ElementHelper::findSource($elementType, $key, 'modal');
            if (!$source) {
                continue;
            }
            // Both constraints must match independently: picker settings can use
            // the same criterion (e.g. sectionId) as the allowed source.
            $matches = true;
            foreach ([$source, $picker] as $restriction) {
                $query = $elementType::find()->siteId($element->siteId)->status(null);
                Craft::configure($query, $restriction['criteria'] ?? []);
                if ($condition = $restriction['condition'] ?? null) {
                    Craft::$app->getConditions()->createCondition($condition)->modifyQuery($query);
                }
                if (!$query->andWhere(['elements.id' => $element->id, 'elements_sites.siteId' => $element->siteId])->exists()) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                return true;
            }
        }
        return false;
    }


    // Private Methods
    // =========================================================================

    private static function _requireMenu(?MenuSettings $menu): MenuSettings
    {
        if (!$menu || !$menu->uid) {
            throw new BadRequestHttpException('Invalid menu.');
        }

        return $menu;
    }
}
