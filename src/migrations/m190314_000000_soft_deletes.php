<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\fields\NavigationField;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\MigrationHelper;

class m190314_000000_soft_deletes extends Migration
{
    public function safeUp()
    {
        // Add the dateDeleted columns
        if (!$this->db->columnExists('{{%navigation_navs}}', 'dateDeleted')) {
            $this->addColumn('{{%navigation_navs}}', 'dateDeleted', $this->dateTime()->null()->after('dateUpdated'));
        }

        if (!MigrationHelper::doesIndexExist('{{%navigation_navs}}', 'dateDeleted')) {
            $this->createIndex(null, '{{%navigation_navs}}', ['dateDeleted'], false);
        }

        // Keep track of how elements are deleted
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'deletedWithNav')) {
            $this->addColumn('{{%navigation_nodes}}', 'deletedWithNav', $this->boolean()->null()->after('newWindow'));
        }

        // Give nodes a way to keep track of their parent IDs in case they are soft-deleted and need to be restored
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'parentId')) {
            $this->addColumn('{{%navigation_nodes}}', 'parentId', $this->integer()->after('navId'));
        }


        // Delete old columns
        if ($this->db->columnExists('{{%navigation_navs}}', 'isArchived')) {
            $this->dropColumn('{{%navigation_navs}}', 'isArchived');
        }

        if ($this->db->columnExists('{{%navigation_navs}}', 'dateArchived')) {
            $this->dropColumn('{{%navigation_navs}}', 'dateArchived');
        }

        if (MigrationHelper::doesIndexExist('{{%navigation_navs}}', 'isArchived')) {
            MigrationHelper::dropIndexIfExists('{{%navigation_navs}}', ['isArchived'], true, $this);
        }

        return true;
    }

    public function safeDown()
    {
        echo "m190314_000000_soft_deletes cannot be reverted.\n";

        return false;
    }
}
