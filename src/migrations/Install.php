<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;

use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

class Install extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    public function safeDown(): bool
    {
        $this->dropForeignKeys();
        $this->dropTables();
        $this->dropProjectConfig();

        return true;
    }

    public function createTables(): void
    {
        $this->archiveTableIfExists('{{%navigation_nodes}}');
        $this->createTable('{{%navigation_nodes}}', [
            'id' => $this->integer()->notNull(),
            'elementId' => $this->integer(),
            'navId' => $this->integer()->notNull(),
            'parentId' => $this->integer(),
            'url' => $this->string(255),
            'type' => $this->string(255),
            'classes' => $this->string(255),
            'urlSuffix' => $this->string(255),
            'customAttributes' => $this->text(),
            'data' => $this->text(),
            'newWindow' => $this->boolean()->defaultValue(false),
            'deletedWithNav' => $this->boolean()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->archiveTableIfExists('{{%navigation_navs}}');
        $this->createTable('{{%navigation_navs}}', [
            'id' => $this->primaryKey(),
            'structureId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'instructions' => $this->text(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'propagationMethod' => $this->string()->defaultValue(Nav::PROPAGATION_METHOD_ALL)->notNull(),
            'maxNodes' => $this->integer(),
            'maxNodesSettings' => $this->text(),
            'permissions' => $this->text(),
            'fieldLayoutId' => $this->integer(),
            'defaultPlacement' => $this->enum('defaultPlacement', [Nav::DEFAULT_PLACEMENT_BEGINNING, Nav::DEFAULT_PLACEMENT_END])->defaultValue('end')->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists('{{%navigation_navs_sites}}');
        $this->createTable('{{%navigation_navs_sites}}', [
            'id' => $this->primaryKey(),
            'navId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'enabled' => $this->boolean()->defaultValue(true)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    public function createIndexes(): void
    {
        $this->createIndex(null, '{{%navigation_nodes}}', ['navId'], false);
        $this->createIndex(null, '{{%navigation_navs}}', ['handle'], false);
        $this->createIndex(null, '{{%navigation_navs}}', ['structureId'], false);
        $this->createIndex(null, '{{%navigation_navs}}', ['fieldLayoutId'], false);
        $this->createIndex(null, '{{%navigation_navs}}', ['dateDeleted'], false);
        $this->createIndex(null, '{{%navigation_navs_sites}}', ['navId', 'siteId'], true);
        $this->createIndex(null, '{{%navigation_navs_sites}}', ['siteId'], false);
    }

    public function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['navId'], '{{%navigation_navs}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['elementId'], '{{%elements}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_navs}}', ['structureId'], '{{%structures}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_navs}}', ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%navigation_navs_sites}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%navigation_navs_sites}}', ['navId'], '{{%navigation_navs}}', ['id'], 'CASCADE', null);
    }

    public function dropTables(): void
    {
        $this->dropTableIfExists('{{%navigation_nodes}}');
        $this->dropTableIfExists('{{%navigation_navs}}');
        $this->dropTableIfExists('{{%navigation_navs_sites}}');
    }

    public function dropForeignKeys(): void
    {
        if ($this->db->tableExists('{{%navigation_nodes}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_nodes}}', $this);
        }

        if ($this->db->tableExists('{{%navigation_navs}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_navs}}', $this);
        }

        if ($this->db->tableExists('{{%navigation_navs_sites}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_navs_sites}}', $this);
        }
    }

    public function dropProjectConfig(): void
    {
        Craft::$app->projectConfig->remove('navigation');
    }
}
