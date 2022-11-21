<?php
namespace verbb\navigation\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\Json;

class m220427_000000_navs_site_settings extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->tableExists('{{%navigation_navs_sites}}')) {
            $this->createTable('{{%navigation_navs_sites}}', [
                'id' => $this->primaryKey(),
                'navId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->notNull(),
                'enabled' => $this->boolean()->defaultValue(true)->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, '{{%navigation_navs_sites}}', ['navId', 'siteId'], true);
            $this->createIndex(null, '{{%navigation_navs_sites}}', ['siteId'], false);

            $this->addForeignKey(null, '{{%navigation_navs_sites}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
            $this->addForeignKey(null, '{{%navigation_navs_sites}}', ['navId'], '{{%navigation_navs}}', ['id'], 'CASCADE', null);
        }

        $navs = (new Query())
            ->select(['*'])
            ->from('{{%navigation_navs}}')
            ->all($this->db);

        $sites = (new Query())
            ->select(['uid', 'id'])
            ->from('{{%sites}}')
            ->pairs($this->db);

        foreach ($navs as $nav) {
            if (isset($nav['siteSettings'])) {
                $siteSettings = Json::decode($nav['siteSettings']) ?? [];

                foreach ($siteSettings as $siteUid => $enabled) {
                    $siteId = $sites[$siteUid] ?? null;

                    // If a non-multisite install, assume it's enabled
                    if ($enabled === null && !Craft::$app->getIsMultiSite()) {
                        $enabled = true;
                    }

                    if ($siteId) {
                        Db::upsert('{{%navigation_navs_sites}}', [
                            'siteId' => $siteId,
                            'navId' => $nav['id'],
                            'enabled' => (bool)$enabled,
                        ]);
                    }
                }

                if (!$siteSettings) {
                    $this->createDefaultSites($nav);
                }
            } else {
                $this->createDefaultSites($nav);
            }
        }

        if ($this->db->columnExists('{{%navigation_navs}}', 'siteSettings')) {
            $this->dropColumn('{{%navigation_navs}}', 'siteSettings');
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m220427_000000_navs_site_settings cannot be reverted.\n";
        return false;
    }

    private function createDefaultSites($nav)
    {
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            Db::upsert('{{%navigation_navs_sites}}', [
                'siteId' => $site->id,
                'navId' => $nav['id'],
                'enabled' => true,
            ]);
        }
    }
}

