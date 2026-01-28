<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;
use verbb\navigation\nodetypes\CustomType;

use Craft;
use craft\db\Query;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;

class m260128_000000_site_menu extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'showSiteMenu')) {
            $this->addColumn('{{%navigation_navs}}', 'showSiteMenu', $this->boolean()->defaultValue(true)->after('defaultPlacement'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m260128_000000_site_menu cannot be reverted.\n";
        return false;
    }
}

