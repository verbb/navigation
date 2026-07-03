import type { BuilderNode } from '../types';

export const NAVIGATION_TREE_ROOT_ID = '__navigation_root__';

export type NavigationTreeChildrenMap = Record<string, string[]>;

/** Build parent→child id lists from a flat, ordered node array. */
export function buildNavigationTreeChildrenMap(nodes: BuilderNode[]): NavigationTreeChildrenMap {
  const children: NavigationTreeChildrenMap = {
    [NAVIGATION_TREE_ROOT_ID]: [],
  };

  for (const node of nodes) {
    const parentKey =
      node.parentId === null ? NAVIGATION_TREE_ROOT_ID : String(node.parentId);

    if (!children[parentKey]) {
      children[parentKey] = [];
    }

    children[parentKey].push(String(node.id));

    const key = String(node.id);

    if (!children[key]) {
      children[key] = [];
    }
  }

  return children;
}

/** Flatten a parent→child map back into the builder's ordered node array. */
export function flattenNavigationTree(
  nodeMap: Map<number, BuilderNode>,
  childrenMap: NavigationTreeChildrenMap,
  parentKey: string = NAVIGATION_TREE_ROOT_ID,
  parentLevel = 0,
): BuilderNode[] {
  const childIds = childrenMap[parentKey] ?? [];
  const flat: BuilderNode[] = [];

  for (const childId of childIds) {
    const id = Number(childId);
    const node = nodeMap.get(id);

    if (!node) {
      continue;
    }

    const level = parentLevel + 1;
    const parentId = parentKey === NAVIGATION_TREE_ROOT_ID ? null : Number(parentKey);

    flat.push({
      ...node,
      level,
      parentId,
    });

    flat.push(...flattenNavigationTree(nodeMap, childrenMap, childId, level));
  }

  return flat;
}

export function refreshHasDescendants(nodes: BuilderNode[]): BuilderNode[] {
  const childCounts = new Map<number, number>();

  for (const node of nodes) {
    if (node.parentId !== null) {
      childCounts.set(node.parentId, (childCounts.get(node.parentId) ?? 0) + 1);
    }
  }

  return nodes.map((node) => ({
    ...node,
    hasDescendants: (childCounts.get(node.id) ?? 0) > 0,
  }));
}

/** Replace visible tree nodes with a new flat order while keeping filtered-out nodes in place. */
export function mergeReorderedTreeNodes(
  allNodes: BuilderNode[],
  treeNodeIds: Set<number>,
  nextTreeNodes: BuilderNode[],
): BuilderNode[] {
  const nextById = new Map(nextTreeNodes.map((node) => [node.id, node]));
  let flatIndex = 0;

  return allNodes.map((node) => {
    if (!treeNodeIds.has(node.id)) {
      return node;
    }

    const next = nextTreeNodes[flatIndex];

    if (next?.id === node.id) {
      flatIndex += 1;
      return next;
    }

    return nextById.get(node.id) ?? node;
  });
}

export function getExpandedTreeItemIds(
  nodes: BuilderNode[],
  collapsedNodeIds: Record<number, boolean>,
): string[] {
  return nodes
    .filter((node) => node.hasDescendants && !collapsedNodeIds[node.id])
    .map((node) => String(node.id));
}

/** Clone a children map so drop mutations never touch the current render snapshot. */
export function cloneNavigationTreeChildrenMap(
  childrenMap: NavigationTreeChildrenMap,
): NavigationTreeChildrenMap {
  return Object.fromEntries(
    Object.entries(childrenMap).map(([key, childIds]) => [key, [...childIds]]),
  );
}

/** Remove dragged ids from every parent list in the children map. */
export function removeIdsFromChildrenMap(
  childrenMap: NavigationTreeChildrenMap,
  draggedIds: Set<string>,
): NavigationTreeChildrenMap {
  const nextMap = cloneNavigationTreeChildrenMap(childrenMap);

  for (const parentId of Object.keys(nextMap)) {
    nextMap[parentId] = nextMap[parentId].filter((id) => !draggedIds.has(id));
  }

  return nextMap;
}
