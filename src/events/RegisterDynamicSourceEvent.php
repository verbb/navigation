<?php
namespace verbb\navigation\events;

use yii\base\Event;

class RegisterDynamicSourceEvent extends Event
{
    // Properties
    // =========================================================================

    public array $providers = [];
}
