<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;

use Craft;
use craft\db\Query;
use craft\events\MoveElementEvent;
use craft\services\Structures;

use yii\web\BadRequestHttpException;

class StructureLimits
{
    // Static Methods
    // =========================================================================

    /** Full-tree edits may temporarily exceed limits while exchanging siblings. */
    public static function batch(MenuSettings $menu, int $siteId, callable $callback, array $skipIds = []): void
    {
        $key = $menu->id . ':' . $siteId;
        $previous = self::$_batches[$key] ?? 0;
        self::$_batches[$key] = $previous + 1;

        try {
            Craft::$app->getDb()->transaction(function() use ($menu, $siteId, $callback, $skipIds, $previous) {
                $before = !$previous && self::_hasLimits($menu) ? self::_tree($menu, $siteId) : null;

                $callback();

                if ($before !== null) {
                    self::_validate($menu, array_diff_key(self::_tree($menu, $siteId), $skipIds), $before);
                }
            });
        } finally {
            if ($previous) {
                self::$_batches[$key] = $previous;
            } else {
                unset(self::$_batches[$key]);
            }
        }
    }

    /** Capture the tree before a change, so `requireCurrent()` can tell what the change added. */
    public static function snapshot(MenuSettings $menu, int $siteId): ?array
    {
        if (isset(self::$_batches[$menu->id . ':' . $siteId]) || !self::_hasLimits($menu)) {
            return null;
        }

        return self::_tree($menu, $siteId);
    }

    public static function requireCurrent(MenuSettings $menu, int $siteId, array $before): void
    {
        if (!isset(self::$_batches[$menu->id . ':' . $siteId]) && self::_hasLimits($menu)) {
            self::_validate($menu, self::_tree($menu, $siteId), $before);
        }
    }

    /** Predict the destination tree before Craft changes it, including moved descendants. */
    public static function requireMove(MoveElementEvent $event): void
    {
        $node = $event->element;

        if (!$node instanceof Node || !$node->getIsCanonical()) {
            return;
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($node->menuId);

        if (!$menu || !self::_hasLimits($menu) || isset(self::$_batches[$menu->id . ':' . $node->siteId])) {
            return;
        }

        $before = self::_tree($menu, (int)$node->siteId);
        $tree = $before;
        $target = $event->targetElementId ? ($tree[$event->targetElementId] ?? null) : null;
        $asChild = in_array($event->action, [Structures::ACTION_APPEND, Structures::ACTION_PREPEND], true);
        $parentId = $asChild ? ($target['id'] ?? null) : ($target['parentId'] ?? null);
        $level = $target ? $target['level'] + ($asChild ? 1 : 0) : 1;
        $original = $tree[$node->id] ?? null;

        if ($original) {
            $delta = $level - $original['level'];

            foreach ($tree as &$row) {
                if ($row['lft'] > $original['lft'] && $row['rgt'] < $original['rgt']) {
                    $row['level'] += $delta;
                }
            }

            unset($row);
        }

        $tree[$node->id] = ['id' => $node->id, 'parentId' => $parentId, 'level' => $level];

        try {
            self::_validate($menu, $tree, $before);
        } catch (BadRequestHttpException) {
            // A false event result lets Craft release its structure mutex normally.
            $event->isValid = false;
        }
    }

    private static function _tree(MenuSettings $menu, int $siteId): array
    {
        $ids = Node::find()->menuId($menu->id)->siteId($siteId)->status(null)->ids();
        $rows = (new Query())->select(['id' => 'elementId', 'lft', 'rgt', 'level'])
            ->from('{{%structureelements}}')->where(['structureId' => $menu->structureId, 'elementId' => $ids])
            ->orderBy(['lft' => SORT_ASC])->all();
        $tree = [];
        $stack = [];

        foreach ($rows as $row) {
            while ($stack && end($stack)['rgt'] < $row['lft']) {
                array_pop($stack);
            }

            $row['parentId'] = $stack ? end($stack)['id'] : null;
            $row['level'] = (int)$row['level'];
            $tree[$row['id']] = $row;
            $stack[] = $row;
        }

        return $tree;
    }

    private static function _hasLimits(MenuSettings $menu): bool
    {
        return $menu->maxNodes || $menu->maxLevels || $menu->maxNodesSettings;
    }

    /**
     * Limits can be lowered below a menu's current size, so only a breach the change itself adds
     * fails. Deleting, editing or reordering nodes in a menu that's already over a limit still works.
     */
    private static function _validate(MenuSettings $menu, array $tree, array $before): void
    {
        // Native duplication, restoration and imports do not share the builder's temporary-node list.
        if ($menu->maxNodes && count($tree) > $menu->maxNodes && count($tree) > count($before)) {
            throw new BadRequestHttpException(Craft::t('navigation', 'Exceeded maximum allowed nodes ({number}) for this menu.', ['number' => $menu->maxNodes]));
        }

        $counts = self::_levelCounts($tree);
        $beforeCounts = self::_levelCounts($before);

        foreach ($tree as $id => $row) {
            if ($menu->maxLevels && $row['level'] > $menu->maxLevels && $row['level'] > ($before[$id]['level'] ?? 0)) {
                throw new BadRequestHttpException(Craft::t('navigation', 'Maximum menu depth exceeded.'));
            }
        }

        foreach ($counts as $key => $count) {
            $level = (int)explode(':', $key)[0];

            foreach ($menu->maxNodesSettings ?? [] as $limit) {
                if (isset($limit['level'], $limit['max']) && (int)$limit['level'] === $level
                    && $count > (int)$limit['max'] && $count > ($beforeCounts[$key] ?? 0)) {
                    throw new BadRequestHttpException(Craft::t('navigation', 'Exceeded maximum allowed nodes for this level.'));
                }
            }
        }
    }

    private static function _levelCounts(array $tree): array
    {
        $counts = [];

        foreach ($tree as $row) {
            $key = $row['level'] . ':' . ($row['parentId'] ?? 'root');
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }


    // Properties
    // =========================================================================

    private static array $_batches = [];
}
