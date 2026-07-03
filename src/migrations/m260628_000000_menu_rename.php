<?php
namespace verbb\navigation\migrations;

use verbb\navigation\helpers\ProjectConfigData;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class m260628_000000_menu_rename extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if ($this->db->tableExists('{{%navigation_navs}}')) {
            $this->renameTable('{{%navigation_navs}}', '{{%navigation_menus}}');
        }

        if ($this->db->tableExists('{{%navigation_navs_sites}}')) {
            $this->renameTable('{{%navigation_navs_sites}}', '{{%navigation_menus_sites}}');
        }

        if ($this->db->tableExists('{{%navigation_nodes}}') && $this->db->columnExists('{{%navigation_nodes}}', 'navId')) {
            $this->renameColumn('{{%navigation_nodes}}', 'navId', 'menuId');
        }

        if ($this->db->tableExists('{{%navigation_nodes}}') && $this->db->columnExists('{{%navigation_nodes}}', 'deletedWithNav')) {
            $this->renameColumn('{{%navigation_nodes}}', 'deletedWithNav', 'deletedWithMenu');
        }

        if ($this->db->tableExists('{{%navigation_menus_sites}}') && $this->db->columnExists('{{%navigation_menus_sites}}', 'navId')) {
            $this->renameColumn('{{%navigation_menus_sites}}', 'navId', 'menuId');
        }

        $this->_migrateProjectConfig();
        $this->_migrateUserPermissions();

        return true;
    }

    public function safeDown(): bool
    {
        echo "m260628_000000_menu_rename cannot be reverted.\n";

        return false;
    }


    // Private Methods
    // =========================================================================

    private function _migrateProjectConfig(): void
    {
        if (!ProjectConfigData::canUpdateProjectConfig()) {
            return;
        }

        $projectConfig = Craft::$app->getProjectConfig();

        if (!$projectConfig->get('navigation.navs')) {
            return;
        }

        $navs = $projectConfig->get('navigation.navs');
        $projectConfig->set('navigation.menus', $navs, 'Rename navigation.navs to navigation.menus');
        $projectConfig->remove('navigation.navs');
    }

    private function _migrateUserPermissions(): void
    {
        $replacements = [
            'navigation-createNavs' => 'navigation-createMenus',
            'navigation-manageNav:' => 'navigation-manageMenu:',
            'navigation-editNav:' => 'navigation-editMenu:',
            'navigation-deleteNav:' => 'navigation-deleteMenu:',
        ];

        foreach (['{{%userpermissions}}', '{{%usergroups}}'] as $table) {
            if (!$this->db->tableExists($table) || !$this->db->columnExists($table, 'permissions')) {
                continue;
            }

            $rows = (new Query())
                ->select(['id', 'permissions'])
                ->from([$table])
                ->all($this->db);

            foreach ($rows as $row) {
                if (empty($row['permissions'])) {
                    continue;
                }

                $permissions = Json::decode($row['permissions']);
                if (!is_array($permissions)) {
                    continue;
                }

                $changed = false;
                $newPermissions = [];

                foreach ($permissions as $permission) {
                    $newPermission = (string)$permission;

                    foreach ($replacements as $search => $replace) {
                        if ($search === $newPermission || str_starts_with($newPermission, $search)) {
                            $updated = str_replace($search, $replace, $newPermission);
                            if ($updated !== $newPermission) {
                                $newPermission = $updated;
                                $changed = true;
                            }
                        }
                    }

                    $newPermissions[] = $newPermission;
                }

                if ($changed) {
                    $this->update($table, [
                        'permissions' => Json::encode(array_values(array_unique($newPermissions))),
                    ], [
                        'id' => $row['id'],
                    ], [], false);
                }
            }
        }
    }
}
