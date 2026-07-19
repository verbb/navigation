import { useRef, useState } from 'react';
import { Button } from '@verbb/plugin-kit-react/components/Button';
import {
  DropdownItem,
  DropdownMenu,
  DropdownSeparator,
} from '@verbb/plugin-kit-react/components/DropdownMenu';
import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { Status } from '@verbb/plugin-kit-react/components/Status';
import { useBuilderStore } from '../store';
import type { NodeStatusFilter } from '../types';
import { NodeTreeViewPopover } from './NodeTreeViewPopover';
import { COPY_TO_SITE_MENU_VALUE, CopyToSiteMenuItems } from './CopyToSiteMenuItems';
import { DescendantScopeMenuItems } from './DescendantScopeMenuItems';
import { openNodeEditor } from '../utils/craft';
import { runAfterMenuClose } from '../utils/openDialogAfterMenuClose';
import { t } from '../api';
import {
  asPkStatusVariant,
  type DropdownMenuHost,
  type PkOpenChangeEvent,
} from '../utils/pluginKitEvents';

const STATUS_RADIO_GROUP = 'navigation-status-filter';

const STATUS_OPTIONS: Array<{
  value: NodeStatusFilter;
  label: string;
  status?: string;
}> = [
  { value: 'all', label: 'All', status: 'all' },
  { value: 'enabled', label: 'Enabled', status: 'enabled' },
  { value: 'disabled', label: 'Disabled', status: 'disabled' },
  { value: 'trashed', label: 'Trashed' },
];

type PkSelectDetail = {
  value?: string;
};

export function NodeTreeToolbar() {
  const actionsMenuRef = useRef<DropdownMenuHost | null>(null);
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
  const openCopyToSiteDialog = useBuilderStore((s) => s.openCopyToSiteDialog);

  const elementType = state?.elementType ?? '';
  const maxLevels = state?.menu.maxLevels ?? null;
  const allowNestedActions = !maxLevels || maxLevels > 1;

  const hasSelection = selectedNodeIds.length > 0;
  const selectedNodes = nodes.filter((node) => selectedNodeIds.includes(node.id));
  const actionableNodeIds = selectedNodes.filter((node) => !node.pendingDelete).map((node) => node.id);
  const hasActionableSelection = actionableNodeIds.length > 0;
  const canEditSelection = selectedNodeIds.length === 1 && !selectedNodes[0]?.pendingDelete;

  const activeStatus = STATUS_OPTIONS.find((option) => option.value === statusFilter) ?? STATUS_OPTIONS[0];
  const [statusMenuOpen, setStatusMenuOpen] = useState(false);
  const duplicateLabel = t('Duplicate');
  const deleteLabel = t('Delete');

  const handleStatusMenuSelect = (event: Event) => {
    const value = (event as CustomEvent<PkSelectDetail>).detail?.value as NodeStatusFilter | undefined;

    if (!value || !STATUS_OPTIONS.some((option) => option.value === value)) {
      return;
    }

    setStatusFilter(value);
    setStatusMenuOpen(false);
  };

  const handleStatusActionSelect = (event: Event) => {
    const value = (event as CustomEvent<PkSelectDetail>).detail?.value;

    if (value === 'enabled' || value === 'disabled') {
      void setSelectedNodesStatus(value);
    }
  };

  const handleActionsMenuSelect = (event: Event) => {
    const value = (event as CustomEvent<PkSelectDetail>).detail?.value;

    if (!value) {
      return;
    }

    const afterClose = (action: () => void) => {
      runAfterMenuClose(actionsMenuRef.current, action);
    };

    switch (value) {
      case 'edit':
        if (canEditSelection) {
          afterClose(() => {
            openNodeEditor(elementType, selectedNodeIds[0], siteId, () => void refresh());
          });
        }
        return;
      case COPY_TO_SITE_MENU_VALUE:
        afterClose(() => {
          openCopyToSiteDialog(actionableNodeIds, allowNestedActions);
        });
        return;
      case duplicateLabel:
      case `${duplicateLabel}-shallow`:
        afterClose(() => {
          void duplicateSelectedNodes(false);
        });
        return;
      case `${duplicateLabel}-deep`:
        afterClose(() => {
          void duplicateSelectedNodes(true);
        });
        return;
      case deleteLabel:
      case `${deleteLabel}-shallow`:
        afterClose(() => {
          void deleteSelectedNodes(false);
        });
        return;
      case `${deleteLabel}-deep`:
        afterClose(() => {
          void deleteSelectedNodes(true);
        });
        return;
      default:
        return;
    }
  };

  return (
    <div id="navigation-builder-toolbar" className="flex min-w-0 flex-1 flex-wrap items-center gap-1.5">
      <DropdownMenu
        open={statusMenuOpen}
        placement="bottom-start"
        onPkSelect={handleStatusMenuSelect}
        onPkOpenChange={(event) => setStatusMenuOpen((event as PkOpenChangeEvent).detail.open)}
      >
        <Button slot="trigger" type="button" variant="default" withCaret>
          {activeStatus.value === 'trashed' ? (
            <Icon slot="start" icon="trash" className="size-3 text-gray-500" />
          ) : activeStatus.status ? (
            <Status slot="start" status={asPkStatusVariant(activeStatus.status)} />
          ) : null}
          <span>{t(activeStatus.label)}</span>
        </Button>

        {STATUS_OPTIONS.map((option) => (
          <DropdownItem
            key={option.value}
            value={option.value}
            type="radio"
            radioGroup={STATUS_RADIO_GROUP}
            checked={statusFilter === option.value}
          >
            {option.value === 'trashed' ? (
              <Icon slot="prefix" icon="trash" className="size-3 text-gray-500" />
            ) : option.status ? (
              <Status slot="prefix" status={asPkStatusVariant(option.status)} />
            ) : null}
            {t(option.label)}
          </DropdownItem>
        ))}
      </DropdownMenu>

      <NodeTreeViewPopover />

      {hasSelection && (
        <>
          <DropdownMenu placement="bottom-end" onPkSelect={handleStatusActionSelect}>
            <Button slot="trigger" type="button" variant="default" withCaret>
              <span>{t('Set status')}</span>
            </Button>
            <DropdownItem value="enabled" disabled={!hasActionableSelection}>
              <Status slot="prefix" status="enabled" />
              {t('Enabled')}
            </DropdownItem>
            <DropdownItem value="disabled" disabled={!hasActionableSelection}>
              <Status slot="prefix" status="disabled" />
              {t('Disabled')}
            </DropdownItem>
          </DropdownMenu>

          <DropdownMenu
            ref={(el) => {
              actionsMenuRef.current = el as DropdownMenuHost | null;
            }}
            placement="bottom-end"
            onPkSelect={handleActionsMenuSelect}
          >
            <Button slot="trigger" type="button" variant="default" withCaret>
              <Icon slot="start" icon="gear" className="size-3.5 text-gray-500" />
              <span>{t('Actions')}</span>
            </Button>
            <DropdownItem value="edit" disabled={!canEditSelection}>
              {t('Edit node')}
            </DropdownItem>
            <CopyToSiteMenuItems
              nodeIds={actionableNodeIds}
              disabled={!hasActionableSelection}
              showIcon={false}
            />
            <DescendantScopeMenuItems
              label={duplicateLabel}
              disabled={!hasActionableSelection}
              includeDeepOption={allowNestedActions}
              deepAsSubmenu
            />
            <DropdownSeparator />
            <DescendantScopeMenuItems
              label={deleteLabel}
              disabled={!hasActionableSelection}
              includeDeepOption={allowNestedActions}
              deepAsSubmenu
              destructive
            />
          </DropdownMenu>
        </>
      )}
    </div>
  );
}
