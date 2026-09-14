<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;

class m260912_000000_build_session_revision extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_build_sessions}}', 'structureRevision')) {
            // Existing drafts have no provable baseline; null must not certify their ordering.
            $this->addColumn('{{%navigation_build_sessions}}', 'structureRevision', $this->string(64)->null());
        }

        return true;
    }

    public function safeDown(): bool
    {
        return false;
    }
}
