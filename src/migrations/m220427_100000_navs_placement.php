<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;

use craft\db\Migration;

class m220427_100000_navs_placement extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'siteSettings')) {
            $this->addColumn('{{%navigation_navs}}', 'defaultPlacement', $this->enum('defaultPlacement', [Nav::DEFAULT_PLACEMENT_BEGINNING, Nav::DEFAULT_PLACEMENT_END])->defaultValue('end')->notNull()->after('fieldLayoutId'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m220427_100000_navs_placement cannot be reverted.\n";
        return false;
    }
}

