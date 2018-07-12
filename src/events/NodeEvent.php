<?php
namespace verbb\navigation\events;

use craft\models\Node as NodeModel;
use yii\base\Event;

class NodeEvent extends Event
{
    // Properties
    // =========================================================================

    public $node;

    public $isNew = false;
}
