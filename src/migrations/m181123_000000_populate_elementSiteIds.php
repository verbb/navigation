<?php
namespace verbb\navigation\migrations;

use verbb\navigation\records\Node;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m181123_000000_populate_elementSiteIds extends Migration
{
    public function safeUp()
    {
        // Populate it
        $nodes = (new Query())
            ->select(['nodes.id AS nodeId', 'siteId'])
            ->from('{{%navigation_nodes}} nodes')
            ->leftJoin('{{%elements_sites}} elements_sites', '[[elements_sites.elementId]] = [[nodes.elementId]]')
            ->where(['is', 'nodes.elementSiteId', null])
            ->all();

        foreach ($nodes as $node) {
            $record = Node::findOne($node['nodeId']);

            if ($record) {
                $record->elementSiteId = $node['siteId'];
                $record->save(false);
            }
        }
    
        return true;
    }

    public function safeDown()
    {
        echo "m181123_000000_populate_elementSiteIds cannot be reverted.\n";

        return false;
    }
}
