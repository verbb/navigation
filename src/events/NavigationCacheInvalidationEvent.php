<?php
namespace verbb\navigation\events;

use yii\base\Event;

class NavigationCacheInvalidationEvent extends Event
{
    // Properties
    // =========================================================================

    public array $tags = [];
    public ?string $menuUid = null;
    public ?int $siteId = null;
    public ?int $nodeId = null;
}
