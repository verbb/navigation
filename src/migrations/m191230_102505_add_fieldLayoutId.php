<?php

namespace verbb\navigation\migrations;

use Craft;
use craft\db\Migration;

/**
 * m191230_102505_add_fieldLayoutId migration.
 */
class m191230_102505_add_fieldLayoutId extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'fieldLayoutId')) {
            $this->addColumn('{{%navigation_navs}}', 'fieldLayoutId', $this->integer()->after('propagateNodes'));
            $this->createIndex(null, '{{%navigation_navs}}', ['fieldLayoutId'], false);
            $this->addForeignKey(null, '{{%navigation_navs}}', ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191230_102505_add_fieldLayoutId cannot be reverted.\n";
        return false;
    }
}
