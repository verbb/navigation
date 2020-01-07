<?php
namespace verbb\navigation\records;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\Structure;
use craft\records\FieldLayout;

use yii\db\ActiveQueryInterface;

class Nav extends ActiveRecord
{
    // Traits
    // =========================================================================

    use SoftDeleteTrait;


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

    public function getNodes(): ActiveQueryInterface
    {
        return $this->hasMany(Node::class, ['navId' => 'id']);
    }

    public function getFieldLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
    }
}
