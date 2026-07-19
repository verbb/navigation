import { useCallback, useEffect, useRef, type DragEvent, type MouseEvent } from 'react';
import type { ItemInstance } from '@headless-tree/core';
import { Button } from '@verbb/plugin-kit-react/components/Button';
import { Checkbox } from '@verbb/plugin-kit-react/components/Checkbox';
import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { Status } from '@verbb/plugin-kit-react/components/Status';
import { cn } from '../utils/cn';
import { asPkStatusVariant } from '../utils/pluginKitEvents';
import type { BuilderNode } from '../types';
import { useBuilderStore } from '../store';
import { t } from '../api';
import { openNodeEditor } from '../utils/craft';
import { isRowInteractiveTarget } from '../utils/selection';
import { getNodeRowPaddingLeft, nodeTreeGridClass } from '../utils/nodeRowLayout';
import { NodeTypeBadge } from './NodeTypeBadge';
import { NodeRowActionsMenu } from './NodeRowActionsMenu';

type Props = {
  node: BuilderNode;
  item: ItemInstance<BuilderNode>;
  itemProps: Record<string, unknown>;
  showTypeColumn?: boolean;
  visibleSelectableNodeIds: number[];
  isDragging?: boolean;
  isDragSession?: boolean;
  isDropNestTarget?: boolean;
};

function GripIcon({ className, slot }: { className?: string; slot?: string }) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 448 512"
      focusable="false"
      aria-hidden="true"
      className={className}
      slot={slot}
    >
      <path
        fill="currentColor"
        d="M71.3 295.6c-21.9-21.9-21.9-57.3 0-79.2s57.3-21.9 79.2 0 21.9 57.3 0 79.2s-57.4 21.9-79.2 0zM184.4 182.5c-21.9-21.9-21.9-57.3 0-79.2s57.3-21.9 79.2 0 21.9 57.3 0 79.2-57.3 21.8-79.2 0zm0 147c21.9-21.9 57.3-21.9 79.2 0s21.9 57.3 0 79.2s-57.3 21.9-79.2 0c-21.9-21.8-21.9-57.3 0-79.2zM297.5 216.4c21.9-21.9 57.3-21.9 79.2 0s21.9 57.3 0 79.2s-57.3 21.9-79.2 0c-21.8-21.9-21.8-57.3 0-79.2z"
      />
    </svg>
  );
}

function nodeStatusIndicator(node: BuilderNode): string {
  if (node.pendingDelete) {
    return 'disabled';
  }

  if (node.pendingAdd) {
    return 'pending';
  }

  if (node.pendingEdit) {
    return 'warning';
  }

  if (!node.enabled || !node.enabledForSite) {
    return 'disabled';
  }

  return node.status === 'live' ? 'enabled' : node.status;
}

function formatNodeClasses(classes: string | null | undefined): string | null {
  if (!classes?.trim()) {
    return null;
  }

  return `.${classes.trim().split(/\s+/).join(' .')}`;
}

function getRowActionsCellClassName(
  isSelected: boolean,
  isDropNestTarget: boolean,
  pendingAdd: boolean,
): string {
  if (isDropNestTarget) {
    return 'bg-sky-50';
  }

  if (pendingAdd) {
    return 'bg-orange-50/40';
  }

  if (isSelected) {
    return 'bg-gray-100';
  }

  return 'bg-white group-hover:bg-gray-50';
}

export function NodeRow({
  node,
  item,
  itemProps,
  showTypeColumn = true,
  visibleSelectableNodeIds,
  isDragging = false,
  isDragSession = false,
  isDropNestTarget = false,
}: Props) {
  const selectedNodeIds = useBuilderStore((s) => s.selectedNodeIds);
  const selectNode = useBuilderStore((s) => s.selectNode);
  const collapsedNodeIds = useBuilderStore((s) => s.collapsedNodeIds);
  const toggleNodeCollapsed = useBuilderStore((s) => s.toggleNodeCollapsed);
  const restoreNode = useBuilderStore((s) => s.restoreNode);
  const elementType = useBuilderStore((s) => s.state?.elementType ?? '');
  const siteId = useBuilderStore((s) => s.siteId);
  const refresh = useBuilderStore((s) => s.refresh);
  const selectClickTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    return () => {
      if (selectClickTimerRef.current) {
        clearTimeout(selectClickTimerRef.current);
      }
    };
  }, []);

  const {
    ref: registerElement,
    onClick: _treeOnClick,
    role: _treeRole,
    onDragOver: treeOnDragOver,
    ...dragProps
  } = itemProps as {
    ref?: (element: HTMLElement | null) => void;
    onClick?: (event: MouseEvent) => void;
    onDragOver?: (event: DragEvent) => void;
    role?: string;
  } & Record<string, unknown>;

  const { onDragStart: treeOnDragStart, ...dragHandleProps } = item.getDragHandleProps() as {
    onDragStart?: (event: DragEvent) => void;
  } & Record<string, unknown>;
  const paddingLeft = getNodeRowPaddingLeft(node.level);
  const isSelected = selectedNodeIds.includes(node.id);
  const isCollapsed = Boolean(collapsedNodeIds[node.id]);
  const isSelectable = !node.pendingDelete;
  const classLabel = formatNodeClasses(node.classes);

  const handleSelect = (event: MouseEvent) => {
    if (!isSelectable) {
      return;
    }

    selectNode(
      node.id,
      {
        shiftKey: event.shiftKey,
        metaKey: event.metaKey,
        ctrlKey: event.ctrlKey,
      },
      visibleSelectableNodeIds,
    );
  };

  const handleEditNode = useCallback(() => {
    if (!isSelectable || !elementType) {
      return;
    }

    openNodeEditor(elementType, node.id, siteId, () => void refresh());
  }, [elementType, isSelectable, node.id, refresh, siteId]);

  return (
    <div
      ref={registerElement}
      role="row"
      {...dragProps}
      onDragOver={(event) => {
        treeOnDragOver?.(event);
        if (event.dataTransfer) {
          event.dataTransfer.dropEffect = 'move';
        }
      }}
      className={cn(
        nodeTreeGridClass(showTypeColumn, 'group min-h-9 outline-none focus:outline-none focus-visible:outline-none'),
        isSelectable && !isDragSession && 'cursor-pointer',
        !isSelected && !isDragSession && 'hover:bg-gray-50',
        isSelected && !isDragSession && 'bg-gray-100 hover:bg-gray-100',
        isSelected && isDragSession && 'bg-gray-100',
        isDropNestTarget && 'bg-sky-50',
        node.pendingAdd && !isDropNestTarget && 'bg-orange-50/40',
        isDragging && 'opacity-40',
      )}
      data-id={node.id}
      data-node-id={node.id}
      data-tree-row=""
      data-level={node.level}
      onClick={(event) => {
        if (!isSelectable || isRowInteractiveTarget(event.target)) {
          return;
        }

        if (selectClickTimerRef.current) {
          clearTimeout(selectClickTimerRef.current);
        }

        selectClickTimerRef.current = setTimeout(() => {
          selectClickTimerRef.current = null;
          handleSelect(event);
        }, 200);
      }}
      onDoubleClick={(event) => {
        if (!isSelectable || isRowInteractiveTarget(event.target)) {
          return;
        }

        if (selectClickTimerRef.current) {
          clearTimeout(selectClickTimerRef.current);
          selectClickTimerRef.current = null;
        }

        event.preventDefault();
        handleEditNode();
      }}
    >
      <div role="cell" className="flex items-center px-3 py-1.5">
        {isSelectable && (
          <Checkbox
            aria-label={`Select ${node.title}`}
            checked={isSelected}
            tabIndex={-1}
            onClick={(event) => event.stopPropagation()}
            onPkChange={() => {
              selectNode(
                node.id,
                { shiftKey: false, metaKey: false, ctrlKey: false, toggle: true },
                visibleSelectableNodeIds,
              );
            }}
          />
        )}
      </div>

      <div role="cell" className="flex min-h-9 items-center gap-1.5 py-1.5 pr-3" style={{ paddingLeft }}>
        <span className="relative inline-flex w-3 shrink-0 justify-center">
          {node.hasDescendants ? (
            <button
              type="button"
              data-no-row-select
              className="absolute top-1/2 left-1/2 flex size-6 -translate-x-1/2 -translate-y-1/2 cursor-pointer items-center justify-center rounded bg-transparent text-gray-400 hover:bg-transparent hover:text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-600/40"
              aria-expanded={!isCollapsed}
              onClick={(event) => {
                event.stopPropagation();
                toggleNodeCollapsed(node.id);
              }}
            >
              <Icon
                icon="chevron-right"
                className={cn('size-2.5', !isCollapsed && 'rotate-90')}
              />
            </button>
          ) : null}
        </span>

        {isSelectable && (
          <span className="relative inline-flex shrink-0">
            <Button
              type="button"
              variant="none"
              size="xs"
              data-no-row-select
              className="absolute top-1/2 left-1/2 flex size-6 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded bg-transparent p-0 text-gray-400 outline-none hover:bg-transparent hover:text-gray-600 focus:outline-none focus-visible:outline-none focus-visible:ring-0 [&::part(base)]:cursor-move"
              title="Drag to reorder"
              onClick={(event) => event.stopPropagation()}
              onDragStart={(event) => {
                if (event.dataTransfer) {
                  event.dataTransfer.effectAllowed = 'move';
                }
                treeOnDragStart?.(event);
              }}
              {...dragHandleProps}
            >
              <GripIcon slot="start" className="size-3.5" />
            </Button>
            <span className="invisible inline-flex size-3.5" aria-hidden>
              <GripIcon className="size-3.5" />
            </span>
          </span>
        )}

        <Status status={asPkStatusVariant(nodeStatusIndicator(node))} className="size-2.5 shrink-0" />

        <span
          className={cn(
            'min-w-0 truncate font-normal text-gray-900',
            node.pendingDelete && 'text-gray-400 line-through',
          )}
        >
          {node.title || '(Untitled)'}
        </span>

        {node.newWindow && (
          <Icon
            icon="arrow-up-right-from-square"
            className="size-2.5 shrink-0 text-gray-400/80"
            label="Opens in a new window"
          />
        )}

        {classLabel && (
          <span className="shrink-0 font-mono text-xs leading-snug text-[#8f98a3]">{classLabel}</span>
        )}

        {isSelectable && (
          <Button
            type="button"
            variant="none"
            size="xs"
            data-no-row-select
            className={cn(
              'node-edit-btn ml-[7px] shrink-0 rounded border border-[rgba(96,125,159,0.25)] bg-transparent px-1.5 text-[11px] font-normal leading-none text-gray-700 opacity-0 transition-opacity hover:bg-transparent focus-visible:opacity-100 group-hover:opacity-100',
              '[--pk-btn-height:1.25rem] [--pk-btn-padding-inline:0] [--pk-btn-font:11px] [--pk-btn-radius:0.25rem]',
              isSelected && 'opacity-100',
            )}
            onClick={(event) => {
              event.stopPropagation();
              handleEditNode();
            }}
          >
            {t('Edit')}
          </Button>
        )}

        {node.pendingDelete && (
          <Button type="button" size="sm" variant="secondary" onClick={() => void restoreNode(node.id)}>
            Restore
          </Button>
        )}
      </div>

      {showTypeColumn && (
        <div role="cell" className="flex items-center justify-end px-1 py-1.5">
          <NodeTypeBadge node={node} />
        </div>
      )}

      {isSelectable ? (
        <div
          role="cell"
          className={cn(
            'sticky right-0 z-10 flex items-center justify-center py-1.5 pr-1 pl-0.5',
            getRowActionsCellClassName(isSelected, isDropNestTarget, node.pendingAdd),
          )}
        >
          <NodeRowActionsMenu node={node} isDragSession={isDragSession} />
        </div>
      ) : (
        <div role="cell" aria-hidden />
      )}
    </div>
  );
}
