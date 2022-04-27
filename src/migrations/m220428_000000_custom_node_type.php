<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;
use verbb\navigation\nodetypes\CustomType;

use Craft;
use craft\db\Query;
use craft\db\Migration;
use craft\helpers\ArrayHelper;

class m220428_000000_custom_node_type extends Migration
{
    public function safeUp(): bool
    {
        $nodes = (new Query())
            ->select(['*'])
            ->from('{{%navigation_nodes}}')
            ->all($this->db);

        foreach ($nodes as $node) {
            if ($node['type'] === '' || $node['type'] === 'custom' || $node['type'] === null) {
                $this->update('{{%navigation_nodes}}', [
                    'type' => CustomType::class,
                ], [
                    'id' => $node['id'],
                ]);
            }
        }

        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.navigation.schemaVersion', true);

        if (version_compare($schemaVersion, '2.0.2', '<')) {
            $navs = $projectConfig->get('navigation.navs') ?? [];

            foreach ($navs as $navUid => $nav) {
                $permissions = $nav['permissions'] ?? [];

                if ($permissions) {
                    $permissions[CustomType::class] = ArrayHelper::remove($permissions, 'custom');

                    $projectConfig->set("navigation.navs.$navUid.permissions", $permissions);
                }
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m220428_000000_custom_node_type cannot be reverted.\n";
        return false;
    }
}

