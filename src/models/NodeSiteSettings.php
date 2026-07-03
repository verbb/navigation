<?php
namespace verbb\navigation\models;

use craft\base\Model;

class NodeSiteSettings extends Model
{
    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $nodeId = null;
    public ?int $siteId = null;
    public ?int $linkedElementSiteId = null;
    public ?string $url = null;
    public ?string $urlSuffix = null;
    public ?string $uid = null;
}
