<?php
namespace verbb\navigation\events;

use verbb\navigation\elements\Node;
use verbb\navigation\models\NodeSiteSettings;

use yii\base\Event;

class CopyNodeToSiteEvent extends Event
{
    // Properties
    // =========================================================================

    public Node $source;
    public Node $duplicate;
    public int $targetSiteId;
    public NodeSiteSettings $targetSettings;
    public bool $remapLinkedElements;
}
