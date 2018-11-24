<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;

class m181123_000000_populate_elementSiteIds extends Migration
{
    public function safeUp()
    {
        // Populate it
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $nodes = Node::find()->siteId($site->id)->status(null)->all();

            foreach ($nodes as $node) {
                $record = NodeRecord::findOne($node->id);

                if (!$record) {
                    continue;
                }

                if ($node->siteId) {
                    $record->elementSiteId = $node->siteId;
                } else {
                    $record->elementSiteId = Craft::$app->getSites()->getPrimarySite()->id;
                }

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
