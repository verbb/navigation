<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m201018_000000_site_settings extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'siteSettings')) {
            $this->addColumn('{{%navigation_navs}}', 'siteSettings', $this->text()->after('permissions'));
        }

        return true;
    }

    public function safeDown()
    {
        echo "m201018_000000_site_settings cannot be reverted.\n";

        return false;
    }
}
