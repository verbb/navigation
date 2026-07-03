import type { BuilderNode } from '../types';
import {
  buildNavigationTreeChildrenMap,
  flattenNavigationTree,
  refreshHasDescendants,
} from './headlessTreeData';

/** Reconcile flat node order, levels, parentId, and hasDescendants from the tree hierarchy. */
export function normalizeTreeNodes(nodes: BuilderNode[]): BuilderNode[] {
  const nodeMap = new Map(nodes.map((node) => [node.id, node]));
  const childrenMap = buildNavigationTreeChildrenMap(nodes);

  return refreshHasDescendants(flattenNavigationTree(nodeMap, childrenMap));
}
