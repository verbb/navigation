<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m201017_000000_add_permissions extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'permissions')) {
            $this->addColumn('{{%navigation_navs}}', 'permissions', $this->text()->after('maxNodes'));
        }
    
        return true;
    }

    public function safeDown(): bool
    {
        echo "m201017_000000_add_permissions cannot be reverted.\n";

        return false;
    }
}
