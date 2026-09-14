<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

class NodeHierarchy
{
    // Static Methods
    // =========================================================================

    /** Derive direct children from stored bounds without assuming query order or complete branches. */
    public static function childrenByParent(array $nodes): array
    {
        $groups = [];
        foreach ($nodes as $node) {
            if ($node instanceof Node && $node->lft !== null && $node->rgt !== null) {
                $key = $node->menuId . ':' . $node->structureId . ':' . $node->root . ':' . $node->siteId;
                $groups[$key][] = $node;
            }
        }

        $children = [];
        foreach ($groups as $group) {
            // Sort a copy: callers retain their requested result order and keys.
            usort($group, static fn(Node $a, Node $b) => $a->lft <=> $b->lft);
            $stack = [];
            foreach ($group as $node) {
                while ($stack && end($stack)->rgt <= $node->rgt) {
                    array_pop($stack);
                }
                $parent = $stack ? end($stack) : null;
                if ($parent && (int)$parent->level + 1 === (int)$node->level) {
                    $children[$parent->id . ':' . (int)$parent->siteId][] = $node;
                }
                $stack[] = $node;
            }
        }

        return $children;
    }
}
