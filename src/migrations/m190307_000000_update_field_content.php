<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\fields\NavigationField;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

class m190307_000000_update_field_content extends Migration
{
    public function safeUp(): bool
    {
        $navFields = (new Query())
            ->select(['handle'])
            ->from(['{{%fields}}'])
            ->where(['type' => NavigationField::class])
            ->all();

        foreach ($navFields as $navField) {
            $column = 'field_' . $navField['handle'];

            if (!$this->db->columnExists('{{%content}}', $column)) {
                continue;
            }

            $navContents = (new Query())
                ->select(['id', $column])
                ->from(['{{%content}}'])
                ->where(['not', [$column => null]])
                ->all();

            foreach ($navContents as $navContent) {
                $id = $navContent['id'];
                $content = $navContent[$column];

                // Check if this was the ID of the nav
                $nav = Navigation::$plugin->getNavs()->getNavById($content);

                if ($nav) {
                    $value = $nav->handle;
                } else {
                    // This could also be the JSON object for the nav - we don't want that
                    if (is_string($content) && !empty($content)) {
                        $json = Json::decodeIfJson($content);

                        if ($json && isset($json['handle'])) {
                            $value = $json['handle'];
                        }
                    }
                }

                $this->update('{{%content}}', [$column => $value], ['id' => $id], [], false);
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m190307_000000_update_field_content cannot be reverted.\n";

        return false;
    }
}
