import { useEffect, useMemo } from 'react';
import {
  dragAndDropFeature,
  syncDataLoaderFeature,
  type DragTarget,
  type ItemInstance,
} from '@headless-tree/core';
import { useTree } from '@headless-tree/react';
import { Checkbox } from '@verbb/plugin-kit-react/components/Checkbox';
import { cn } from '../utils/cn';
import { useBuilderStore } from '../store';
import { filterNodesByStatus } from '../utils/tree';
import { type PkCheckedChangeEvent } from '../utils/pluginKitEvents';
import { t } from '../api';
import {
  NAVIGATION_TREE_ROOT_ID,
  buildNavigationTreeChildrenMap,
  flattenNavigationTree,
  getExpandedTreeItemIds,
  mergeReorderedTreeNodes,
  refreshHasDescendants,
  removeIdsFromChildrenMap,
} from '../utils/headlessTreeData';
import { NODE_LEVEL_INDENT_PX, nodeTreeGridClass } from '../utils/nodeRowLayout';
import type { BuilderNode } from '../types';
import { NodeRow } from './NodeRow';
import { TreeDragLine } from './TreeDragLine';
import { useNavigationDragSession } from '../hooks/useNavigationDragSession';
import { useNodeTreeKeyboardShortcuts } from '../hooks/useNodeTreeKeyboardShortcuts';

const ROOT_NODE: BuilderNode = {
  id: 0,
  title: 'Root',
  type: 'root',
  typeLabel: '',
  typeClass: '',
  typeColorRgb: '',
  typeTextColorRgb: '',
  url: null,
  level: 0,
  parentId: null,
  status: 'live',
  newWindow: false,
  classes: null,
  pendingAdd: false,
  pendingDelete: false,
  pendingEdit: false,
  enabled: true,
  enabledForSite: true,
  hasDescendants: true,
  isElementLinked: false,
};

export function NodeTree() {
  const allNodes = useBuilderStore((s) => s.nodes);
  const maxLevels = useBuilderStore((s) => s.state?.menu.maxLevels ?? null);
  const collapsedNodeIds = useBuilderStore((s) => s.collapsedNodeIds);
  const statusFilter = useBuilderStore((s) => s.statusFilter);
  const visibleColumns = useBuilderStore((s) => s.visibleColumns);
  const setNodes = useBuilderStore((s) => s.setNodes);
  const selectedNodeIds = useBuilderStore((s) => s.selectedNodeIds);
  const setSelectedNodeIds = useBuilderStore((s) => s.setSelectedNodeIds);
  const clearSelection = useBuilderStore((s) => s.clearSelection);

  const treeNodes = useMemo(
    () => filterNodesByStatus(allNodes, statusFilter),
    [allNodes, statusFilter],
  );

  const treeNodeIds = useMemo(() => new Set(treeNodes.map((node) => node.id)), [treeNodes]);
  const nodeMap = useMemo(() => new Map(allNodes.map((node) => [node.id, node])), [allNodes]);
  const childrenMap = useMemo(() => buildNavigationTreeChildrenMap(treeNodes), [treeNodes]);

  const expandedItems = useMemo(
    () => getExpandedTreeItemIds(treeNodes, collapsedNodeIds),
    [treeNodes, collapsedNodeIds],
  );

  const selectableNodes = treeNodes.filter((node) => !node.pendingDelete);
  const visibleSelectableNodeIds = selectableNodes.map((node) => node.id);
  const allSelected = selectableNodes.length > 0 && selectedNodeIds.length === selectableNodes.length;
  const showTypeColumn = visibleColumns.type;

  const applyTreeStructure = (nextChildrenMap: typeof childrenMap) => {
    const flattened = refreshHasDescendants(
      flattenNavigationTree(nodeMap, nextChildrenMap),
    );

    if (treeNodeIds.size === allNodes.length) {
      setNodes(flattened);
      return;
    }

    setNodes(mergeReorderedTreeNodes(allNodes, treeNodeIds, flattened));
  };

  const handleDrop = (items: ItemInstance<BuilderNode>[], target: DragTarget<BuilderNode>) => {
    const draggedIds = new Set(items.map((item) => item.getId()));
    let nextMap = removeIdsFromChildrenMap(childrenMap, draggedIds);

    const targetParentId = target.item.getId();
    const currentChildren = [...(nextMap[targetParentId] ?? [])];
    const draggedIdList = items.map((item) => item.getId());

    if ('childIndex' in target) {
      nextMap[targetParentId] = [
        ...currentChildren.slice(0, target.insertionIndex),
        ...draggedIdList,
        ...currentChildren.slice(target.insertionIndex),
      ];
    } else {
      nextMap[targetParentId] = [...currentChildren, ...draggedIdList];
    }

    applyTreeStructure(nextMap);
  };

  const tree = useTree<BuilderNode>({
    rootItemId: NAVIGATION_TREE_ROOT_ID,
    state: {
      expandedItems,
    },
    setExpandedItems: (updater) => {
      const { expandNodeCollapsed, toggleNodeCollapsed, collapsedNodeIds: storedCollapsed } =
        useBuilderStore.getState();
      const prev = getExpandedTreeItemIds(treeNodes, storedCollapsed);
      const next = typeof updater === 'function' ? updater(prev) : updater;
      const nextExpanded = new Set(next);

      for (const node of treeNodes) {
        if (!node.hasDescendants) {
          continue;
        }

        const id = node.id;
        const shouldBeExpanded = nextExpanded.has(String(id));
        const isCollapsed = Boolean(storedCollapsed[id]);

        if (shouldBeExpanded && isCollapsed) {
          expandNodeCollapsed(id);
        } else if (!shouldBeExpanded && !isCollapsed) {
          toggleNodeCollapsed(id);
        }
      }
    },
    getItemName: (item) => item.getItemData()?.title || '(Untitled)',
    isItemFolder: (item) => {
      const nodeLevel = item.getItemMeta().level + 1;

      return maxLevels === null || nodeLevel < maxLevels;
    },
    dataLoader: {
      getItem: (itemId) => {
        if (itemId === NAVIGATION_TREE_ROOT_ID) {
          return ROOT_NODE;
        }

        return nodeMap.get(Number(itemId)) ?? ROOT_NODE;
      },
      getChildren: (itemId) => childrenMap[itemId] ?? [],
    },
    indent: NODE_LEVEL_INDENT_PX,
    features: [syncDataLoaderFeature, dragAndDropFeature],
    seperateDragHandle: true,
    // Top/bottom edge bands for insert-line; middle band is drop-as-child (default 0.3).
    reorderAreaPercentage: 0.2,
    openOnDropDelay: 500,
    canDrop: (items, target) => {
      if ('dragLineLevel' in target) {
        const targetLevel = target.dragLineLevel + 1;
        const draggedRoot = items[0];
        const draggedNode = draggedRoot?.getItemData();

        if (!draggedNode) {
          return true;
        }

        const subtreeDepth = getSubtreeDepthFromMap(
          childrenMap,
          draggedRoot.getId(),
          draggedNode.level,
        );

        if (maxLevels !== null && targetLevel + subtreeDepth - 1 > maxLevels) {
          return false;
        }
      }

      return true;
    },
    canDrag: (items) => items.every((item) => !item.getItemData()?.pendingDelete),
    createForeignDragObject: (items) => ({
      format: 'application/x-navigation-node',
      data: items.map((item) => item.getId()).join(','),
      effectAllowed: 'move',
    }),
    setDragImage: () => {
      const element = document.createElement('div');
      element.style.width = '1px';
      element.style.height = '1px';
      element.style.opacity = '0';
      element.style.position = 'fixed';
      element.style.top = '-9999px';
      document.body.appendChild(element);

      return {
        imgElement: element,
        xOffset: 0,
        yOffset: 0,
      };
    },
    onDrop: handleDrop,
  });

  useEffect(() => {
    tree.rebuildTree();
  }, [tree, childrenMap, expandedItems, treeNodes]);

  const draggedIds = useMemo(() => {
    const dragged = tree.getState().dnd?.draggedItems;

    if (!dragged?.length) {
      return new Set<string>();
    }

    return new Set(dragged.map((item) => item.getId()));
  }, [tree, tree.getState().dnd]);

  const isDragSession = Boolean(tree.getState().dnd);
  const { linePosition, nestTargetId } = useNavigationDragSession(
    tree,
    showTypeColumn,
    isDragSession,
  );

  useNodeTreeKeyboardShortcuts(!isDragSession && treeNodes.length > 0);

  if (!allNodes.length) {
    return (
      <div className="flex min-h-[320px] items-center justify-center border border-dashed border-gray-200 p-12 text-sm text-gray-500">
        <p>{t('No nodes yet. Use the sidebar to add your first node.')}</p>
      </div>
    );
  }

  const items = tree.getItems().filter((item) => item.getId() !== NAVIGATION_TREE_ROOT_ID);
  const containerProps = tree.getContainerProps(t('Navigation nodes'));
  const { onDragOver: treeOnDragOver, ...treeContainerProps } = containerProps;

  return (
    <div className="overflow-x-auto">
      <div role="table" className="min-w-[640px] text-sm">
        <div
          role="rowgroup"
          className={nodeTreeGridClass(
            showTypeColumn,
            'bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500',
          )}
        >
          <div role="columnheader" className="px-3 py-2.5">
            <Checkbox
              aria-label={t('Select all')}
              checked={allSelected}
              onPkChange={(event) => {
                if ((event as PkCheckedChangeEvent).detail.checked) {
                  setSelectedNodeIds(selectableNodes.map((node) => node.id));
                } else {
                  clearSelection();
                }
              }}
            />
          </div>
          <div role="columnheader" className="px-3 py-2.5 font-medium normal-case">
            {t('Node')}
          </div>
          {showTypeColumn && (
            <div role="columnheader" className="px-3 py-2.5 text-right font-medium normal-case">
              {t('Type')}
            </div>
          )}
          <div role="columnheader" className="sticky right-0 z-10 bg-gray-50 py-2.5" aria-hidden />
        </div>

        <div role="rowgroup" className="select-none">
          {items.length === 0 ? (
            <div className="py-4 text-center text-sm text-gray-500">
              {t('No nodes match the selected status.')}
            </div>
          ) : (
            <div
              {...treeContainerProps}
              className={cn('pb-8', isDragSession && 'cursor-grabbing')}
              onDragOver={(event) => {
                treeOnDragOver?.(event);
                if (event.dataTransfer) {
                  event.dataTransfer.dropEffect = 'move';
                }
              }}
            >
              <TreeDragLine position={linePosition} />
              {items.map((item) => (
                <HeadlessTreeRow
                  key={item.getId()}
                  item={item}
                  showTypeColumn={showTypeColumn}
                  visibleSelectableNodeIds={visibleSelectableNodeIds}
                  isDragging={draggedIds.has(item.getId())}
                  isDragSession={isDragSession}
                  isDropNestTarget={nestTargetId === item.getId()}
                />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

type RowProps = {
  item: ItemInstance<BuilderNode>;
  showTypeColumn: boolean;
  visibleSelectableNodeIds: number[];
  isDragging: boolean;
  isDragSession: boolean;
  isDropNestTarget: boolean;
};

function HeadlessTreeRow({
  item,
  showTypeColumn,
  visibleSelectableNodeIds,
  isDragging,
  isDragSession,
  isDropNestTarget,
}: RowProps) {
  const node = item.getItemData();
  const itemProps = item.getProps();

  if (!node || node.id === 0) {
    return null;
  }

  return (
    <NodeRow
      node={node}
      item={item}
      itemProps={itemProps}
      showTypeColumn={showTypeColumn}
      visibleSelectableNodeIds={visibleSelectableNodeIds}
      isDragging={isDragging}
      isDragSession={isDragSession}
      isDropNestTarget={isDropNestTarget}
    />
  );
}

function getSubtreeDepthFromMap(
  childrenMap: Record<string, string[]>,
  rootId: string,
  rootLevel: number,
): number {
  const childIds = childrenMap[rootId] ?? [];

  if (childIds.length === 0) {
    return 1;
  }

  return 1 + Math.max(
    ...childIds.map((childId) => {
      const childLevel = rootLevel + 1;

      return getSubtreeDepthFromMap(childrenMap, childId, childLevel);
    }),
  );
}
