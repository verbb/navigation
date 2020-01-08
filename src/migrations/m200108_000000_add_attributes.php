<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m200108_000000_add_attributes extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'customAttributes')) {
            $this->addColumn('{{%navigation_nodes}}', 'customAttributes', $this->text()->after('classes'));
        }
    
        return true;
    }

    public function safeDown()
    {
        echo "m200108_000000_add_attributes cannot be reverted.\n";

        return false;
    }
}
