<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m200205_000000_add_data extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'data')) {
            $this->addColumn('{{%navigation_nodes}}', 'data', $this->text()->after('customAttributes'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m200205_000000_add_data cannot be reverted.\n";

        return false;
    }
}
