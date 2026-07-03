<?php
namespace verbb\navigation\records;

use craft\db\ActiveRecord;
use craft\records\Site;
use craft\records\User;

use yii\db\ActiveQueryInterface;

class BuildSession extends ActiveRecord
{
    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_build_sessions}}';
    }


    // Public Methods
    // =========================================================================

    public function getMenu(): ActiveQueryInterface
    {
        return $this->hasOne(Menu::class, ['id' => 'menuId']);
    }

    public function getSite(): ActiveQueryInterface
    {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }

    public function getUser(): ActiveQueryInterface
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }
}
