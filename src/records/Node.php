<?php
namespace verbb\navigation\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use craft\records\Site;

use yii\db\ActiveQueryInterface;

class Node extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_nodes}}';
    }

    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    public function getNav(): ActiveQueryInterface
    {
        return $this->hasOne(Nav::class, ['id' => 'navId']);
    }
}
