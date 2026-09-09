import { create } from 'zustand';
import type { BuilderNode, BuilderState, BuilderTab, NodeStatusFilter, StructureMove, TreeColumnId } from './types';
import type { DropPosition } from './utils/dropTarget';
import {
  fetchBuilderState,
  saveDraft,
  publishMenu,
  applyStructure,
  discardSession,
  stageDelete,
  unstageDelete,
  setNodeStatus,
  duplicateNodes,
  copyNodesToSite as copyNodesToSiteApi,
  displayError,
  displayNotice,
  t,
} from './api';
import {
  applyStructureMoves,
  baselineMoves,
  collectStructureMoves,
  indentNode,
  mergeServerNodesPreservingStructure,
  movesEqual,
  outdentNode,
  moveNode as applyNodeMove,
  repositionNodeLevel,
} from './utils/tree';
import { applyDropTarget } from './utils/dropTarget';
import { normalizeTreeNodes } from './utils/normalizeTreeNodes';
import { getNextSibling, getPreviousSibling } from './utils/nodeMoveActions';
import { computeNodeSelection, type NodeSelectionModifiers } from './utils/selection';
import {
  buildInitialCollapsedNodeIds,
  loadCollapsedNodeIds,
  saveCollapsedNodeIds,
} from './utils/collapsedNodesStorage';

type SaveFeedbackState = 'idle' | 'success' | 'error';

type CopyToSiteDialogState = {
  open: boolean;
  nodeIds: number[];
  includeDeepOption: boolean;
};

type BuilderStore = {
  menuId: number;
  siteId: number;
  loading: boolean;
  saving: boolean;
  publishing: boolean;
  discarding: boolean;
  saveFeedbackState: SaveFeedbackState;
  state: BuilderState | null;
  nodes: BuilderNode[];
  baselineStructureMoves: StructureMove[];
  structureDirty: boolean;
  selectedNodeIds: number[];
  lastSelectedNodeId: number | null;
  statusFilter: NodeStatusFilter;
  visibleColumns: Record<TreeColumnId, boolean>;
  collapsedNodeIds: Record<number, boolean>;
  activeTabId: string | null;
  menuContentOpen: boolean;
  copyToSiteDialog: CopyToSiteDialogState;
  error: string | null;

  init: (menuId: number, siteId: number) => Promise<void>;
  refresh: (options?: { resetStructure?: boolean }) => Promise<void>;
  setNodes: (nodes: BuilderNode[]) => void;
  reorder: (activeId: number, overId: number, targetLevel: number) => void;
  dropNode: (
    activeId: number,
    overId: number,
    position: DropPosition,
    targetLevel: number,
  ) => void;
  indent: (nodeId: number) => void;
  outdent: (nodeId: number) => void;
  moveNodeUp: (nodeId: number) => void;
  moveNodeDown: (nodeId: number) => void;
  toggleNodeSelection: (nodeId: number, selected: boolean) => void;
  selectNode: (
    nodeId: number,
    modifiers: NodeSelectionModifiers,
    visibleSelectableNodeIds: number[],
  ) => void;
  setSelectedNodeIds: (nodeIds: number[]) => void;
  toggleSelectAll: (selected: boolean) => void;
  clearSelection: () => void;
  setStatusFilter: (statusFilter: NodeStatusFilter) => void;
  setColumnVisible: (columnId: TreeColumnId, visible: boolean) => void;
  toggleNodeCollapsed: (nodeId: number) => void;
  expandNodeCollapsed: (nodeId: number) => void;
  setActiveTab: (tabId: string | null) => void;
  setMenuContentOpen: (open: boolean) => void;
  openCopyToSiteDialog: (nodeIds: number[], includeDeepOption?: boolean) => void;
  setCopyToSiteDialogOpen: (open: boolean) => void;
  saveDraft: () => Promise<void>;
  publish: () => Promise<void>;
  discard: () => Promise<void>;
  deleteNode: (nodeId: number, withDescendants?: boolean) => Promise<void>;
  deleteSelectedNodes: (withDescendants?: boolean) => Promise<void>;
  setSelectedNodesStatus: (status: 'enabled' | 'disabled') => Promise<void>;
  duplicateNode: (nodeId: number, deep?: boolean) => Promise<void>;
  duplicateSelectedNodes: (deep?: boolean) => Promise<void>;
  copyNodesToSite: (nodeIds: number[], targetSiteId: number, deep?: boolean, remapLinkedElements?: boolean) => Promise<void>;
  restoreNode: (nodeId: number) => Promise<void>;
  /** Hard-replace tree from server (clears structure dirty). Prefer applyMutationNodes for stage actions. */
  applyServerNodes: (nodes: BuilderNode[]) => void;
  /** Merge server payloads while preserving uncommitted client structure (A07). */
  applyMutationNodes: (
    previousNodes: BuilderNode[],
    serverNodes: BuilderNode[],
    session: BuilderState['session'] | Record<string, unknown> | null | undefined,
    structureWasDirty: boolean,
  ) => void;
  applyDuplicationResult: (
    previousNodes: BuilderNode[],
    serverNodes: BuilderNode[],
    duplications: Array<{ sourceId: number; duplicateId: number }>,
    session: BuilderState['session'] | undefined,
    structureWasDirty: boolean,
  ) => void;
  isDirty: () => boolean;
  getStructureMoves: () => StructureMove[];
  persistLiveStructure: (moves: StructureMove[]) => Promise<void>;
};

/** Monotonic seq so rapid live moves only commit the latest successful payload. */
let liveStructureSeq = 0;

export const useBuilderStore = create<BuilderStore>((set, get) => ({
  menuId: 0,
  siteId: 0,
  loading: true,
  saving: false,
  publishing: false,
  discarding: false,
  saveFeedbackState: 'idle',
  state: null,
  nodes: [],
  baselineStructureMoves: [],
  structureDirty: false,
  selectedNodeIds: [],
  lastSelectedNodeId: null,
  statusFilter: 'all',
  visibleColumns: { type: true },
  collapsedNodeIds: {},
  activeTabId: null,
  menuContentOpen: false,
  copyToSiteDialog: { open: false, nodeIds: [], includeDeepOption: false },
  error: null,

  init: async (menuId, siteId) => {
    set({ loading: true, menuId, siteId, error: null });

    try {
      const state = await fetchBuilderState(menuId, siteId);
      let nodes = state.nodes;

      if (state.session?.structureMoves?.length) {
        nodes = applyStructureMoves(nodes, state.session.structureMoves);
      }

      const baseline = baselineMoves(state.nodes);
      const collapsedNodeIds = buildInitialCollapsedNodeIds(nodes, menuId, siteId);

      set({
        state,
        nodes,
        baselineStructureMoves: baseline,
        structureDirty: state.session?.hasStructureMoves ?? false,
        activeTabId: state.builderTabs[0]?.id ?? null,
        collapsedNodeIds,
        loading: false,
        lastSelectedNodeId: null,
      });
    } catch (error) {
      displayError(error);
      set({ loading: false, error: t('Couldn’t load menu builder.') });
    }
  },

  refresh: async (options?: { resetStructure?: boolean }) => {
    const resetStructure = Boolean(options?.resetStructure);
    const { menuId, siteId, nodes: previousNodes, structureDirty } = get();

    try {
      const state = await fetchBuilderState(menuId, siteId);
      const baseline = baselineMoves(state.nodes);
      const collapsedNodeIds = loadCollapsedNodeIds(menuId, siteId, new Set(state.nodes.map((node) => node.id)));

      // Structure is client-staged until Save — do not snap back to Craft order on refresh,
      // unless discard/publish explicitly resets to the server baseline (A07).
      let nodes: BuilderNode[];
      let nextDirty: boolean;

      if (resetStructure) {
        nodes = state.nodes;
        nextDirty = false;
      } else if (structureDirty) {
        nodes = mergeServerNodesPreservingStructure(previousNodes, state.nodes, {
          preserveStructure: true,
        });
        nextDirty = true;
      } else if (state.session?.structureMoves?.length) {
        nodes = applyStructureMoves(state.nodes, state.session.structureMoves);
        nextDirty = Boolean(state.session?.hasStructureMoves);
      } else {
        nodes = state.nodes;
        nextDirty = Boolean(state.session?.hasStructureMoves);
      }

      set({
        state,
        nodes,
        baselineStructureMoves: baseline,
        structureDirty: nextDirty,
        collapsedNodeIds,
        selectedNodeIds: [],
        lastSelectedNodeId: null,
      });
    } catch (error) {
      displayError(error);
    }
  },

  setNodes: (nodes) => {
    const moves = collectStructureMoves(nodes);
    const { baselineStructureMoves, state } = get();
    const dirty = !movesEqual(moves, baselineStructureMoves);

    set({
      nodes,
      structureDirty: dirty,
    });

    // Live Structure Saves: no Save button — persist moves as soon as the tree changes.
    if (dirty && state && !state.stagingEnabled) {
      void get().persistLiveStructure(moves);
    }
  },

  persistLiveStructure: async (moves) => {
    const seq = ++liveStructureSeq;
    const { menuId, siteId } = get();

    try {
      const data = await applyStructure(menuId, siteId, moves);

      // A newer drag superseded this request — leave baseline to that persist.
      if (seq !== liveStructureSeq) {
        return;
      }

      set({
        baselineStructureMoves: moves,
        structureDirty: false,
      });
      displayNotice((data.message as string) ?? t('Menu structure saved.'));
    } catch (error) {
      if (seq !== liveStructureSeq) {
        return;
      }

      displayError(error);
      // Snap UI back to the last persisted structure.
      await get().refresh();
    }
  },

  reorder: (activeId, overId, targetLevel) => {
    const { nodes, state } = get();
    const maxLevels = state?.menu.maxLevels ?? null;

    if (activeId === overId) {
      get().setNodes(
        normalizeTreeNodes(repositionNodeLevel(nodes, activeId, targetLevel, maxLevels)),
      );
      return;
    }

    get().setNodes(
      normalizeTreeNodes(applyNodeMove(nodes, activeId, overId, targetLevel, maxLevels)),
    );
  },

  dropNode: (activeId, overId, position, targetLevel) => {
    const { nodes, state } = get();
    const maxLevels = state?.menu.maxLevels ?? null;

    get().setNodes(
      normalizeTreeNodes(applyDropTarget(nodes, activeId, overId, position, targetLevel, maxLevels)),
    );
  },

  indent: (nodeId) => {
    const { nodes, state } = get();
    const maxLevels = state?.menu.maxLevels ?? null;
    const index = nodes.findIndex((node) => node.id === nodeId);
    const previousRow = index > 0 ? nodes[index - 1] : null;
    const nextNodes = normalizeTreeNodes(indentNode(nodes, nodeId, maxLevels));

    if (previousRow && nextNodes.some((node) => node.id === nodeId)) {
      get().expandNodeCollapsed(previousRow.id);
    }

    get().setNodes(nextNodes);
  },

  outdent: (nodeId) => {
    const { nodes, state } = get();
    get().setNodes(
      normalizeTreeNodes(outdentNode(nodes, nodeId, state?.menu.maxLevels ?? null)),
    );
  },

  moveNodeUp: (nodeId) => {
    const { nodes } = get();
    const node = nodes.find((candidate) => candidate.id === nodeId);
    const previousSibling = getPreviousSibling(nodes, nodeId);

    if (!node || !previousSibling) {
      return;
    }

    get().dropNode(nodeId, previousSibling.id, 'before', node.level);
  },

  moveNodeDown: (nodeId) => {
    const { nodes } = get();
    const node = nodes.find((candidate) => candidate.id === nodeId);
    const nextSibling = getNextSibling(nodes, nodeId);

    if (!node || !nextSibling) {
      return;
    }

    get().dropNode(nodeId, nextSibling.id, 'after', node.level);
  },

  toggleNodeSelection: (nodeId, selected) => {
    const { selectedNodeIds } = get();

    set({
      selectedNodeIds: selected
        ? [...new Set([...selectedNodeIds, nodeId])]
        : selectedNodeIds.filter((id) => id !== nodeId),
      lastSelectedNodeId: nodeId,
    });
  },

  selectNode: (nodeId, modifiers, visibleSelectableNodeIds) => {
    const { selectedNodeIds, lastSelectedNodeId } = get();
    const next = computeNodeSelection(nodeId, visibleSelectableNodeIds, {
      selectedNodeIds,
      lastSelectedNodeId,
    }, modifiers);

    set(next);

    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
  },

  setSelectedNodeIds: (nodeIds) =>
    set({
      selectedNodeIds: nodeIds,
      lastSelectedNodeId: nodeIds.length ? nodeIds[nodeIds.length - 1] : null,
    }),

  toggleSelectAll: (selected) => {
    const selectableIds = get()
      .nodes.filter((node) => !node.pendingDelete)
      .map((node) => node.id);

    set({
      selectedNodeIds: selected ? selectableIds : [],
      lastSelectedNodeId: selected ? selectableIds[selectableIds.length - 1] ?? null : null,
    });
  },

  clearSelection: () => set({ selectedNodeIds: [], lastSelectedNodeId: null }),

  setStatusFilter: (statusFilter) => set({ statusFilter }),

  setColumnVisible: (columnId, visible) =>
    set((state) => ({
      visibleColumns: {
        ...state.visibleColumns,
        [columnId]: visible,
      },
    })),

  toggleNodeCollapsed: (nodeId) => {
    const { collapsedNodeIds, menuId, siteId } = get();
    const nextCollapsedNodeIds = { ...collapsedNodeIds };

    if (nextCollapsedNodeIds[nodeId]) {
      delete nextCollapsedNodeIds[nodeId];
    } else {
      nextCollapsedNodeIds[nodeId] = true;
    }

    set({ collapsedNodeIds: nextCollapsedNodeIds });
    saveCollapsedNodeIds(menuId, siteId, nextCollapsedNodeIds);
  },

  expandNodeCollapsed: (nodeId) => {
    const { collapsedNodeIds, menuId, siteId } = get();

    if (!collapsedNodeIds[nodeId]) {
      return;
    }

    const nextCollapsedNodeIds = { ...collapsedNodeIds };
    delete nextCollapsedNodeIds[nodeId];

    set({ collapsedNodeIds: nextCollapsedNodeIds });
    saveCollapsedNodeIds(menuId, siteId, nextCollapsedNodeIds);
  },

  setActiveTab: (tabId) => set({ activeTabId: tabId }),

  setMenuContentOpen: (open) => set({ menuContentOpen: open }),

  openCopyToSiteDialog: (nodeIds, includeDeepOption = false) => {
    set({
      copyToSiteDialog: {
        open: true,
        nodeIds,
        includeDeepOption,
      },
    });
  },

  setCopyToSiteDialogOpen: (open) => {
    set((state) => ({
      copyToSiteDialog: {
        ...state.copyToSiteDialog,
        open,
        nodeIds: open ? state.copyToSiteDialog.nodeIds : [],
      },
    }));
  },

  getStructureMoves: () => collectStructureMoves(get().nodes),

  isDirty: () => {
    const { structureDirty, state } = get();
    const sessionCount = state?.session?.changeCount ?? 0;

    return structureDirty || sessionCount > 0;
  },

  saveDraft: async () => {
    const { menuId, siteId, structureDirty } = get();

    if (!structureDirty) {
      displayNotice(t('Nothing to save.'));
      return;
    }

    set({ saving: true });

    try {
      const moves = get().getStructureMoves();
      const data = await saveDraft(menuId, siteId, moves);

      displayNotice((data.message as string) ?? t('Draft saved.'));
      await get().refresh();
    } catch (error) {
      displayError(error);
    } finally {
      set({ saving: false });
    }
  },

  publish: async () => {
    const { menuId, siteId, structureDirty } = get();

    set({ publishing: true, saveFeedbackState: 'idle' });

    try {
      const moves = get().getStructureMoves();
      const data = await publishMenu(menuId, siteId, structureDirty, moves);

      displayNotice((data.message as string) ?? t('Menu saved.'));
      set({ saveFeedbackState: 'success' });

      if (data.reload) {
        window.location.reload();
        return;
      }

      // Publish commits structure — take the server baseline, do not preserve dirty moves.
      await get().refresh({ resetStructure: true });
    } catch (error) {
      set({ saveFeedbackState: 'error' });
      displayError(error);
    } finally {
      set({ publishing: false });
    }
  },

  discard: async () => {
    if (!confirm(t('Discard all unsaved changes to this menu?'))) {
      return;
    }

    const { menuId, siteId } = get();

    set({ discarding: true });

    try {
      const data = await discardSession(menuId, siteId);
      displayNotice((data.message as string) ?? t('Build session discarded.'));
      // Discard must clear client structure dirty state (opposite of refresh-while-dirty).
      await get().refresh({ resetStructure: true });
    } catch (error) {
      displayError(error);
    } finally {
      set({ discarding: false });
    }
  },

  deleteNode: async (nodeId, withDescendants = false) => {
    const { menuId, siteId, nodes: previousNodes, structureDirty } = get();

    try {
      const data = await stageDelete(menuId, siteId, nodeId, withDescendants);
      displayNotice(data.message ?? t('Node deleted.'));

      if (data.nodes) {
        get().applyMutationNodes(previousNodes, data.nodes, data.session, structureDirty);
      } else {
        await get().refresh();
      }
    } catch (error) {
      displayError(error);
    }
  },

  deleteSelectedNodes: async (withDescendants = false) => {
    const { selectedNodeIds, nodes } = get();
    const selectedSet = new Set(selectedNodeIds);
    // When deleting with descendants, skip selected nodes that sit under another
    // selected ancestor — otherwise the parent wipe makes child requests fail loudly.
    const orderedIds = nodes
      .filter((node) => {
        if (!selectedSet.has(node.id)) {
          return false;
        }

        if (!withDescendants) {
          return true;
        }

        let parentId = node.parentId;

        while (parentId) {
          if (selectedSet.has(parentId)) {
            return false;
          }

          parentId = nodes.find((candidate) => candidate.id === parentId)?.parentId ?? null;
        }

        return true;
      })
      .map((node) => node.id);

    let firstError: unknown = null;
    let lastMessage: string | undefined;
    let successCount = 0;

    for (const nodeId of orderedIds) {
      try {
        const { menuId, siteId, nodes: previousNodes, structureDirty } = get();
        const data = await stageDelete(menuId, siteId, nodeId, withDescendants);
        successCount += 1;
        lastMessage = data.message;

        if (data.nodes) {
          get().applyMutationNodes(previousNodes, data.nodes, data.session, structureDirty);
        } else {
          await get().refresh();
        }
      } catch (error) {
        firstError ??= error;
      }
    }

    set({ selectedNodeIds: [], lastSelectedNodeId: null });

    if (firstError) {
      displayError(firstError);
    } else if (successCount > 0) {
      // One toast for the batch — live mode is immediate; staging still needs Save.
      displayNotice(
        lastMessage
          ?? t('Node{plural} deleted.', { plural: successCount > 1 ? 's' : '' }),
      );
    }
  },

  setSelectedNodesStatus: async (status) => {
    const { menuId, siteId, selectedNodeIds, nodes: previousNodes, structureDirty } = get();

    if (!selectedNodeIds.length) {
      return;
    }

    try {
      const data = await setNodeStatus(menuId, siteId, selectedNodeIds, status);
      displayNotice(data.message ?? t('Status updated.'));

      if (data.nodes) {
        get().applyMutationNodes(previousNodes, data.nodes, undefined, structureDirty);
      } else {
        await get().refresh();
      }
    } catch (error) {
      displayError(error);
    }
  },

  duplicateNode: async (nodeId, deep = false) => {
    const { menuId, siteId, nodes: previousNodes, structureDirty } = get();

    try {
      const data = await duplicateNodes(menuId, siteId, [nodeId], deep);
      displayNotice(data.message ?? t('Elements duplicated.'));

      if (data.nodes) {
        get().applyDuplicationResult(previousNodes, data.nodes, data.duplications ?? [], data.session, structureDirty);
        return;
      }

      await get().refresh();
    } catch (error) {
      displayError(error);
    }
  },

  duplicateSelectedNodes: async (deep = false) => {
    const { menuId, siteId, selectedNodeIds, nodes: previousNodes, structureDirty } = get();

    if (!selectedNodeIds.length) {
      return;
    }

    try {
      const data = await duplicateNodes(menuId, siteId, selectedNodeIds, deep);
      displayNotice(data.message ?? t('Elements duplicated.'));

      if (data.nodes) {
        get().applyDuplicationResult(previousNodes, data.nodes, data.duplications ?? [], data.session, structureDirty);
        return;
      }

      await get().refresh();
    } catch (error) {
      displayError(error);
    }
  },

  copyNodesToSite: async (nodeIds, targetSiteId, deep = false, remapLinkedElements = false) => {
    if (!nodeIds.length) {
      return;
    }

    const { menuId, siteId } = get();

    try {
      const data = await copyNodesToSiteApi(menuId, siteId, nodeIds, targetSiteId, deep, remapLinkedElements);

      displayNotice(
        data.message
          ?? (nodeIds.length === 1
            ? t('Node copied to site.')
            : t('{count} nodes copied to site.', { count: nodeIds.length })),
      );
      await get().refresh();
    } catch (error) {
      displayError(error);
    }
  },

  restoreNode: async (nodeId) => {
    const { menuId, siteId, nodes: previousNodes, structureDirty } = get();

    try {
      const data = await unstageDelete(menuId, siteId, nodeId);
      displayNotice(data.message ?? t('Node restored to menu.'));

      if (data.nodes) {
        get().applyMutationNodes(previousNodes, data.nodes, undefined, structureDirty);
      } else {
        await get().refresh();
      }
    } catch (error) {
      displayError(error);
    }
  },

  applyServerNodes: (nodes) => {
    const baseline = baselineMoves(nodes);
    set({
      nodes,
      baselineStructureMoves: baseline,
      structureDirty: false,
      selectedNodeIds: [],
      lastSelectedNodeId: null,
    });
  },

  applyMutationNodes: (previousNodes, serverNodes, session, structureWasDirty) => {
    const nodes = mergeServerNodesPreservingStructure(previousNodes, serverNodes, {
      preserveStructure: structureWasDirty,
    });
    const baseline = baselineMoves(serverNodes);
    const { state } = get();

    set({
      nodes,
      baselineStructureMoves: baseline,
      structureDirty: structureWasDirty || !movesEqual(collectStructureMoves(nodes), baseline),
      state:
        state && session
          ? { ...state, session: session as BuilderState['session'] }
          : state,
      selectedNodeIds: [],
      lastSelectedNodeId: null,
    });
  },

  applyDuplicationResult: (previousNodes, serverNodes, duplications, session, structureWasDirty) => {
    const nodes = mergeServerNodesPreservingStructure(previousNodes, serverNodes, {
      // Always keep the on-screen order — duplicate places next to DB position otherwise.
      preserveStructure: true,
      duplications,
    });
    const baseline = baselineMoves(serverNodes);
    const { state } = get();

    set({
      nodes,
      baselineStructureMoves: baseline,
      structureDirty: structureWasDirty || !movesEqual(collectStructureMoves(nodes), baseline),
      state: state && session ? { ...state, session } : state,
      selectedNodeIds: [],
      lastSelectedNodeId: null,
    });
  },
}));

export function useBuilderTabs(): BuilderTab[] {
  return useBuilderStore((s) => s.state?.builderTabs ?? EMPTY_BUILDER_TABS);
}

const EMPTY_BUILDER_TABS: BuilderTab[] = [];
