<?php
namespace verbb\navigation\records;

use craft\db\ActiveRecord;

class NodeSiteSettings extends ActiveRecord
{
    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_nodes_sites}}';
    }
}
