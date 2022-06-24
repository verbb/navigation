<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m200108_200000_add_max_nodes extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'maxNodes')) {
            $this->addColumn('{{%navigation_navs}}', 'maxNodes', $this->integer()->after('propagateNodes'));
        }

        return true;
    }

    public function safeDown()
    {
        echo "m200108_200000_add_max_nodes cannot be reverted.\n";

        return false;
    }
}
