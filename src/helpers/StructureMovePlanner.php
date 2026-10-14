<?php
namespace verbb\navigation\helpers;

use yii\web\BadRequestHttpException;

class StructureMovePlanner
{
    // Static Methods
    // =========================================================================

    /** Build the canonical menu tree while ignoring physical draft rows in Craft's structure. */
    public static function fromStructureRows(array $rows, array $elementIds): array
    {
        $allowedIds = array_fill_keys(array_map('intval', $elementIds), true);
        $moves = [];
        $stack = [];
        $previousByParent = [];

        foreach ($rows as $row) {
            $elementId = (int)($row['elementId'] ?? 0);

            if (!$elementId || !isset($allowedIds[$elementId])) {
                continue;
            }

            $left = (int)($row['lft'] ?? 0);

            while ($stack && (int)end($stack)['rgt'] < $left) {
                array_pop($stack);
            }

            $parentId = $stack ? (int)end($stack)['elementId'] : null;
            $parentKey = $parentId ?? 0;
            $moves[] = [
                'elementId' => $elementId,
                'parentId' => $parentId,
                'prevId' => $previousByParent[$parentKey] ?? null,
            ];
            $previousByParent[$parentKey] = $elementId;
            $stack[] = [
                'elementId' => $elementId,
                'rgt' => (int)($row['rgt'] ?? 0),
            ];
        }

        return $moves;
    }

    /**
     * Reconcile submitted move commands into the smallest sibling edits that preserve
     * the same final tree. Full snapshots remain useful for staging without replaying
     * every unchanged node through Craft's structure service.
     */
    public static function plan(array $currentMoves, array $submittedMoves, array $skipIds = []): array
    {
        $current = self::_tree($currentMoves);
        $skip = array_fill_keys(array_map('intval', $skipIds), true);
        [$desired, $effectiveSubmitted] = self::_simulate($current, $submittedMoves, $skip);

        // Deleted nodes can remain as temporary anchors until publication removes them.
        // Preserve the submitted execution order for that transitional tree.
        if ($skip) {
            return $effectiveSubmitted;
        }

        $moveIds = [];

        foreach ($desired['parents'] as $elementId => $parentId) {
            if (($current['parents'][$elementId] ?? null) !== $parentId) {
                $moveIds[$elementId] = true;
            }
        }

        $parentIds = array_unique(array_merge(array_keys($current['children']), array_keys($desired['children'])));

        foreach ($parentIds as $parentId) {
            $currentChildren = array_values(array_filter(
                $current['children'][$parentId] ?? [],
                fn(int $elementId) => ($desired['parents'][$elementId] ?? null) === (int)$parentId,
            ));
            $desiredChildren = array_values(array_filter(
                $desired['children'][$parentId] ?? [],
                fn(int $elementId) => ($current['parents'][$elementId] ?? null) === (int)$parentId,
            ));
            $stationaryIds = self::_longestOrderedIds($currentChildren, $desiredChildren);

            foreach ($desiredChildren as $elementId) {
                if (!isset($stationaryIds[$elementId])) {
                    $moveIds[$elementId] = true;
                }
            }
        }

        $plannedMoves = [];

        foreach (self::_preorder($desired['children']) as $elementId) {
            if (!isset($moveIds[$elementId])) {
                continue;
            }

            $parentId = $desired['parents'][$elementId];
            $siblings = $desired['children'][$parentId] ?? [];
            $index = array_search($elementId, $siblings, true);
            $plannedMoves[] = [
                'elementId' => $elementId,
                'parentId' => $parentId ?: null,
                'prevId' => $index ? $siblings[$index - 1] : null,
            ];
        }

        [$plannedTree, $effectivePlan] = self::_simulate($current, $plannedMoves, []);

        // Keep the established sequential semantics as a correctness fallback if a
        // future tree shape exposes a planner case that cannot reproduce the snapshot.
        if ($plannedTree['parents'] !== $desired['parents'] || $plannedTree['children'] !== $desired['children']) {
            return $effectiveSubmitted;
        }

        return $effectivePlan;
    }


    // Private Methods
    // =========================================================================

    private static function _tree(array $moves): array
    {
        $parents = [];
        $children = [0 => []];

        foreach ($moves as $move) {
            if (!is_array($move)) {
                throw new BadRequestHttpException('Invalid move payload.');
            }

            $elementId = self::_id($move['elementId'] ?? null);
            $parentId = self::_id($move['parentId'] ?? null, true) ?? 0;

            if (isset($parents[$elementId]) || ($parentId && !isset($parents[$parentId]))) {
                throw new BadRequestHttpException('Invalid move node or target.');
            }

            $parents[$elementId] = $parentId;
            $children[$parentId] ??= [];
            $children[$parentId][] = $elementId;
            $children[$elementId] ??= [];
        }

        return compact('parents', 'children');
    }

    private static function _simulate(array $tree, array $moves, array $skip): array
    {
        $effectiveMoves = [];

        foreach ($moves as $move) {
            if (!is_array($move)) {
                throw new BadRequestHttpException('Invalid move payload.');
            }

            $elementId = self::_id($move['elementId'] ?? null);

            if (isset($skip[$elementId])) {
                continue;
            }

            $parentId = self::_id($move['parentId'] ?? null, true) ?? 0;
            $prevId = self::_id($move['prevId'] ?? null, true);

            foreach (array_filter([$elementId, $parentId, $prevId]) as $targetId) {
                if (!isset($tree['parents'][$targetId])) {
                    throw new BadRequestHttpException('Invalid move node or target.');
                }
            }

            $destinationId = $prevId ? $tree['parents'][$prevId] : $parentId;

            if ($prevId === $elementId || $destinationId === $elementId) {
                throw new BadRequestHttpException('Invalid move target.');
            }

            $oldParentId = $tree['parents'][$elementId];
            $oldIndex = array_search($elementId, $tree['children'][$oldParentId], true);

            if ($oldIndex === false) {
                throw new BadRequestHttpException('Invalid move node or target.');
            }

            $oldPrevId = $oldIndex ? $tree['children'][$oldParentId][$oldIndex - 1] : null;

            if ($oldParentId === $destinationId && $oldPrevId === $prevId) {
                continue;
            }

            $descendantIds = self::_descendantIds($tree['children'], $elementId);

            if (isset($descendantIds[$destinationId])) {
                throw new BadRequestHttpException('Cannot move a node beneath its descendant.');
            }

            array_splice($tree['children'][$oldParentId], $oldIndex, 1);
            $tree['children'][$destinationId] ??= [];
            $newIndex = $prevId ? array_search($prevId, $tree['children'][$destinationId], true) : -1;

            if ($prevId && $newIndex === false) {
                throw new BadRequestHttpException('Invalid move target.');
            }

            array_splice($tree['children'][$destinationId], $newIndex + 1, 0, [$elementId]);
            $tree['parents'][$elementId] = $destinationId;
            $effectiveMoves[] = [
                'elementId' => $elementId,
                'parentId' => $destinationId ?: null,
                'prevId' => $prevId,
            ];
        }

        return [$tree, $effectiveMoves];
    }

    private static function _descendantIds(array $children, int $elementId): array
    {
        $descendantIds = [];
        $pendingIds = $children[$elementId] ?? [];

        while ($pendingIds) {
            $descendantId = array_shift($pendingIds);
            $descendantIds[$descendantId] = true;
            array_push($pendingIds, ...($children[$descendantId] ?? []));
        }

        return $descendantIds;
    }

    /** Find the stationary sibling subsequence in O(n log n) for unique element IDs. */
    private static function _longestOrderedIds(array $currentIds, array $desiredIds): array
    {
        if (!$currentIds || !$desiredIds) {
            return [];
        }

        $positions = array_flip($desiredIds);
        $tails = [];
        $tailIndices = [];
        $previousIndices = [];

        foreach ($currentIds as $index => $elementId) {
            $position = $positions[$elementId];
            $low = 0;
            $high = count($tails);

            while ($low < $high) {
                $middle = intdiv($low + $high, 2);

                if ($tails[$middle] < $position) {
                    $low = $middle + 1;
                } else {
                    $high = $middle;
                }
            }

            $tails[$low] = $position;
            $previousIndices[$index] = $low ? $tailIndices[$low - 1] : null;
            $tailIndices[$low] = $index;
        }

        $orderedIds = [];
        $index = end($tailIndices);

        while ($index !== false && $index !== null) {
            $orderedIds[$currentIds[$index]] = true;
            $index = $previousIndices[$index];
        }

        return $orderedIds;
    }

    private static function _preorder(array $children): array
    {
        $orderedIds = [];
        $pendingIds = array_reverse($children[0] ?? []);

        while ($pendingIds) {
            $elementId = array_pop($pendingIds);
            $orderedIds[] = $elementId;

            foreach (array_reverse($children[$elementId] ?? []) as $childId) {
                $pendingIds[] = $childId;
            }
        }

        return $orderedIds;
    }

    private static function _id(mixed $value, bool $optional = false): ?int
    {
        if ($optional && in_array($value, [null, '', 0, '0'], true)) {
            return null;
        }

        if ((!is_int($value) && !is_string($value))
            || !preg_match('/^[1-9][0-9]*$/D', (string)$value)
            || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new BadRequestHttpException('Invalid move identifier.');
        }

        return (int)$value;
    }
}
