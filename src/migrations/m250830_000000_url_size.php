<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m250830_000000_url_size extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $this->alterColumn('{{%navigation_nodes}}', 'url', $this->text());

        return true;
    }

    public function safeDown(): bool
    {
        echo "m250830_000000_url_size cannot be reverted.\n";

        return false;
    }
}
