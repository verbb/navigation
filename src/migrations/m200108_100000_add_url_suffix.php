<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m200108_100000_add_url_suffix extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'urlSuffix')) {
            $this->addColumn('{{%navigation_nodes}}', 'urlSuffix', $this->string(255)->after('classes'));
        }

        return true;
    }

    public function safeDown()
    {
        echo "m200108_100000_add_url_suffix cannot be reverted.\n";

        return false;
    }
}
