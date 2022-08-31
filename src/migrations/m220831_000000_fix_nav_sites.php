<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;
use verbb\navigation\nodetypes\CustomType;

use Craft;
use craft\db\Query;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;

class m220831_000000_fix_nav_sites extends Migration
{
    public function safeUp(): bool
    {
        // Fix a Craft 3 > 4 migration issue for single-site installs, that didn't enable 
        if (!Craft::$app->getIsMultiSite()) {
            Db::update('{{%navigation_navs_sites}}', [
                'enabled' => true,
            ]);
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m220831_000000_fix_nav_sites cannot be reverted.\n";
        return false;
    }
}

