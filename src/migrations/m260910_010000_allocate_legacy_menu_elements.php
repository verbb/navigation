<?php
namespace verbb\navigation\migrations;

use verbb\navigation\helpers\MenuElementCollisionRepair;

use craft\db\Migration;

/** v3 menu IDs were independent of Craft element IDs and commonly overlap users/entries. */
class m260910_010000_allocate_legacy_menu_elements extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        MenuElementCollisionRepair::migrateLegacyMenuIds();

        return true;
    }

    public function safeDown(): bool
    {
        return false;
    }
}
