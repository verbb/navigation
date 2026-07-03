<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Menu;

use craft\db\Migration;

class m260631_000000_clear_stale_menu_element_field_layout extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $table = $this->db->tableExists('{{%navigation_menus}}')
            ? '{{%navigation_menus}}'
            : '{{%navigation_navs}}';

        if (!$this->db->columnExists($table, 'menuFieldLayoutId')) {
            return true;
        }

        // Menu content fields live on menuFieldLayoutId only. elements.fieldLayoutId can
        // retain a stale layout after menu fields are removed, which incorrectly surfaced
        // builder tabs when getFieldLayout() fell back to it.
        $this->update(
            '{{%elements}}',
            ['fieldLayoutId' => null],
            [
                'and',
                ['type' => Menu::class],
                ['not', ['fieldLayoutId' => null]],
                [
                    'id' => (new \craft\db\Query())
                        ->select('id')
                        ->from([$table])
                        ->where(['menuFieldLayoutId' => null]),
                ],
            ],
        );

        return true;
    }

    public function safeDown(): bool
    {
        return true;
    }
}
