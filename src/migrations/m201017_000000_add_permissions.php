<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m201017_000000_add_permissions extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'permissions')) {
            $this->addColumn('{{%navigation_navs}}', 'permissions', $this->text()->after('maxNodes'));
        }
    
        return true;
    }

    public function safeDown()
    {
        echo "m201017_000000_add_permissions cannot be reverted.\n";

        return false;
    }
}
