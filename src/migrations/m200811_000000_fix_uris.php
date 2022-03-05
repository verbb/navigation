<?php
namespace verbb\navigation\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;

class m200811_000000_fix_uris extends Migration
{
    public function safeUp(): bool
    {
        $nodeIds = (new Query())
            ->select(['id'])
            ->from(['{{%navigation_nodes}} nodes'])
            ->column();

        foreach ($nodeIds as $nodeId) {
            echo 'Reset URI for ' . $nodeId . PHP_EOL;

            $this->update(Table::ELEMENTS_SITES, ['uri' => ''], ['id' => $nodeId]);
        }
    
        return true;
    }

    public function safeDown(): bool
    {
        echo "m200811_000000_fix_uris cannot be reverted.\n";

        return false;
    }
}
