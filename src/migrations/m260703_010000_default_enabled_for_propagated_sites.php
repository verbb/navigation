<?php
namespace verbb\navigation\migrations;

use Craft;
use craft\db\Migration;

class m260703_010000_default_enabled_for_propagated_sites extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_menus}}', 'defaultEnabledForPropagatedSites')) {
            $this->addColumn(
                '{{%navigation_menus}}',
                'defaultEnabledForPropagatedSites',
                $this->boolean()->notNull()->defaultValue(true)->after('titleTranslationKeyFormat'),
            );
        }

        $this->_migrateProjectConfig();

        return true;
    }

    public function safeDown(): bool
    {
        if ($this->db->columnExists('{{%navigation_menus}}', 'defaultEnabledForPropagatedSites')) {
            $this->dropColumn('{{%navigation_menus}}', 'defaultEnabledForPropagatedSites');
        }

        return true;
    }


    // Private Methods
    // =========================================================================

    private function _migrateProjectConfig(): void
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $menus = $projectConfig->get('navigation.menus');

        if (!is_array($menus)) {
            return;
        }

        $projectConfig->muteEvents = true;

        foreach ($menus as $menuUid => $menu) {
            if (!is_array($menu) || array_key_exists('defaultEnabledForPropagatedSites', $menu)) {
                continue;
            }

            $menu['defaultEnabledForPropagatedSites'] = true;
            $projectConfig->set('navigation.menus.' . $menuUid, $menu, 'Add default enabled status for propagated nodes');
        }

        $projectConfig->muteEvents = false;
    }
}
