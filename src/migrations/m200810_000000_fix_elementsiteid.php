<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\records\Node as NodeRecord;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;

class m200810_000000_fix_elementsiteid extends Migration
{
    public function safeUp()
    {
        // Check if the Craft 3.5 update has messed up the slug, which we use to store the `elementSiteId`
        $siteIds = (new Query())
            ->select(['id'])
            ->from([Table::SITES])
            ->column();

        $rows = (new Query())
            ->select(['elements_sites.id', 'slug'])
            ->from(['{{%navigation_nodes}} nodes'])
            ->leftJoin(['elements_sites' => Table::ELEMENTS_SITES], '[[elements_sites.elementId]] = [[nodes.id]]')
            ->all();

        // Check each rows slug. If it matches a site ID, its fine, if not, it's a reference to the column ID for elements_sites
        foreach ($rows as $row) {
            if (!$row['slug']) {
                continue;
            }

            if (!in_array($row['slug'], $siteIds)) {
                $siteId = (new Query())
                    ->select(['siteId'])
                    ->from([Table::ELEMENTS_SITES])
                    ->where(['id' => $row['slug']])
                    ->scalar();

                if ($siteId) {
                    echo 'Fixing ID ' . $row['slug'] . ' > ' . $siteId . PHP_EOL;

                    $this->update(Table::ELEMENTS_SITES, ['slug' => $siteId], ['id' => $row['id']]);
                } else {
                    Navigation::error('Invalid site info ' . json_encode($row));
                }
            }
        }

        return true;
    }

    public function safeDown()
    {
        echo "m200810_000000_fix_elementsiteid cannot be reverted.\n";

        return false;
    }
}
