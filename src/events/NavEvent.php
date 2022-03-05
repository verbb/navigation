<?php
namespace verbb\navigation\events;

use verbb\navigation\models\Nav;

use yii\base\Event;

class NavEvent extends Event
{
    // Properties
    // =========================================================================

    public Nav $nav;
    public bool $isNew = false;
}
