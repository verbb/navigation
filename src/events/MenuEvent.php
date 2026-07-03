<?php
namespace verbb\navigation\events;

use verbb\navigation\deprecations\MenuEventDeprecations;
use verbb\navigation\models\MenuSettings;

use yii\base\Event;

class MenuEvent extends Event
{
    // Traits
    // =========================================================================

    use MenuEventDeprecations;


    // Properties
    // =========================================================================

    public MenuSettings $menu;
    public bool $isNew = false;
}
