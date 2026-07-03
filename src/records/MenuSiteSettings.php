<?php
namespace verbb\navigation\records;

use verbb\navigation\records\Menu;

use craft\db\ActiveRecord;
use craft\records\Site;

use yii\db\ActiveQueryInterface;

class MenuSiteSettings extends ActiveRecord
{
    // Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_menus_sites}}';
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
}
