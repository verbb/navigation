<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;
use verbb\navigation\nodetypes\CustomType;

use Craft;
use craft\db\Query;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;

class m221025_100000_max_nodes_settings extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'maxNodesSettings')) {
            $this->addColumn('{{%navigation_navs}}', 'maxNodesSettings', $this->text()->after('maxNodes'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m221025_100000_max_nodes_settings cannot be reverted.\n";
        return false;
    }
}

