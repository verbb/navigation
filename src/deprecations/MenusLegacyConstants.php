<?php
namespace verbb\navigation\deprecations;

trait MenusLegacyConstants
{
    // Constants
    // =========================================================================

    /** @deprecated in 4.0.0. Added in Navigation 4.0.0 for v3 compatibility. Use {@see Menus::EVENT_BEFORE_SAVE_MENU}. */
    public const EVENT_BEFORE_SAVE_NAV = 'beforeSaveMenu';

    /** @deprecated in 4.0.0. Added in Navigation 4.0.0 for v3 compatibility. Use {@see Menus::EVENT_AFTER_SAVE_MENU}. */
    public const EVENT_AFTER_SAVE_NAV = 'afterSaveMenu';

    /** @deprecated in 4.0.0. Added in Navigation 4.0.0 for v3 compatibility. Use {@see Menus::EVENT_BEFORE_APPLY_MENU_DELETE}. */
    public const EVENT_BEFORE_APPLY_NAV_DELETE = 'beforeApplyMenuDelete';

    /** @deprecated in 4.0.0. Added in Navigation 4.0.0 for v3 compatibility. Use {@see Menus::EVENT_BEFORE_DELETE_MENU}. */
    public const EVENT_BEFORE_DELETE_NAV = 'beforeDeleteMenu';

    /** @deprecated in 4.0.0. Added in Navigation 4.0.0 for v3 compatibility. Use {@see Menus::EVENT_AFTER_DELETE_MENU}. */
    public const EVENT_AFTER_DELETE_NAV = 'afterDeleteMenu';
}
