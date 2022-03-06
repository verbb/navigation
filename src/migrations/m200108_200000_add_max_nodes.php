<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m200108_200000_add_max_nodes extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'maxNodes')) {
            $this->addColumn('{{%navigation_navs}}', 'maxNodes', $this->integer()->after('propagateNodes'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m200108_200000_add_max_nodes cannot be reverted.\n";

        return false;
    }
}
