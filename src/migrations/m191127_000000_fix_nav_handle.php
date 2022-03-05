<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

class m191127_000000_fix_nav_handle extends Migration
{
    public function safeUp(): bool
    {
        // Unique names & handles should no longer be enforced by the DB
        MigrationHelper::dropIndexIfExists('{{%navigation_navs}}', ['handle'], true, $this);
        MigrationHelper::dropIndexIfExists('{{%navigation_navs}}', ['handle'], false, $this);
        $this->createIndex(null, '{{%navigation_navs}}', ['handle'], false);

        return true;
    }

    public function safeDown(): bool
    {
        echo "m191127_000000_fix_nav_handle cannot be reverted.\n";

        return false;
    }
}
