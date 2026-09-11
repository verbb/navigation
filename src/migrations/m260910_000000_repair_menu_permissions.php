<?php
namespace verbb\navigation\migrations;

use verbb\navigation\helpers\MenuPermissionMigration;

use craft\db\Migration;

class m260910_000000_repair_menu_permissions extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        MenuPermissionMigration::migrate();

        return true;
    }

    public function safeDown(): bool
    {
        return false;
    }
}
