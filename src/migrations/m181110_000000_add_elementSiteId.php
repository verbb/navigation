<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;

use Craft;
use craft\db\Migration;

class m181110_000000_add_elementSiteId extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%navigation_nodes}}', 'elementSiteId')) {
            $this->addColumn('{{%navigation_nodes}}', 'elementSiteId', $this->integer()->after('elementId'));
            $this->createIndex(null, '{{%navigation_nodes}}', ['elementSiteId'], false);
            $this->addForeignKey(null, '{{%navigation_nodes}}', ['elementSiteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');

            // Populate it
            $nodes = Node::find()->all();

            foreach ($nodes as $node) {
                if ($node->siteId) {
                    $node->elementSiteId = $node->siteId;
                } else {
                    $node->elementSiteId = Craft::$app->getSites()->getPrimarySite()->id;
                }

                Craft::$app->getElements()->saveElement($node, true, false);
            }
        }

        return true;
    }

    public function safeDown()
    {
        echo "m181110_000000_add_elementSiteId cannot be reverted.\n";

        return false;
    }
}
