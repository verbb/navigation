<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\MigrationHelper;

class m190209_000000_project_config extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'isArchived')) {
            $this->addColumn('{{%navigation_navs}}', 'isArchived', $this->boolean()->notNull()->defaultValue(false)->after('propagateNodes'));
        }

        if (!$this->db->columnExists('{{%navigation_navs}}', 'dateArchived')) {
            $this->addColumn('{{%navigation_navs}}', 'dateArchived', $this->dateTime()->after('isArchived'));
        }

        if (!MigrationHelper::doesIndexExist('{{%navigation_navs}}', 'isArchived')) {
            $this->createIndex(null, '{{%navigation_navs}}', 'isArchived', false);
        }

        return true;
    }

    public function safeDown()
    {
        echo "m190203_000000_add_instructions cannot be reverted.\n";

        return false;
    }
}
