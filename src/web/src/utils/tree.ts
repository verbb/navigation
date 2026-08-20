import type { BuilderNode, NodeStatusFilter, StructureMove } from '../types';
import { NODE_LEVEL_INDENT_PX } from './nodeRowLayout';
import { normalizeTreeNodes } from './normalizeTreeNodes';

export type SubtreeRange = {
  start: number;
  end: number;
};

export type LevelBounds = {
  min: number;
  max: number;
};

export type MoveContext = {
  remaining: BuilderNode[];
  insertIndex: number;
  subtree: BuilderNode[];
  rootLevel: number;
  subtreeDepth: number;
};

function nodeMatchesStatusFilter(node: BuilderNode, statusFilter: NodeStatusFilter): boolean {
  switch (statusFilter) {
    case 'enabled':
      return node.enabled && node.enabledForSite && !node.pendingDelete;
    case 'disabled':
      return !node.pendingDelete && (!node.enabled || !node.enabledForSite);
    case 'trashed':
      return node.pendingDelete;
    default:
      return true;
  }
}

export function filterNodesByStatus(nodes: BuilderNode[], statusFilter: NodeStatusFilter): BuilderNode[] {
  if (statusFilter === 'all') {
    return nodes;
  }

  const nodeMap = new Map(nodes.map((node) => [node.id, node]));
  const visibleIds = new Set<number>();

  for (const node of nodes) {
    if (!nodeMatchesStatusFilter(node, statusFilter)) {
      continue;
    }

    visibleIds.add(node.id);

    let parentId = node.parentId;

    while (parentId) {
      visibleIds.add(parentId);
      parentId = nodeMap.get(parentId)?.parentId ?? null;
    }
  }

  return nodes.filter((node) => visibleIds.has(node.id));
}

export function collectStructureMoves(nodes: BuilderNode[]): StructureMove[] {
  const moves: Array<StructureMove & { level: number }> = [];
  const stack: Array<{ id: number; level: number }> = [];

  // Pending-delete rows stay visible for undo, but must not own parent/prev slots —
  // publish hard-deletes them, and moves that still name them fail with Invalid node ID.
  for (const node of nodes) {
    if (node.pendingDelete) {
      continue;
    }

    const level = node.level;

    while (stack.length && stack[stack.length - 1].level >= level) {
      stack.pop();
    }

    const parentId = stack.length ? stack[stack.length - 1].id : null;
    let prevId: number | null = null;

    for (let i = moves.length - 1; i >= 0; i--) {
      if (moves[i].level === level && moves[i].parentId === parentId) {
        prevId = moves[i].elementId;
        break;
      }
    }

    moves.push({
      elementId: node.id,
      parentId,
      prevId,
      level,
    });

    stack.push({ id: node.id, level });
  }

  return moves.map(({ elementId, parentId, prevId }) => ({
    elementId,
    parentId,
    prevId,
  }));
}

function isDescendantNode(
  node: BuilderNode,
  ancestorId: number,
  nodeMap: Map<number, BuilderNode>,
): boolean {
  let parentId = node.parentId;

  while (parentId !== null) {
    if (parentId === ancestorId) {
      return true;
    }

    parentId = nodeMap.get(parentId)?.parentId ?? null;
  }

  return false;
}

export function getSubtreeRange(nodes: BuilderNode[], nodeId: number): SubtreeRange {
  const start = nodes.findIndex((node) => node.id === nodeId);

  if (start < 0) {
    return { start: -1, end: -1 };
  }

  const rootLevel = nodes[start].level;
  const nodeMap = new Map(nodes.map((node) => [node.id, node]));
  let end = start + 1;

  while (end < nodes.length) {
    const candidate = nodes[end];

    if (candidate.level > rootLevel || isDescendantNode(candidate, nodeId, nodeMap)) {
      end += 1;
      continue;
    }

    break;
  }

  return { start, end };
}

function getSubtreeRelativeDepths(subtree: BuilderNode[], rootId: number): Map<number, number> {
  const subtreeIds = new Set(subtree.map((node) => node.id));
  const depthById = new Map<number, number>();
  depthById.set(rootId, 0);

  let changed = true;

  while (changed) {
    changed = false;

    for (const node of subtree) {
      if (node.id === rootId) {
        continue;
      }

      let parentId = node.parentId;

      if (!parentId || !subtreeIds.has(parentId)) {
        parentId = rootId;
      }

      const parentDepth = depthById.get(parentId);

      if (parentDepth === undefined) {
        continue;
      }

      const depth = parentDepth + 1;

      if (depthById.get(node.id) !== depth) {
        depthById.set(node.id, depth);
        changed = true;
      }
    }
  }

  const rootLevel = subtree[0].level;

  for (const node of subtree) {
    if (!depthById.has(node.id)) {
      depthById.set(node.id, Math.max(0, node.level - rootLevel));
    }
  }

  return depthById;
}

/** Reassign subtree levels from parentId hierarchy, not a flat level delta. */
export function adjustSubtreeToTargetRootLevel(
  subtree: BuilderNode[],
  targetRootLevel: number,
): BuilderNode[] {
  if (subtree.length === 0) {
    return subtree;
  }

  const rootId = subtree[0].id;
  const depthById = getSubtreeRelativeDepths(subtree, rootId);

  return subtree.map((node) => ({
    ...node,
    level: targetRootLevel + (depthById.get(node.id) ?? 0),
  }));
}

export function getSubtreeDepth(subtree: BuilderNode[], _rootLevel: number): number {
  if (subtree.length === 0) {
    return 0;
  }

  const rootId = subtree[0].id;
  const depthById = getSubtreeRelativeDepths(subtree, rootId);

  return Math.max(...subtree.map((node) => depthById.get(node.id) ?? 0));
}

export function getLevelBounds(
  nodes: BuilderNode[],
  insertIndex: number,
  subtreeDepth: number,
  maxLevels: number | null,
): LevelBounds {
  const prev = insertIndex > 0 ? nodes[insertIndex - 1] : null;
  const next = insertIndex < nodes.length ? nodes[insertIndex] : null;

  let min = next ? next.level : 1;
  let max = prev ? prev.level + 1 : 1;

  if (maxLevels !== null) {
    if (min !== 1 && min + subtreeDepth > maxLevels) {
      min = maxLevels - subtreeDepth;

      if (min < 1) {
        min = 1;
      }
    }

    if (max + subtreeDepth > maxLevels) {
      max = maxLevels - subtreeDepth;

      if (max < min) {
        max = min;
      }
    }
  }

  return { min, max };
}

export function levelFromHorizontalDelta(startLevel: number, deltaX: number): number {
  const levelDelta = Math.round(deltaX / NODE_LEVEL_INDENT_PX);

  return Math.max(1, startLevel + levelDelta);
}

export function clampTargetLevel(
  requestedLevel: number,
  bounds: LevelBounds,
): number {
  return Math.max(bounds.min, Math.min(bounds.max, requestedLevel));
}

export function getMoveContext(
  nodes: BuilderNode[],
  activeId: number,
  overId: number,
): MoveContext | null {
  const { start, end } = getSubtreeRange(nodes, activeId);

  if (start < 0) {
    return null;
  }

  const overIndex = nodes.findIndex((node) => node.id === overId);

  if (overIndex < 0 || (overIndex >= start && overIndex < end)) {
    return null;
  }

  const subtree = nodes.slice(start, end);
  const rootLevel = subtree[0].level;
  const remaining = [...nodes.slice(0, start), ...nodes.slice(end)];
  const insertIndex = remaining.findIndex((node) => node.id === overId);

  if (insertIndex < 0) {
    return null;
  }

  return {
    remaining,
    insertIndex,
    subtree,
    rootLevel,
    subtreeDepth: getSubtreeDepth(subtree, rootLevel),
  };
}

export function resolveTargetLevel(
  nodes: BuilderNode[],
  activeId: number,
  overId: number,
  requestedLevel: number,
  maxLevels: number | null,
): number | null {
  const context = getMoveContext(nodes, activeId, overId);

  if (!context) {
    return null;
  }

  const bounds = getLevelBounds(
    context.remaining,
    context.insertIndex,
    context.subtreeDepth,
    maxLevels,
  );

  return clampTargetLevel(requestedLevel, bounds);
}

export function resolveSamePositionTargetLevel(
  nodes: BuilderNode[],
  activeId: number,
  requestedLevel: number,
  maxLevels: number | null,
): number | null {
  const { start, end } = getSubtreeRange(nodes, activeId);

  if (start < 0) {
    return null;
  }

  const subtree = nodes.slice(start, end);
  const rootLevel = subtree[0].level;
  const remaining = [...nodes.slice(0, start), ...nodes.slice(end)];
  const bounds = getLevelBounds(
    remaining,
    start,
    getSubtreeDepth(subtree, rootLevel),
    maxLevels,
  );

  return clampTargetLevel(requestedLevel, bounds);
}

export function repositionNodeLevel(
  nodes: BuilderNode[],
  activeId: number,
  requestedLevel: number,
  maxLevels: number | null,
): BuilderNode[] {
  const { start, end } = getSubtreeRange(nodes, activeId);

  if (start < 0) {
    return nodes;
  }

  const subtree = nodes.slice(start, end);
  const rootLevel = subtree[0].level;
  const remaining = [...nodes.slice(0, start), ...nodes.slice(end)];
  const targetLevel = resolveSamePositionTargetLevel(nodes, activeId, requestedLevel, maxLevels);

  if (targetLevel === null || targetLevel === rootLevel) {
    return nodes;
  }

  const adjustedSubtree = adjustSubtreeToTargetRootLevel(subtree, targetLevel);

  const next = [
    ...remaining.slice(0, start),
    ...adjustedSubtree,
    ...remaining.slice(start),
  ];

  return recalculateLevels(next);
}

export function moveNode(
  nodes: BuilderNode[],
  activeId: number,
  overId: number,
  requestedLevel: number,
  maxLevels: number | null,
): BuilderNode[] {
  if (activeId === overId) {
    return nodes;
  }

  const context = getMoveContext(nodes, activeId, overId);

  if (!context) {
    return nodes;
  }

  const targetLevel = resolveTargetLevel(nodes, activeId, overId, requestedLevel, maxLevels);

  if (targetLevel === null) {
    return nodes;
  }

  const adjustedSubtree = adjustSubtreeToTargetRootLevel(context.subtree, targetLevel);

  const next = [
    ...context.remaining.slice(0, context.insertIndex),
    ...adjustedSubtree,
    ...context.remaining.slice(context.insertIndex),
  ];

  return recalculateLevels(next);
}

export function reorderNodes(nodes: BuilderNode[], activeId: number, overId: number): BuilderNode[] {
  const active = nodes.find((node) => node.id === activeId);

  return moveNode(nodes, activeId, overId, active?.level ?? 1, null);
}

export function indentNode(nodes: BuilderNode[], nodeId: number, maxLevels: number | null): BuilderNode[] {
  const index = nodes.findIndex((node) => node.id === nodeId);

  if (index <= 0) {
    return nodes;
  }

  const prev = nodes[index - 1];
  const targetLevel = prev.level + 1;

  if (maxLevels !== null && targetLevel > maxLevels) {
    return nodes;
  }

  return repositionNodeLevel(nodes, nodeId, targetLevel, maxLevels);
}

export function outdentNode(
  nodes: BuilderNode[],
  nodeId: number,
  maxLevels: number | null,
): BuilderNode[] {
  const index = nodes.findIndex((node) => node.id === nodeId);

  if (index <= 0) {
    return nodes;
  }

  const node = nodes[index];

  if (node.level <= 1) {
    return nodes;
  }

  let parentNode: BuilderNode | null = null;

  for (let i = index - 1; i >= 0; i -= 1) {
    if (nodes[i].level === node.level - 1) {
      parentNode = nodes[i];
      break;
    }
  }

  if (!parentNode) {
    return nodes;
  }

  const { start, end } = getSubtreeRange(nodes, nodeId);

  if (start < 0) {
    return nodes;
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
  const clampedLevel = clampTargetLevel(targetLevel, bounds);

  if (clampedLevel !== targetLevel) {
    return nodes;
  }

  const adjustedSubtree = adjustSubtreeToTargetRootLevel(subtree, clampedLevel);

  const next = [
    ...remaining.slice(0, insertIndex),
    ...adjustedSubtree,
    ...remaining.slice(insertIndex),
  ];

  return recalculateLevels(next);
}

export function recalculateLevels(nodes: BuilderNode[]): BuilderNode[] {
  const stack: Array<{ id: number; level: number }> = [];

  return nodes.map((node) => {
    while (stack.length && stack[stack.length - 1].level >= node.level) {
      stack.pop();
    }

    const parentId = stack.length ? stack[stack.length - 1].id : null;
    const normalized = { ...node, parentId, level: node.level };
    stack.push({ id: node.id, level: node.level });

    return normalized;
  });
}

export function movesEqual(a: StructureMove[], b: StructureMove[]): boolean {
  return JSON.stringify(a) === JSON.stringify(b);
}

export function baselineMoves(nodes: BuilderNode[]): StructureMove[] {
  return collectStructureMoves(nodes);
}

export function applyStructureMoves(nodes: BuilderNode[], moves: StructureMove[]): BuilderNode[] {
  if (moves.length === 0) {
    return nodes;
  }

  const byId = new Map(nodes.map((n) => [n.id, { ...n }]));
  const ordered: BuilderNode[] = [];

  for (const move of moves) {
    const node = byId.get(move.elementId);

    if (!node) {
      continue;
    }

    ordered.push(node);
    byId.delete(move.elementId);
  }

  for (const node of byId.values()) {
    ordered.push(node);
  }

  const withLevels = ordered.map((node) => {
    const move = moves.find((m) => m.elementId === node.id);

    if (!move) {
      return node;
    }

    const parentLevel = move.parentId
      ? (ordered.find((n) => n.id === move.parentId)?.level ?? 0)
      : 0;

    return {
      ...node,
      parentId: move.parentId,
      level: parentLevel + 1,
    };
  });

  return recalculateLevels(withLevels);
}

export type NodeDuplication = {
  sourceId: number;
  duplicateId: number;
};

/**
 * Reconcile server node payloads onto the client’s uncommitted tree order.
 *
 * Structure moves live client-side until Save — a naive refresh snaps rows back to
 * Craft structure. This keeps the staged order and inserts new roots (duplicates)
 * after their sources, then nests remaining new nodes under known parents.
 */
export function mergeServerNodesPreservingStructure(
  previousNodes: BuilderNode[],
  serverNodes: BuilderNode[],
  options: {
    preserveStructure?: boolean;
    duplications?: NodeDuplication[];
  } = {},
): BuilderNode[] {
  const preserveStructure = options.preserveStructure ?? true;
  const duplications = options.duplications ?? [];

  if (!preserveStructure && duplications.length === 0) {
    return serverNodes;
  }

  if (!preserveStructure) {
    const used = new Set<number>();
    return finalizeMergedNodes(
      insertDuplicationsIntoOrder(serverNodes, serverNodes, duplications, used),
      serverNodes,
      used,
    );
  }

  const serverById = new Map(serverNodes.map((node) => [node.id, node]));
  const used = new Set<number>();
  const ordered: BuilderNode[] = [];

  for (const previous of previousNodes) {
    const fresh = serverById.get(previous.id);

    if (!fresh) {
      continue;
    }

    ordered.push({
      ...fresh,
      level: previous.level,
      parentId: previous.parentId,
    });
    used.add(previous.id);
  }

  return finalizeMergedNodes(
    insertDuplicationsIntoOrder(ordered, serverNodes, duplications, used),
    serverNodes,
    used,
  );
}

function insertDuplicationsIntoOrder(
  ordered: BuilderNode[],
  serverNodes: BuilderNode[],
  duplications: NodeDuplication[],
  used: Set<number> = new Set(ordered.map((node) => node.id)),
): BuilderNode[] {
  const serverById = new Map(serverNodes.map((node) => [node.id, node]));
  let next = [...ordered];

  for (const { sourceId, duplicateId } of duplications) {
    if (used.has(duplicateId)) {
      continue;
    }

    const duplicate = serverById.get(duplicateId);

    if (!duplicate) {
      continue;
    }

    const sourceIndex = next.findIndex((node) => node.id === sourceId);

    if (sourceIndex < 0) {
      continue;
    }

    const source = next[sourceIndex];
    const { end } = getSubtreeRange(next, sourceId);
    next = [
      ...next.slice(0, end),
      {
        ...duplicate,
        level: source.level,
        parentId: source.parentId,
      },
      ...next.slice(end),
    ];
    used.add(duplicateId);
  }

  return next;
}

function finalizeMergedNodes(
  ordered: BuilderNode[],
  serverNodes: BuilderNode[],
  used: Set<number>,
): BuilderNode[] {
  let next = [...ordered];

  // Deep-duplicated children / other new nodes whose parent is already present.
  for (const node of serverNodes) {
    if (used.has(node.id)) {
      continue;
    }

    const parentId = node.parentId;

    if (!parentId || !used.has(parentId)) {
      continue;
    }

    const parentIndex = next.findIndex((candidate) => candidate.id === parentId);

    if (parentIndex < 0) {
      continue;
    }

    const parent = next[parentIndex];
    const { end } = getSubtreeRange(next, parentId);
    next = [
      ...next.slice(0, end),
      {
        ...node,
        level: parent.level + 1,
        parentId: parent.id,
      },
      ...next.slice(end),
    ];
    used.add(node.id);
  }

  for (const node of serverNodes) {
    if (!used.has(node.id)) {
      next.push(node);
      used.add(node.id);
    }
  }

  return normalizeTreeNodes(recalculateLevels(next));
}

function getCollapsedAncestorLevel(
  node: BuilderNode,
  nodeMap: Map<number, BuilderNode>,
  collapsedNodeIds: Record<number, boolean>,
): number | null {
  let parentId = node.parentId;

  while (parentId) {
    if (collapsedNodeIds[parentId]) {
      return nodeMap.get(parentId)?.level ?? null;
    }

    parentId = nodeMap.get(parentId)?.parentId ?? null;
  }

  return null;
}

export function filterVisibleNodes(
  nodes: BuilderNode[],
  collapsedNodeIds: Record<number, boolean>,
): BuilderNode[] {
  const nodeMap = new Map(nodes.map((node) => [node.id, node]));

  return nodes.filter((node) => getCollapsedAncestorLevel(node, nodeMap, collapsedNodeIds) === null);
}
