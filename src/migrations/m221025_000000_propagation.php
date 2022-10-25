<?php
namespace verbb\navigation\migrations;

use verbb\navigation\models\Nav;
use verbb\navigation\nodetypes\CustomType;

use Craft;
use craft\db\Query;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;

class m221025_000000_propagation extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_navs}}', 'propagationMethod')) {
            $this->addColumn('{{%navigation_navs}}', 'propagationMethod', $this->string()->defaultValue(Nav::PROPAGATION_METHOD_ALL)->notNull()->after('propagateNodes'));
        }

        $navs = (new Query())
            ->select(['*'])
            ->from('{{%navigation_navs}}')
            ->all($this->db);

        foreach ($navs as $nav) {
            if (array_key_exists('propagateNodes', $nav)) {
                if ($nav['propagateNodes']) {
                    Db::update('{{%navigation_navs}}', ['propagationMethod' => 'all'], ['id' => $nav['id']]);
                } else {
                    Db::update('{{%navigation_navs}}', ['propagationMethod' => 'none'], ['id' => $nav['id']]);
                }
            }
        }

        if ($this->db->columnExists('{{%navigation_navs}}', 'propagateNodes')) {
            $this->dropColumn('{{%navigation_navs}}', 'propagateNodes');
        }

        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.navigation.schemaVersion', true);

        if (version_compare($schemaVersion, '2.0.5', '<')) {
            $navs = $projectConfig->get('navigation.navs') ?? [];

            foreach ($navs as $navUid => $nav) {
                if (array_key_exists('propagateNodes', $nav)) {
                    $propagateNodes = ArrayHelper::remove($nav, 'propagateNodes');

                    $nav['propagationMethod'] = ($propagateNodes) ? 'all' : 'none';

                    $projectConfig->set("navigation.navs.$navUid", $nav);
                }
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m221025_000000_propagation cannot be reverted.\n";
        return false;
    }
}

