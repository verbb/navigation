<?php
namespace verbb\navigation\migrations;

use Craft;
use craft\base\Field;
use craft\db\Migration;

class m260703_000000_menu_title_translation extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%navigation_menus}}', 'titleTranslationMethod')) {
            $this->addColumn(
                '{{%navigation_menus}}',
                'titleTranslationMethod',
                $this->string()->notNull()->defaultValue(Field::TRANSLATION_METHOD_SITE)->after('propagationMethod'),
            );
        }

        if (!$this->db->columnExists('{{%navigation_menus}}', 'titleTranslationKeyFormat')) {
            $this->addColumn(
                '{{%navigation_menus}}',
                'titleTranslationKeyFormat',
                $this->string()->null()->after('titleTranslationMethod'),
            );
        }

        $this->_migrateProjectConfig();

        return true;
    }

    public function safeDown(): bool
    {
        if ($this->db->columnExists('{{%navigation_menus}}', 'titleTranslationKeyFormat')) {
            $this->dropColumn('{{%navigation_menus}}', 'titleTranslationKeyFormat');
        }

        if ($this->db->columnExists('{{%navigation_menus}}', 'titleTranslationMethod')) {
            $this->dropColumn('{{%navigation_menus}}', 'titleTranslationMethod');
        }

        return true;
    }


    // Private Methods
    // =========================================================================

    private function _migrateProjectConfig(): void
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $menus = $projectConfig->get('navigation.menus');

        if (!is_array($menus)) {
            return;
        }

        $projectConfig->muteEvents = true;

        foreach ($menus as $menuUid => $menu) {
            if (!is_array($menu) || isset($menu['titleTranslationMethod'])) {
                continue;
            }

            $menu['titleTranslationMethod'] = Field::TRANSLATION_METHOD_SITE;
            $projectConfig->set('navigation.menus.' . $menuUid, $menu, 'Add default title translation method');
        }

        $projectConfig->muteEvents = false;
    }
}
