<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;

class m200810_000000_fix_elementsiteid extends Migration
{
    public function safeUp(): bool
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

        // Check each rows slug. If it matches a site ID, it's fine, if not, it's a reference to the column ID for elements_sites
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
                    Navigation::error('Invalid site info ' . Json::encode($row));
                }
            }
        }
    
        return true;
    }

    public function safeDown(): bool
    {
        echo "m200810_000000_fix_elementsiteid cannot be reverted.\n";

        return false;
    }
}
