<?php
namespace verbb\navigation\events;

use yii\base\Event;

class RegisterNodeTypeEvent extends Event
{
    // Properties
    // =========================================================================

    public $types = [];
}
