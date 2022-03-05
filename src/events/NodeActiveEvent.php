<?php
namespace verbb\navigation\events;

use verbb\navigation\elements\Node;

use yii\base\Event;

class NodeActiveEvent extends Event
{
    // Properties
    // =========================================================================

    public Node $node;
    public ?bool $isActive = null;
}
