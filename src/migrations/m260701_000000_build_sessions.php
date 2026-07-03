<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m260701_000000_build_sessions extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if ($this->db->tableExists('{{%navigation_build_sessions}}')) {
            return true;
        }

        $this->createTable('{{%navigation_build_sessions}}', [
            'id' => $this->primaryKey(),
            'menuId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'structureMoves' => $this->text()->null(),
            'addedNodeIds' => $this->text()->null(),
            'stagedDeletes' => $this->text()->null(),
            'menuDraftId' => $this->integer()->null(),
            'menuContentDraft' => $this->text()->null(),
            'nodeDraftMap' => $this->text()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%navigation_build_sessions}}', ['menuId', 'siteId', 'userId'], true);
        $this->createIndex(null, '{{%navigation_build_sessions}}', ['siteId'], false);
        $this->createIndex(null, '{{%navigation_build_sessions}}', ['userId'], false);
        $this->addForeignKey(null, '{{%navigation_build_sessions}}', ['menuId'], '{{%navigation_menus}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_build_sessions}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%navigation_build_sessions}}', ['userId'], '{{%users}}', ['id'], 'CASCADE', null);

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%navigation_build_sessions}}');

        return true;
    }
}
