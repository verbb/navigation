import type { BuilderNode } from '../types';
import {
  clampTargetLevel,
  getLevelBounds,
  getSubtreeDepth,
  getSubtreeRange,
  adjustSubtreeToTargetRootLevel,
  recalculateLevels,
  repositionNodeLevel,
  type LevelBounds,
} from './tree';

export type DropPosition = 'before' | 'after' | 'inside';

const BEFORE_RATIO = 0.25;
const AFTER_RATIO = 0.75;

export function resolveDropPosition(pointerY: number, overTop: number, overHeight: number): DropPosition {
  if (overHeight <= 0) {
    return 'inside';
  }

  const ratio = (pointerY - overTop) / overHeight;

  if (ratio < BEFORE_RATIO) {
    return 'before';
  }

  if (ratio > AFTER_RATIO) {
    return 'after';
  }

  return 'inside';
}

export function getDropInsertIndex(
  nodes: BuilderNode[],
  overId: number,
  position: DropPosition,
): number {
  const { start, end } = getSubtreeRange(nodes, overId);

  if (start < 0) {
    return -1;
  }

  switch (position) {
    case 'before':
      return start;
    case 'after':
      return end;
    case 'inside':
      return start + 1;
  }
}

export type DropPreview = {
  insertIndex: number;
  targetLevel: number;
  position: DropPosition;
};

export function resolveDropPreview(
  nodes: BuilderNode[],
  activeId: number,
  overId: number,
  position: DropPosition,
  requestedLevel: number,
  maxLevels: number | null,
): DropPreview | null {
  const { start, end } = getSubtreeRange(nodes, activeId);

  if (start < 0) {
    return null;
  }

  const overNode = nodes.find((node) => node.id === overId);

  if (!overNode) {
    return null;
  }

  const overIndex = nodes.findIndex((node) => node.id === overId);

  if (overId !== activeId && overIndex >= start && overIndex < end) {
    return null;
  }

  const insertIndex = getDropInsertIndex(nodes, overId, position);

  if (insertIndex < 0) {
    return null;
  }

  const subtree = nodes.slice(start, end);
  const rootLevel = subtree[0].level;
  const remaining = [...nodes.slice(0, start), ...nodes.slice(end)];
  let adjustedInsertIndex = insertIndex;

  if (insertIndex > start) {
    adjustedInsertIndex = insertIndex - (end - start);
  }

  const subtreeDepth = getSubtreeDepth(subtree, rootLevel);
  const bounds = getLevelBounds(remaining, adjustedInsertIndex, subtreeDepth, maxLevels);
  const levelRequest = position === 'inside' ? overNode.level + 1 : requestedLevel;
  const targetLevel = resolveDropTargetLevel(levelRequest, bounds, position, overNode.level);

  return {
    insertIndex: adjustedInsertIndex,
    targetLevel,
    position,
  };
}

function resolveDropTargetLevel(
  requestedLevel: number,
  bounds: LevelBounds,
  position: DropPosition,
  overLevel: number,
): number {
  let level = clampTargetLevel(requestedLevel, bounds);

  if (position === 'inside') {
    level = Math.max(overLevel + 1, level);
    level = clampTargetLevel(level, bounds);
  }

  return level;
}

export function applyDropTarget(
  nodes: BuilderNode[],
  activeId: number,
  overId: number,
  position: DropPosition,
  requestedLevel: number,
  maxLevels: number | null,
): BuilderNode[] {
  if (activeId === overId) {
    if (position === 'inside') {
      return nodes;
    }

    return repositionNodeLevel(nodes, activeId, requestedLevel, maxLevels);
  }

  const preview = resolveDropPreview(nodes, activeId, overId, position, requestedLevel, maxLevels);

  if (!preview) {
    return nodes;
  }

  const { start, end } = getSubtreeRange(nodes, activeId);

  if (start < 0) {
    return nodes;
  }

  const subtree = nodes.slice(start, end);
  const rootLevel = subtree[0].level;
  const remaining = [...nodes.slice(0, start), ...nodes.slice(end)];

  if (preview.targetLevel === rootLevel && preview.insertIndex === start) {
    return nodes;
  }

  const adjustedSubtree = adjustSubtreeToTargetRootLevel(subtree, preview.targetLevel);

  const next = [
    ...remaining.slice(0, preview.insertIndex),
    ...adjustedSubtree,
    ...remaining.slice(preview.insertIndex),
  ];

  return recalculateLevels(next);
}

export type InsertIndicatorPlacement = {
  anchorNodeId: number;
  placement: 'before' | 'after';
  level: number;
};

export function getInsertIndicatorPlacement(
  overId: number,
  position: DropPosition,
  targetLevel: number,
): InsertIndicatorPlacement {
  if (position === 'before') {
    return {
      anchorNodeId: overId,
      placement: 'before',
      level: targetLevel,
    };
  }

  return {
    anchorNodeId: overId,
    placement: 'after',
    level: targetLevel,
  };
}
