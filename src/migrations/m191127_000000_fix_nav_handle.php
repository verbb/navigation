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

class m191127_000000_fix_nav_handle extends Migration
{
    public function safeUp()
    {
        // Unique names & handles should no longer be enforced by the DB
        MigrationHelper::dropIndexIfExists('{{%navigation_navs}}', ['handle'], true, $this);
        MigrationHelper::dropIndexIfExists('{{%navigation_navs}}', ['handle'], false, $this);
        $this->createIndex(null, '{{%navigation_navs}}', ['handle'], false);

        return true;
    }

    public function safeDown()
    {
        echo "m191127_000000_fix_nav_handle cannot be reverted.\n";

        return false;
    }
}
