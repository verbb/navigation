<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m180827_000000_propagate_nav_setting_additional extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'propagateNodes')) {
            $this->addColumn('{{%navigation_navs}}', 'propagateNodes', $this->boolean()->after('sortOrder')->notNull()->defaultValue(false));
        }

        return true;
    }

    public function safeDown()
    {
        echo "m180827_000000_propagate_nav_setting_additional cannot be reverted.\n";

        return false;
    }
}
