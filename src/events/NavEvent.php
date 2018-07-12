<?php
namespace verbb\navigation\events;

use craft\models\Nav as NavModel;
use yii\base\Event;

class NavEvent extends Event
{
    // Properties
    // =========================================================================

    public $nav;

    public $isNew = false;
}
