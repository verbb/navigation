<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m200205_000000_add_data extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'data')) {
            $this->addColumn('{{%navigation_nodes}}', 'data', $this->text()->after('customAttributes'));
        }
    
        return true;
    }

    public function safeDown()
    {
        echo "m200205_000000_add_data cannot be reverted.\n";

        return false;
    }
}
