<?php
namespace verbb\navigation\records;

use craft\db\ActiveRecord;
use craft\records\Site;

use yii\db\ActiveQueryInterface;

class Nav_SiteSettings extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName(): string
    {
        return '{{%navigation_navs_sites}}';
    }

    public function getNav(): ActiveQueryInterface
    {
        return $this->hasOne(Nav::class, ['id' => 'navId']);
    }

    public function getSite(): ActiveQueryInterface
    {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }
}
