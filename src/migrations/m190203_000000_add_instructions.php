<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m190203_000000_add_instructions extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'instructions')) {
            $this->addColumn('{{%navigation_navs}}', 'instructions', $this->text()->after('handle'));
        }
    
        return true;
    }

    public function safeDown(): bool
    {
        echo "m190203_000000_add_instructions cannot be reverted.\n";

        return false;
    }
}
