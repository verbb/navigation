<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m200108_100000_add_url_suffix extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'urlSuffix')) {
            $this->addColumn('{{%navigation_nodes}}', 'urlSuffix', $this->string(255)->after('classes'));
        }
    
        return true;
    }

    public function safeDown(): bool
    {
        echo "m200108_100000_add_url_suffix cannot be reverted.\n";

        return false;
    }
}
