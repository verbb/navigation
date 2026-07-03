<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Menu;
use verbb\navigation\helpers\MenuContentFieldLayout;
use verbb\navigation\helpers\ProjectConfigData;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\fieldlayoutelements\TitleField;

class m260630_000000_strip_menu_title_field extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $this->_stripTitleFromStoredLayouts();
        $this->_stripTitleFromProjectConfig();

        return true;
    }

    public function safeDown(): bool
    {
        return true;
    }


    // Private Methods
    // =========================================================================

    private function _stripTitleFromStoredLayouts(): void
    {
        $table = $this->db->tableExists('{{%navigation_menus}}')
            ? '{{%navigation_menus}}'
            : '{{%navigation_navs}}';

        if (!$this->db->columnExists($table, 'menuFieldLayoutId')) {
            return;
        }

        $layoutIds = (new Query())
            ->select(['menuFieldLayoutId'])
            ->from([$table])
            ->where(['not', ['menuFieldLayoutId' => null]])
            ->column($this->db);

        $fieldsService = Craft::$app->getFields();

        foreach ($layoutIds as $layoutId) {
            $layout = $fieldsService->getLayoutById((int)$layoutId);

            if (!$layout || $layout->type !== Menu::class) {
                continue;
            }

            if (!$layout->isFieldIncluded(fn($field) => $field instanceof TitleField || $field->attribute() === 'title')) {
                continue;
            }

            $fieldsService->saveLayout(MenuContentFieldLayout::withoutTitle($layout), false);
        }
    }

    private function _stripTitleFromProjectConfig(): void
    {
        if (!ProjectConfigData::canUpdateProjectConfig()) {
            return;
        }

        $projectConfig = Craft::$app->getProjectConfig();

        foreach (['navigation.menus', 'navigation.navs'] as $path) {
            $menus = $projectConfig->get($path);

            if (!$menus || !is_array($menus)) {
                continue;
            }

            $changed = false;

            foreach ($menus as $uid => $menu) {
                if (empty($menu['menuFieldLayouts']) || !is_array($menu['menuFieldLayouts'])) {
                    continue;
                }

                foreach ($menu['menuFieldLayouts'] as $layoutUid => $layoutConfig) {
                    if (!is_array($layoutConfig)) {
                        continue;
                    }

                    $stripped = MenuContentFieldLayout::stripTitleFromConfig($layoutConfig);

                    if ($stripped !== $layoutConfig) {
                        $menus[$uid]['menuFieldLayouts'][$layoutUid] = $stripped;
                        $changed = true;
                    }
                }
            }

            if ($changed) {
                $projectConfig->set($path, $menus, 'Remove title field from menu content layouts');
            }
        }
    }
}
