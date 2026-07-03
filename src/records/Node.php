<?php
namespace verbb\navigation\records;

use verbb\navigation\records\Menu;

use craft\db\ActiveRecord;
use craft\records\Element;

use yii\db\ActiveQueryInterface;

class Node extends ActiveRecord
{
    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_nodes}}';
    }


    // Public Methods
    // =========================================================================

    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    public function getMenu(): ActiveQueryInterface
    {
        return $this->hasOne(Menu::class, ['id' => 'menuId']);
    }
}
