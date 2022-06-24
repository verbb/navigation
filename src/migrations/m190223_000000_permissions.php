<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m190223_000000_permissions extends Migration
{
    public function safeUp()
    {
        $navs = (new Query())
            ->select(['*'])
            ->from(['{{%navigation_navs}}'])
            ->all();

        $navPermissions = [];
        $permissionIds = [];

        // Update permissions
        foreach ($navs as $nav) {
            $this->insert('{{%userpermissions}}', ['name' => 'navigation-managenav:' . $nav['uid']]);
            $permissionIds[] = $this->db->getLastInsertID('{{%userpermissions}}');
        }

        // See which users & groups already have the "accessplugin-navigation" permission
        $userIds = (new Query())
            ->select(['up_u.userId'])
            ->from(['{{%userpermissions_users}} up_u'])
            ->innerJoin('{{%userpermissions}} up', '[[up.id]] = [[up_u.permissionId]]')
            ->where(['up.name' => 'accessplugin-navigation'])
            ->column($this->db);

        $groupIds = (new Query())
            ->select(['up_ug.groupId'])
            ->from(['{{%userpermissions_usergroups}} up_ug'])
            ->innerJoin('{{%userpermissions}} up', '[[up.id]] = [[up_ug.permissionId]]')
            ->where(['up.name' => 'accessplugin-navigation'])
            ->column($this->db);

        if (empty($userIds) && empty($groupIds)) {
            return;
        }

        // Assign the new permissions to the users
        if (!empty($userIds)) {
            $data = [];

            foreach ($userIds as $userId) {
                foreach ($permissionIds as $permissionId) {
                    $data[] = [$permissionId, $userId];
                }
            }

            $this->batchInsert('{{%userpermissions_users}}', ['permissionId', 'userId'], $data);
        }

        // Assign the new permissions to the groups
        if (!empty($groupIds)) {
            $data = [];

            foreach ($groupIds as $groupId) {
                foreach ($permissionIds as $permissionId) {
                    $data[] = [$permissionId, $groupId];
                }
            }

            $this->batchInsert('{{%userpermissions_usergroups}}', ['permissionId', 'groupId'], $data);
        }

        return true;
    }

    public function safeDown()
    {
        echo "m190223_000000_permissions cannot be reverted.\n";

        return false;
    }
}
