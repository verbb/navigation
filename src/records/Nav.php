<?php
namespace verbb\navigation\records;

use craft\db\ActiveRecord;
use craft\records\Structure;

use yii\db\ActiveQueryInterface;

class Nav extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_navs}}';
    }

    public function getStructure(): ActiveQueryInterface
    {
        return $this->hasOne(Structure::class, ['id' => 'structureId']);
    }

    public function getNavItems(): ActiveQueryInterface
    {
        return $this->hasMany(Node::class, ['navId' => 'id']);
    }
}
