<?php
namespace verbb\navigation\helpers;

use verbb\navigation\models\MenuSettings;

use Craft;
use craft\db\Query;

use yii\web\ConflictHttpException;

/** Optimistic protection for complete tree order submitted by separate editors. */
class BuilderStructureRevision
{
    // Static Methods
    // =========================================================================

    public static function get(MenuSettings $menu): string
    {
        $rows = (new Query())
            ->select(['elementId', 'root', 'lft', 'rgt', 'level'])
            ->from('{{%structureelements}}')
            ->where(['structureId' => $menu->structureId])
            ->orderBy(['lft' => SORT_ASC])
            ->all();

        return hash('sha256', json_encode([$menu->uid, $rows], JSON_THROW_ON_ERROR));
    }

    /** Must run inside the transaction that applies the structure. */
    public static function requireCurrent(MenuSettings $menu, mixed $expected): void
    {
        Craft::$app->getDb()
            ->createCommand('SELECT [[id]] FROM {{%navigation_menus}} WHERE [[id]] = :id FOR UPDATE', [':id' => $menu->id])
            ->queryScalar();

        if (!is_string($expected) || !hash_equals(self::get($menu), $expected)) {
            throw new ConflictHttpException(Craft::t('navigation', 'This menu structure changed in another tab or session. Reload the menu before saving.'));
        }
    }

    public static function apply(MenuSettings $menu, mixed $expected, callable $callback): mixed
    {
        return Craft::$app->getDb()->transaction(function() use ($menu, $expected, $callback) {
            self::requireCurrent($menu, $expected);

            return $callback();
        });
    }
}
