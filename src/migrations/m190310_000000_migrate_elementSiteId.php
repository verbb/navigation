<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Db;

class m190310_000000_migrate_elementSiteId extends Migration
{
    public function safeUp(): bool
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

            Db::dropForeignKeyIfExists('{{%navigation_nodes}}', ['elementSiteId'], $this);
            Db::dropIndexIfExists('{{%navigation_nodes}}', ['elementSiteId'], true, $this);

            $this->dropColumn('{{%navigation_nodes}}', 'elementSiteId');
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m190310_000000_migrate_elementSiteId cannot be reverted.\n";

        return false;
    }
}
