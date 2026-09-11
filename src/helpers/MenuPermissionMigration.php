<?php
namespace verbb\navigation\helpers;

use Craft;
use craft\db\Query;

/** Migrates stored grants, preserving user/group assignments when names collide. */
class MenuPermissionMigration
{
    // Static Methods
    // =========================================================================

    public static function rename(string $name): string
    {
        $normalized = strtolower($name);

        foreach ([
            'navigation-createnavs' => 'navigation-createmenus',
            'navigation-managenav:' => 'navigation-managemenu:',
            'navigation-editnav:' => 'navigation-editmenu:',
            'navigation-deletenav:' => 'navigation-deletemenu:',
        ] as $old => $new) {
            if ($normalized === $old || (str_ends_with($old, ':') && str_starts_with($normalized, $old))) {
                return $new . substr($normalized, strlen($old));
            }
        }

        return $name;
    }

    public static function migrate(): void
    {
        $db = Craft::$app->getDb();
        foreach ((new Query())->from('{{%userpermissions}}')->all($db) as $row) {
            $name = self::rename($row['name']);
            if ($name === $row['name']) {
                continue;
            }
            $target = (new Query())->from('{{%userpermissions}}')->where(['name' => $name])->one($db);
            if ($target && (int)$target['id'] !== (int)$row['id']) {
                foreach (['{{%userpermissions_users}}' => 'userId', '{{%userpermissions_usergroups}}' => 'groupId'] as $table => $ownerColumn) {
                    foreach ((new Query())->from($table)->where(['permissionId' => $row['id']])->all($db) as $assignment) {
                        $exists = (new Query())->from($table)->where([
                            'permissionId' => $target['id'],
                            $ownerColumn => $assignment[$ownerColumn],
                        ])->exists($db);

                        if ($exists) {
                            $db->createCommand()->delete($table, ['id' => $assignment['id']])->execute();
                        } else {
                            $db->createCommand()->update($table, ['permissionId' => $target['id']], ['id' => $assignment['id']])->execute();
                        }
                    }
                }
                $db->createCommand()->delete('{{%userpermissions}}', ['id' => $row['id']])->execute();
            } else {
                $db->createCommand()->update('{{%userpermissions}}', ['name' => $name], ['id' => $row['id']])->execute();
            }
        }

        // This is a schema migration, including on installations whose CP disallows
        // configuration edits. Restore that policy immediately after updating grants.
        $config = Craft::$app->getProjectConfig();
        $readOnly = $config->readOnly;
        $config->readOnly = false;

        try {
            foreach (($config->get('users.groups') ?? []) as $uid => $group) {
                if (!is_array($group['permissions'] ?? null)) {
                    continue;
                }

                $permissions = array_values(array_unique(array_map(self::rename(...), $group['permissions'])));

                if ($permissions !== $group['permissions']) {
                    $config->set('users.groups.' . $uid . '.permissions', $permissions, 'Migrate Navigation menu grants');
                }
            }
        } finally {
            $config->readOnly = $readOnly;
        }
    }
}
