<?php
namespace verbb\navigation\helpers;

use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation;

use Craft;
use craft\elements\User;
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

        if ($siteId !== null && !in_array($siteId, $menu->getSiteIds(), false)) {
            throw new ForbiddenHttpException('User is not authorized to access this menu site.');
        }

        return $menu;
    }

    public static function currentUserCanManage(?MenuSettings $menu): bool
    {
        return self::canManageMenu(Craft::$app->getUser()->getIdentity(), $menu);
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
