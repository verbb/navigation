<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m200108_000000_add_attributes extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'customAttributes')) {
            $this->addColumn('{{%navigation_nodes}}', 'customAttributes', $this->text()->after('classes'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m200108_000000_add_attributes cannot be reverted.\n";

        return false;
    }
}
