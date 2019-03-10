<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\fields\NavigationField;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\MigrationHelper;

class m190310_000000_migrate_elementSiteId extends Migration
{
    public function safeUp()
    {
        if ($this->db->columnExists('{{%navigation_nodes}}', 'elementSiteId')) {
            $rows = (new Query())
                ->select(['id', 'elementSiteId'])
                ->from(['{{%navigation_nodes}}'])
                ->where(['IS NOT', 'elementSiteId', null])
                ->all();

            foreach ($rows as $row) {
                $this->update('{{%elements_sites}}', ['slug' => $row['elementSiteId']], ['id' => $row['id']], [], false);
            }

            MigrationHelper::dropForeignKeyIfExists('{{%navigation_nodes}}', ['elementSiteId'], $this);
            MigrationHelper::dropIndexIfExists('{{%navigation_nodes}}', ['elementSiteId'], true, $this);

            $this->dropColumn('{{%navigation_nodes}}', 'elementSiteId');
        }

        return true;
    }

    public function safeDown()
    {
        echo "m190310_000000_migrate_elementSiteId cannot be reverted.\n";

        return false;
    }
}
