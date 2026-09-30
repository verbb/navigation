<?php
namespace verbb\navigation\migrations;

use Craft;
use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;
use craft\models\FieldLayout;

use yii\base\InvalidConfigException;

class m231229_000000_content_refactor extends BaseContentRefactorMigration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $schema = $this->_resolveSchema();

        if (!$schema) {
            return true;
        }

        // This migration predates the menu rename, so current plugin services may
        // expect tables and columns that do not exist yet during a direct upgrade.
        $menus = (new Query())
            ->select(['id', 'fieldLayoutId'])
            ->from($schema['menuTable'])
            ->where(['dateDeleted' => null])
            ->all($this->db);

        foreach ($menus as $menu) {
            $this->updateElements(
                (new Query())->from('{{%navigation_nodes}}')->where([$schema['nodeMenuIdColumn'] => $menu['id']]),
                $this->_fieldLayout($menu['fieldLayoutId']),
            );
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m231229_000000_content_refactor cannot be reverted.\n";

        return false;
    }


    // Private Methods
    // =========================================================================

    private function _resolveSchema(): ?array
    {
        if ($this->db->tableExists('{{%navigation_menus}}')) {
            return [
                'menuTable' => '{{%navigation_menus}}',
                'nodeMenuIdColumn' => $this->db->columnExists('{{%navigation_nodes}}', 'menuId') ? 'menuId' : 'navId',
            ];
        }

        if ($this->db->tableExists('{{%navigation_navs}}')) {
            return [
                'menuTable' => '{{%navigation_navs}}',
                'nodeMenuIdColumn' => 'navId',
            ];
        }

        return null;
    }

    private function _fieldLayout(int|string|null $fieldLayoutId): ?FieldLayout
    {
        if (!$fieldLayoutId) {
            return null;
        }

        $fieldLayout = Craft::$app->getFields()->getLayoutById((int)$fieldLayoutId, true);

        if (!$fieldLayout) {
            throw new InvalidConfigException('Invalid field layout ID: ' . $fieldLayoutId);
        }

        return $fieldLayout;
    }
}
