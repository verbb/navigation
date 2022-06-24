<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m190203_000000_add_instructions extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'instructions')) {
            $this->addColumn('{{%navigation_navs}}', 'instructions', $this->text()->after('handle'));
        }

        return true;
    }

    public function safeDown()
    {
        echo "m190203_000000_add_instructions cannot be reverted.\n";

        return false;
    }
}
