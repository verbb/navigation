<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m180826_000000_propagate_nav_setting extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%navigation_navs}}', 'propagateNodes', $this->boolean()->after('sortOrder')->defaultValue(false)->notNull());
    }

    public function safeDown()
    {
        echo "m180826_000000_propagate_nav_setting cannot be reverted.\n";

        return false;
    }
}
