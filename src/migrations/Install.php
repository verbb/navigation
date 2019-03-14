<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;

use Craft;
use craft\db\Migration;

class Install extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        // See if we should migrate from A&M Nav
        // $migration = new AmNavPlugin();
        // $migration->safeUp();
    }

    public function safeDown()
    {
        $this->removeTables();
    }

    public function createTables()
    {
        $this->createTable('{{%navigation_nodes}}', [
            'id' => $this->integer()->notNull(),
            'elementId' => $this->integer(),
            'navId' => $this->integer()->notNull(),
            'parentId' => $this->integer(),
            'url' => $this->string(255),
            'type' => $this->string(255),
            'classes' => $this->string(255),
            'newWindow' => $this->boolean()->defaultValue(false),
            'deletedWithNav' => $this->boolean()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createTable('{{%navigation_navs}}', [
            'id' => $this->primaryKey(),
            'structureId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'instructions' => $this->text(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'propagateNodes' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);
    }

    public function createIndexes()
    {
        $this->createIndex(null, '{{%navigation_nodes}}', ['navId'], false);
        $this->createIndex(null, '{{%navigation_navs}}', ['handle'], true);
        $this->createIndex(null, '{{%navigation_navs}}', ['structureId'], false);
        $this->createIndex(null, '{{%navigation_navs}}', ['dateDeleted'], false);
    }

    public function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['navId'], '{{%navigation_navs}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['elementId'], '{{%elements}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_navs}}', ['structureId'], '{{%structures}}', ['id'], 'CASCADE', null);
    }

    public function removeTables()
    {
        $this->dropTableIfExists('{{%navigation_nodes}}');
        $this->dropTableIfExists('{{%navigation_navs}}');
    }
}
