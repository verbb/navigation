import type { BuilderNode } from '../types';
import {
  clampTargetLevel,
  getLevelBounds,
  getSubtreeDepth,
  getSubtreeRange,
} from './tree';

export type NodeMoveCapabilities = {
  canMoveUp: boolean;
  canMoveDown: boolean;
  canMoveLeft: boolean;
  canMoveRight: boolean;
};

export function getPreviousSibling(
  nodes: BuilderNode[],
  nodeId: number,
): BuilderNode | null {
  const index = nodes.findIndex((node) => node.id === nodeId);

  if (index <= 0) {
    return null;
  }

  const node = nodes[index];

  for (let i = index - 1; i >= 0; i -= 1) {
    if (nodes[i].level < node.level) {
      break;
    }

    if (nodes[i].level === node.level && nodes[i].parentId === node.parentId) {
      return nodes[i];
    }
  }

  return null;
}

export function getNextSibling(
  nodes: BuilderNode[],
  nodeId: number,
): BuilderNode | null {
  const index = nodes.findIndex((node) => node.id === nodeId);

  if (index < 0) {
    return null;
  }

  const node = nodes[index];
  const { end } = getSubtreeRange(nodes, nodeId);

  for (let i = end; i < nodes.length; i += 1) {
    if (nodes[i].level < node.level) {
      break;
    }

    if (nodes[i].level === node.level && nodes[i].parentId === node.parentId) {
      return nodes[i];
    }
  }

  return null;
}

export function canIndentNode(
  nodes: BuilderNode[],
  nodeId: number,
  maxLevels: number | null,
): boolean {
  const index = nodes.findIndex((node) => node.id === nodeId);

  if (index <= 0) {
    return false;
  }

  const node = nodes[index];
  const prev = nodes[index - 1];
  const newLevel = prev.level + 1;

  if (maxLevels !== null && newLevel > maxLevels) {
    return false;
  }

  const { start, end } = getSubtreeRange(nodes, nodeId);
  const subtreeDepth = getSubtreeDepth(nodes.slice(start, end), node.level);

  if (maxLevels !== null && newLevel + subtreeDepth > maxLevels) {
    return false;
  }

  return true;
}

export function canOutdentNode(
  nodes: BuilderNode[],
  nodeId: number,
  maxLevels: number | null,
): boolean {
  const index = nodes.findIndex((node) => node.id === nodeId);

  if (index <= 0) {
    return false;
  }

  const node = nodes[index];

  if (node.level <= 1) {
    return false;
  }

  let parentNode: BuilderNode | null = null;

  for (let i = index - 1; i >= 0; i -= 1) {
    if (nodes[i].level === node.level - 1) {
      parentNode = nodes[i];
      break;
    }
  }

  if (!parentNode) {
    return false;
  }

  const { start, end } = getSubtreeRange(nodes, nodeId);

  if (start < 0) {
    return false;
  }

  const subtree = nodes.slice(start, end);
  const rootLevel = subtree[0].level;
  const targetLevel = parentNode.level;
  const parentEnd = getSubtreeRange(nodes, parentNode.id).end;
  let insertIndex = parentEnd;

  if (start < parentEnd) {
    insertIndex -= end - start;
  }

  const remaining = [...nodes.slice(0, start), ...nodes.slice(end)];
  const subtreeDepth = getSubtreeDepth(subtree, rootLevel);
  const bounds = getLevelBounds(remaining, insertIndex, subtreeDepth, maxLevels);

  return clampTargetLevel(targetLevel, bounds) === targetLevel;
}

export function getNodeMoveCapabilities(
  nodes: BuilderNode[],
  nodeId: number,
  maxLevels: number | null,
): NodeMoveCapabilities {
  const allowNested = maxLevels === null || maxLevels > 1;

  return {
    canMoveUp: getPreviousSibling(nodes, nodeId) !== null,
    canMoveDown: getNextSibling(nodes, nodeId) !== null,
    canMoveLeft: allowNested && canOutdentNode(nodes, nodeId, maxLevels),
    canMoveRight: allowNested && canIndentNode(nodes, nodeId, maxLevels),
  };
}
