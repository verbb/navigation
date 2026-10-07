<?php
namespace verbb\navigation\migrations;

use verbb\navigation\helpers\MenuSiteAssociationRepair;
use verbb\navigation\helpers\ProjectConfigData;

use Craft;
use craft\db\Migration;

class m261007_000000_repair_menu_sites extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $result = MenuSiteAssociationRepair::run();

        if ($result['repairedMenuUids'] && ProjectConfigData::canUpdateProjectConfig()) {
            $projectConfig = Craft::$app->getProjectConfig();
            $menus = $projectConfig->get('navigation.menus');

            if (is_array($menus)) {
                $menus = MenuSiteAssociationRepair::mergeDatabaseSiteSettings($menus, $result['repairedMenuUids']);
                $projectConfig->set('navigation.menus', $menus, 'Repair missing menu site settings');
            }
        }

        if ($result['unresolvedMenus'] > 0) {
            Craft::warning("Unable to safely infer site associations for {$result['unresolvedMenus']} Navigation menus.", __METHOD__);
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m261007_000000_repair_menu_sites cannot be reverted.\n";

        return false;
    }
}
