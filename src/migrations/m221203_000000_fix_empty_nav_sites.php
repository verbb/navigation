<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;
use verbb\navigation\nodetypes\CustomType;

use Craft;
use craft\db\Query;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;

use DateTime;

class m221203_000000_fix_empty_nav_sites extends Migration
{
    public function safeUp(): bool
    {
        // Fix a Craft 3 > 4 migration issue, where there might not be any site settings
        $navs = (new Query())
            ->select(['*'])
            ->from('{{%navigation_navs}}')
            ->all();

        foreach ($navs as $nav) {
            $navSite = (new Query())
                ->select(['*'])
                ->from('{{%navigation_navs_sites}}')
                ->where(['navId' => $nav['id']])
                ->all();

            if (!$navSite) {
                foreach (Craft::$app->getSites()->getAllSites() as $site) {
                    Db::insert('{{%navigation_navs_sites}}', [
                        'navId' => $nav['id'],
                        'siteId' => $site->id,
                        'enabled' => true,
                        'dateCreated' => Db::prepareDateForDb(new DateTime()),
                        'dateUpdated' => Db::prepareDateForDb(new DateTime()),
                        'uid' => StringHelper::UUID(),
                    ]);
                }
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m220902_000000_fix_empty_nav_sites cannot be reverted.\n";
        return false;
    }
}

