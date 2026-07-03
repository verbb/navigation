<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\MenuSettings;

use Craft;
use craft\base\Field;
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
        $this->dropProjectConfig();
        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }

    public function createTables(): void
    {
        $this->archiveTableIfExists('{{%navigation_nodes}}');
        $this->createTable('{{%navigation_nodes}}', [
            'id' => $this->integer()->notNull(),
            'elementId' => $this->integer(),
            'menuId' => $this->integer()->notNull(),
            'parentId' => $this->integer(),
            'url' => $this->text(),
            'type' => $this->string(255),
            'classes' => $this->string(255),
            'urlSuffix' => $this->string(255),
            'customAttributes' => $this->text(),
            'data' => $this->text(),
            'newWindow' => $this->boolean()->defaultValue(false),
            'deletedWithMenu' => $this->boolean()->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->archiveTableIfExists('{{%navigation_menus}}');
        $this->createTable('{{%navigation_menus}}', [
            'id' => $this->primaryKey(),
            'structureId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'instructions' => $this->text(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'propagationMethod' => $this->string()->defaultValue(MenuSettings::PROPAGATION_METHOD_ALL)->notNull(),
            'titleTranslationMethod' => $this->string()->notNull()->defaultValue(Field::TRANSLATION_METHOD_SITE),
            'titleTranslationKeyFormat' => $this->string()->null(),
            'maxNodes' => $this->integer(),
            'maxNodesSettings' => $this->text(),
            'permissions' => $this->text(),
            'fieldLayoutId' => $this->integer(),
            'menuFieldLayoutId' => $this->integer(),
            'defaultPlacement' => $this->enum('defaultPlacement', [MenuSettings::DEFAULT_PLACEMENT_BEGINNING, MenuSettings::DEFAULT_PLACEMENT_END])->defaultValue('end')->notNull(),
            'showSiteMenu' => $this->boolean()->defaultValue(true),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateDeleted' => $this->dateTime()->null(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists('{{%navigation_menus_sites}}');
        $this->createTable('{{%navigation_menus_sites}}', [
            'id' => $this->primaryKey(),
            'menuId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'enabled' => $this->boolean()->defaultValue(true)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists('{{%navigation_nodes_sites}}');
        $this->createTable('{{%navigation_nodes_sites}}', [
            'id' => $this->primaryKey(),
            'nodeId' => $this->integer()->notNull(),
            'siteId' => $this->integer()->notNull(),
            'linkedElementSiteId' => $this->integer()->null(),
            'url' => $this->text()->null(),
            'urlSuffix' => $this->string(255)->null(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->archiveTableIfExists('{{%navigation_build_sessions}}');
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
    }

    public function createIndexes(): void
    {
        $this->createIndex(null, '{{%navigation_nodes}}', ['menuId'], false);
        $this->createIndex(null, '{{%navigation_menus}}', ['handle'], false);
        $this->createIndex(null, '{{%navigation_menus}}', ['structureId'], false);
        $this->createIndex(null, '{{%navigation_menus}}', ['fieldLayoutId'], false);
        $this->createIndex(null, '{{%navigation_menus}}', ['menuFieldLayoutId'], false);
        $this->createIndex(null, '{{%navigation_menus}}', ['dateDeleted'], false);
        $this->createIndex(null, '{{%navigation_menus_sites}}', ['menuId', 'siteId'], true);
        $this->createIndex(null, '{{%navigation_menus_sites}}', ['siteId'], false);
        $this->createIndex(null, '{{%navigation_nodes_sites}}', ['nodeId', 'siteId'], true);
        $this->createIndex(null, '{{%navigation_nodes_sites}}', ['siteId'], false);
        $this->createIndex(null, '{{%navigation_build_sessions}}', ['menuId', 'siteId', 'userId'], true);
        $this->createIndex(null, '{{%navigation_build_sessions}}', ['siteId'], false);
        $this->createIndex(null, '{{%navigation_build_sessions}}', ['userId'], false);
    }

    public function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['menuId'], '{{%navigation_menus}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['elementId'], '{{%elements}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%navigation_nodes}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_menus}}', ['structureId'], '{{%structures}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_menus}}', ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%navigation_menus}}', ['menuFieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%navigation_menus_sites}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%navigation_menus_sites}}', ['menuId'], '{{%navigation_menus}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_nodes_sites}}', ['nodeId'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_nodes_sites}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%navigation_build_sessions}}', ['menuId'], '{{%navigation_menus}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%navigation_build_sessions}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        $this->addForeignKey(null, '{{%navigation_build_sessions}}', ['userId'], '{{%users}}', ['id'], 'CASCADE', null);
    }

    public function dropTables(): void
    {
        $this->dropTableIfExists('{{%navigation_nodes}}');
        $this->dropTableIfExists('{{%navigation_nodes_sites}}');
        $this->dropTableIfExists('{{%navigation_build_sessions}}');
        $this->dropTableIfExists('{{%navigation_menus}}');
        $this->dropTableIfExists('{{%navigation_menus_sites}}');
    }

    public function dropForeignKeys(): void
    {
        if ($this->db->tableExists('{{%navigation_nodes}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_nodes}}', $this);
        }

        if ($this->db->tableExists('{{%navigation_nodes_sites}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_nodes_sites}}', $this);
        }

        if ($this->db->tableExists('{{%navigation_build_sessions}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_build_sessions}}', $this);
        }

        if ($this->db->tableExists('{{%navigation_menus}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_menus}}', $this);
        }

        if ($this->db->tableExists('{{%navigation_menus_sites}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%navigation_menus_sites}}', $this);
        }
    }

    public function dropProjectConfig(): void
    {
        Craft::$app->getProjectConfig()->remove('navigation');
    }
}
