<?php
namespace verbb\navigation\migrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

use craft\db\Query;
use craft\migrations\BaseContentRefactorMigration;

class m231229_000000_content_refactor extends BaseContentRefactorMigration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        foreach (Navigation::$plugin->getNavs()->getAllNavs() as $type) {
            $this->updateElements(
                (new Query())->from('{{%navigation_nodes}}')->where(['navId' => $type->id]),
                $type->getFieldLayout(),
            );
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m231229_000000_content_refactor cannot be reverted.\n";

        return false;
    }
}
