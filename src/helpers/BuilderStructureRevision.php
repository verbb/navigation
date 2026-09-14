<?php
namespace verbb\navigation\helpers;

use verbb\navigation\models\MenuSettings;

use Craft;
use craft\db\Query;

use yii\web\ConflictHttpException;
use yii\web\Response;

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
        self::_lock($menu);

        if (!is_string($expected) || !hash_equals(self::get($menu), $expected)) {
            throw new ConflictHttpException(Craft::t('navigation', 'This menu structure changed in another tab or session. Discard your pending changes and reload the menu before saving.'));
        }
    }

    public static function apply(MenuSettings $menu, mixed $expected, callable $callback): mixed
    {
        return Craft::$app->getDb()->transaction(function() use ($menu, $expected, $callback) {
            self::requireCurrent($menu, $expected);

            return $callback();
        });
    }

    /** Report a causal before/after pair so clients cannot adopt unrelated editor revisions. */
    public static function trackMutation(MenuSettings $menu, callable $callback): Response
    {
        return Craft::$app->getDb()->transaction(function() use ($menu, $callback) {
            self::_lock($menu);
            $before = self::get($menu);
            $response = $callback();
            if ($response->getStatusCode() < 400) {
                $response->data['previousStructureRevision'] = $before;
                $response->data['structureRevision'] = self::get($menu);
            }

            return $response;
        });
    }

    private static function _lock(MenuSettings $menu): void
    {
        Craft::$app->getDb()
            ->createCommand('SELECT [[id]] FROM {{%navigation_menus}} WHERE [[id]] = :id FOR UPDATE', [':id' => $menu->id])
            ->queryScalar();
    }

}
