import { create } from 'zustand';
import type { BuilderNode, BuilderState, BuilderTab, NodeStatusFilter, StructureMove, TreeColumnId } from './types';
import type { DropPosition } from './utils/dropTarget';
import {
  fetchBuilderState,
  saveDraft,
  publishMenu,
  discardSession,
  stageDelete,
  unstageDelete,
  setNodeStatus,
  duplicateNodes,
  copyNodesToSite as copyNodesToSiteApi,
  displayError,
  t,
} from './api';
import { getCraft } from './utils/cp';
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
  refresh: () => Promise<void>;
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
  applyServerNodes: (nodes: BuilderNode[]) => void;
  applyDuplicationResult: (
    previousNodes: BuilderNode[],
    serverNodes: BuilderNode[],
    duplications: Array<{ sourceId: number; duplicateId: number }>,
    session: BuilderState['session'] | undefined,
    structureWasDirty: boolean,
  ) => void;
  isDirty: () => boolean;
  getStructureMoves: () => StructureMove[];
};

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

  refresh: async () => {
    const { menuId, siteId, nodes: previousNodes, structureDirty } = get();

    try {
      const state = await fetchBuilderState(menuId, siteId);
      const baseline = baselineMoves(state.nodes);
      const collapsedNodeIds = loadCollapsedNodeIds(menuId, siteId, new Set(state.nodes.map((node) => node.id)));

      // Structure is client-staged until Save — do not snap back to Craft order on refresh.
      let nodes: BuilderNode[];

      if (structureDirty) {
        nodes = mergeServerNodesPreservingStructure(previousNodes, state.nodes, {
          preserveStructure: true,
        });
      } else if (state.session?.structureMoves?.length) {
        nodes = applyStructureMoves(state.nodes, state.session.structureMoves);
      } else {
        nodes = state.nodes;
      }

      set({
        state,
        nodes,
        baselineStructureMoves: baseline,
        structureDirty: structureDirty || Boolean(state.session?.hasStructureMoves),
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
    const { baselineStructureMoves } = get();

    set({
      nodes,
      structureDirty: !movesEqual(moves, baselineStructureMoves),
    });
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
      getCraft().cp.displayNotice(t('Nothing to save.'));
      return;
    }

    set({ saving: true });

    try {
      const moves = get().getStructureMoves();
      const data = await saveDraft(menuId, siteId, moves);

      getCraft().cp.displayNotice((data.message as string) ?? t('Draft saved.'));
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

      getCraft().cp.displayNotice((data.message as string) ?? t('Menu saved.'));
      set({ saveFeedbackState: 'success' });

      if (data.reload) {
        window.location.reload();
        return;
      }

      await get().refresh();
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
      getCraft().cp.displayNotice((data.message as string) ?? t('Build session discarded.'));
      await get().refresh();
    } catch (error) {
      displayError(error);
    } finally {
      set({ discarding: false });
    }
  },

  deleteNode: async (nodeId, withDescendants = false) => {
    const { menuId, siteId } = get();

    try {
      const data = await stageDelete(menuId, siteId, nodeId, withDescendants);
      getCraft().cp.displayNotice(data.message ?? t('Node staged for deletion.'));

      if (data.nodes) {
        get().applyServerNodes(data.nodes);
      } else {
        await get().refresh();
      }
    } catch (error) {
      displayError(error);
    }
  },

  deleteSelectedNodes: async (withDescendants = false) => {
    const { selectedNodeIds, nodes } = get();
    const orderedIds = nodes
      .filter((node) => selectedNodeIds.includes(node.id))
      .map((node) => node.id);

    for (const nodeId of orderedIds) {
      await get().deleteNode(nodeId, withDescendants);
    }

    set({ selectedNodeIds: [], lastSelectedNodeId: null });
  },

  setSelectedNodesStatus: async (status) => {
    const { menuId, siteId, selectedNodeIds } = get();

    if (!selectedNodeIds.length) {
      return;
    }

    try {
      const data = await setNodeStatus(menuId, siteId, selectedNodeIds, status);
      getCraft().cp.displayNotice(data.message ?? t('Status updated.'));

      if (data.nodes) {
        get().applyServerNodes(data.nodes);
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
      getCraft().cp.displayNotice(data.message ?? t('Elements duplicated.'));

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
      getCraft().cp.displayNotice(data.message ?? t('Elements duplicated.'));

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

      getCraft().cp.displayNotice(
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
    const { menuId, siteId } = get();

    try {
      const data = await unstageDelete(menuId, siteId, nodeId);
      getCraft().cp.displayNotice(t('Node restored to menu.'));

      if (data.nodes) {
        get().applyServerNodes(data.nodes);
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
