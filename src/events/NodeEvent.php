<?php
namespace verbb\navigation\events;

use verbb\navigation\elements\Node;

use yii\base\Event;

class NodeEvent extends Event
{
    // Properties
    // =========================================================================

    public Node $node;
    public bool $isNew = false;
}
