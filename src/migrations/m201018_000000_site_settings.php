<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m201018_000000_site_settings extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'siteSettings')) {
            $this->addColumn('{{%navigation_navs}}', 'siteSettings', $this->text()->after('permissions'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m201018_000000_site_settings cannot be reverted.\n";

        return false;
    }
}
