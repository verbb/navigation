<?php
namespace verbb\navigation\migrations;

use verbb\navigation\helpers\ProjectConfigData;

use Craft;
use craft\db\Migration;
use craft\db\Query;

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

        // Craft stores one permission name per row in userpermissions.name (not JSON blobs).
        if ($this->db->tableExists('{{%userpermissions}}') && $this->db->columnExists('{{%userpermissions}}', 'name')) {
            foreach ($replacements as $search => $replace) {
                if (str_ends_with($search, ':')) {
                    $rows = (new Query())
                        ->select(['id', 'name'])
                        ->from(['{{%userpermissions}}'])
                        ->where(['like', 'name', $search . '%', false])
                        ->all($this->db);

                    foreach ($rows as $row) {
                        $newName = str_replace($search, $replace, (string)$row['name']);

                        if ($newName !== $row['name']) {
                            $this->update('{{%userpermissions}}', [
                                'name' => $newName,
                            ], [
                                'id' => $row['id'],
                            ], [], false);
                        }
                    }
                } else {
                    $this->update(
                        '{{%userpermissions}}',
                        ['name' => $replace],
                        ['name' => $search],
                        [],
                        false,
                    );
                }
            }
        }

        // Project Config user group permission lists (deployed / allowAdminChanges=false).
        $projectConfig = Craft::$app->getProjectConfig();
        $groups = $projectConfig->get('users.groups') ?? [];

        foreach ($groups as $uid => $group) {
            $permissions = $group['permissions'] ?? null;

            if (!is_array($permissions) || $permissions === []) {
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
                $projectConfig->set(
                    'users.groups.' . $uid . '.permissions',
                    array_values(array_unique($newPermissions)),
                    'Rename Navigation menu permissions for 4.x',
                );
            }
        }
    }
}
