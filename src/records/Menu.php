<?php
namespace verbb\navigation\records;

use verbb\navigation\records\MenuSiteSettings;
use verbb\navigation\records\Node;

use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;
use craft\records\FieldLayout;
use craft\records\Structure;

use yii\db\ActiveQueryInterface;

class Menu extends ActiveRecord
{
    // Traits
    // =========================================================================

    use SoftDeleteTrait;


    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_menus}}';
    }


    // Public Methods
    // =========================================================================

    public function getStructure(): ActiveQueryInterface
    {
        return $this->hasOne(Structure::class, ['id' => 'structureId']);
    }

    public function getNodes(): ActiveQueryInterface
    {
        return $this->hasMany(Node::class, ['menuId' => 'id']);
    }

    public function getFieldLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
    }

    public function getSiteSettings(): ActiveQueryInterface
    {
        return $this->hasMany(MenuSiteSettings::class, ['menuId' => 'id']);
    }
}
