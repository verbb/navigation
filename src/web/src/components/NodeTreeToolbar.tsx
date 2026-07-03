import { useState, type ReactNode } from 'react';
import {
  Button,
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuRadioGroup,
  DropdownMenuRadioItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  Status,
} from '@verbb/plugin-kit-react/components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faChevronDown, faCog, faTrash } from '@fortawesome/pro-solid-svg-icons';
import { useBuilderStore } from '../store';
import type { NodeStatusFilter } from '../types';
import { NodeTreeViewPopover } from './NodeTreeViewPopover';
import { CopyToSiteMenuItems } from './CopyToSiteMenuItems';
import { openNodeEditor } from '../utils/craft';
import { t } from '../api';

const STATUS_OPTIONS: Array<{
  value: NodeStatusFilter;
  label: string;
  status?: string;
  icon?: ReactNode;
}> = [
  { value: 'all', label: 'All', status: 'all' },
  { value: 'enabled', label: 'Enabled', status: 'enabled' },
  { value: 'disabled', label: 'Disabled', status: 'disabled' },
  {
    value: 'trashed',
    label: 'Trashed',
    icon: <FontAwesomeIcon icon={faTrash} className="size-3 text-gray-500" />,
  },
];

export function NodeTreeToolbar() {
  const state = useBuilderStore((s) => s.state);
  const siteId = useBuilderStore((s) => s.siteId);
  const nodes = useBuilderStore((s) => s.nodes);
  const selectedNodeIds = useBuilderStore((s) => s.selectedNodeIds);
  const statusFilter = useBuilderStore((s) => s.statusFilter);
  const setStatusFilter = useBuilderStore((s) => s.setStatusFilter);
  const setSelectedNodesStatus = useBuilderStore((s) => s.setSelectedNodesStatus);
  const duplicateSelectedNodes = useBuilderStore((s) => s.duplicateSelectedNodes);
  const deleteSelectedNodes = useBuilderStore((s) => s.deleteSelectedNodes);
  const refresh = useBuilderStore((s) => s.refresh);

  const elementType = state?.elementType ?? '';
  const maxLevels = state?.menu.maxLevels ?? null;
  const allowNestedActions = !maxLevels || maxLevels > 1;

  const hasSelection = selectedNodeIds.length > 0;
  const selectedNodes = nodes.filter((node) => selectedNodeIds.includes(node.id));
  const hasActionableSelection = selectedNodes.some((node) => !node.pendingDelete);
  const canEditSelection = selectedNodeIds.length === 1 && !selectedNodes[0]?.pendingDelete;

  const activeStatus = STATUS_OPTIONS.find((option) => option.value === statusFilter) ?? STATUS_OPTIONS[0];
  const [statusMenuOpen, setStatusMenuOpen] = useState(false);

  const handleEditNode = () => {
    if (!canEditSelection) {
      return;
    }

    openNodeEditor(elementType, selectedNodeIds[0], siteId, () => void refresh());
  };

  return (
    <div id="navigation-builder-toolbar" className="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
      <DropdownMenu modal={false} open={statusMenuOpen} onOpenChange={setStatusMenuOpen}>
        <DropdownMenuTrigger
          render={
            <Button type="button" variant="default" className="gap-2">
              {activeStatus.icon ?? (activeStatus.status ? <Status status={activeStatus.status} /> : null)}
              <span>{t(activeStatus.label)}</span>
              <FontAwesomeIcon icon={faChevronDown} className="size-2.5 opacity-70" />
            </Button>
          }
        />
        <DropdownMenuContent align="start" className="min-w-40">
          <DropdownMenuRadioGroup
            value={statusFilter}
            onValueChange={(value) => {
              setStatusFilter(value as NodeStatusFilter);
              setStatusMenuOpen(false);
            }}
          >
            {STATUS_OPTIONS.map((option) => (
              <DropdownMenuRadioItem key={option.value} value={option.value} className="gap-2.5">
                {option.icon ?? (option.status ? <Status status={option.status} /> : null)}
                {t(option.label)}
              </DropdownMenuRadioItem>
            ))}
          </DropdownMenuRadioGroup>
        </DropdownMenuContent>
      </DropdownMenu>

      <NodeTreeViewPopover />

      {hasSelection && (
        <>
          <DropdownMenu modal={false}>
            <DropdownMenuTrigger
              render={
                <Button type="button" variant="default" className="gap-2">
                  <span>{t('Set status')}</span>
                  <FontAwesomeIcon icon={faChevronDown} className="size-2.5 opacity-70" />
                </Button>
              }
            />
            <DropdownMenuContent align="end" className="min-w-40">
              <DropdownMenuItem
                disabled={!hasActionableSelection}
                onClick={() => void setSelectedNodesStatus('enabled')}
              >
                <Status status="enabled" className="mr-2" />
                {t('Enabled')}
              </DropdownMenuItem>
              <DropdownMenuItem
                disabled={!hasActionableSelection}
                onClick={() => void setSelectedNodesStatus('disabled')}
              >
                <Status status="disabled" className="mr-2" />
                {t('Disabled')}
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <DropdownMenu modal={false}>
            <DropdownMenuTrigger
              render={
                <Button type="button" variant="default" className="gap-2">
                  <FontAwesomeIcon icon={faCog} className="size-3.5 text-gray-500" />
                  <span>{t('Actions')}</span>
                  <FontAwesomeIcon icon={faChevronDown} className="size-2.5 opacity-70" />
                </Button>
              }
            />
            <DropdownMenuContent align="end" className="min-w-52">
              <DropdownMenuItem disabled={!canEditSelection} onClick={handleEditNode}>
                {t('Edit node')}
              </DropdownMenuItem>
              <CopyToSiteMenuItems
                nodeIds={selectedNodes.filter((node) => !node.pendingDelete).map((node) => node.id)}
                disabled={!hasActionableSelection}
              />
              <DropdownMenuItem
                disabled={!hasActionableSelection}
                onClick={() => void duplicateSelectedNodes(false)}
              >
                {t('Duplicate')}
              </DropdownMenuItem>
              {allowNestedActions && (
                <DropdownMenuItem
                  disabled={!hasActionableSelection}
                  onClick={() => void duplicateSelectedNodes(true)}
                >
                  {t('Duplicate (with descendants)')}
                </DropdownMenuItem>
              )}
              <DropdownMenuSeparator />
              <DropdownMenuItem
                variant="destructive"
                disabled={!hasActionableSelection}
                onClick={() => void deleteSelectedNodes(false)}
              >
                {t('Delete')}
              </DropdownMenuItem>
              {allowNestedActions && (
                <DropdownMenuItem
                  variant="destructive"
                  disabled={!hasActionableSelection}
                  onClick={() => void deleteSelectedNodes(true)}
                >
                  {t('Delete (with descendants)')}
                </DropdownMenuItem>
              )}
            </DropdownMenuContent>
          </DropdownMenu>
        </>
      )}
    </div>
  );
}
