export type CollapsedNodeIds = Record<number, boolean>;

/** Collapse parent nodes by default when the tree exceeds this size. */
export const LAZY_TREE_COLLAPSE_THRESHOLD = 100;

function getStorageKey(menuId: number, siteId: number): string {
  return `navigation.builder.${menuId}.${siteId}.collapsedNodes`;
}

function readRawStorage(key: string): unknown {
  const craft = (window as Window & {
    Craft?: {
      getLocalStorage?: (storageKey: string, defaultValue?: unknown) => unknown;
    };
  }).Craft;

  if (craft?.getLocalStorage) {
    // Keep a missing value distinct from an explicitly saved empty object,
    // which represents a large tree whose parents are all expanded.
    return craft.getLocalStorage(key, null);
  }

  try {
    const raw = localStorage.getItem(key);

    if (raw === null) {
      return null;
    }

    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function writeRawStorage(key: string, value: CollapsedNodeIds): void {
  const craft = (window as Window & {
    Craft?: {
      setLocalStorage?: (storageKey: string, storageValue: unknown) => void;
    };
  }).Craft;

  if (craft?.setLocalStorage) {
    craft.setLocalStorage(key, value);
    return;
  }

  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch {
    // Ignore quota or privacy mode errors.
  }
}

export function normalizeCollapsedNodeIds(
  raw: unknown,
  validNodeIds?: Set<number>,
): CollapsedNodeIds {
  if (!raw || typeof raw !== 'object') {
    return {};
  }

  const result: CollapsedNodeIds = {};

  for (const [key, value] of Object.entries(raw as Record<string, unknown>)) {
    if (!value) {
      continue;
    }

    const nodeId = Number(key);

    if (!Number.isFinite(nodeId)) {
      continue;
    }

    if (validNodeIds && !validNodeIds.has(nodeId)) {
      continue;
    }

    result[nodeId] = true;
  }

  return result;
}

export function loadCollapsedNodeIds(
  menuId: number,
  siteId: number,
  validNodeIds?: Set<number>,
): CollapsedNodeIds {
  if (!menuId || !siteId) {
    return {};
  }

  return normalizeCollapsedNodeIds(readRawStorage(getStorageKey(menuId, siteId)), validNodeIds);
}

type CollapsibleNode = {
  id: number;
  hasDescendants: boolean;
};

/**
 * Uses saved collapse preferences when present; otherwise collapses large trees by default.
 */
export function buildInitialCollapsedNodeIds(
  nodes: CollapsibleNode[],
  menuId: number,
  siteId: number,
): CollapsedNodeIds {
  const validNodeIds = new Set(nodes.map((node) => node.id));
  const storageKey = getStorageKey(menuId, siteId);
  const stored = readRawStorage(storageKey);

  if (stored !== null) {
    return normalizeCollapsedNodeIds(stored, validNodeIds);
  }

  if (nodes.length <= LAZY_TREE_COLLAPSE_THRESHOLD) {
    return {};
  }

  const collapsed: CollapsedNodeIds = {};

  for (const node of nodes) {
    if (node.hasDescendants) {
      collapsed[node.id] = true;
    }
  }

  return collapsed;
}

export function saveCollapsedNodeIds(
  menuId: number,
  siteId: number,
  collapsedNodeIds: CollapsedNodeIds,
): void {
  if (!menuId || !siteId) {
    return;
  }

  const persisted: CollapsedNodeIds = {};

  for (const [nodeId, collapsed] of Object.entries(collapsedNodeIds)) {
    if (collapsed) {
      persisted[Number(nodeId)] = true;
    }
  }

  writeRawStorage(getStorageKey(menuId, siteId), persisted);
}
