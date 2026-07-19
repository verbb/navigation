import { useRef } from 'react';
import { Button } from '@verbb/plugin-kit-react/components/Button';
import {
  DropdownItem,
  DropdownMenu,
  DropdownSeparator,
} from '@verbb/plugin-kit-react/components/DropdownMenu';
import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { cn } from '../utils/cn';
import type { BuilderNode } from '../types';
import { useBuilderStore } from '../store';
import { openNodeEditor } from '../utils/craft';
import { getNodeMoveCapabilities } from '../utils/nodeMoveActions';
import { COPY_TO_SITE_MENU_VALUE, CopyToSiteMenuItems } from './CopyToSiteMenuItems';
import { DescendantScopeMenuItems } from './DescendantScopeMenuItems';
import { runAfterMenuClose } from '../utils/openDialogAfterMenuClose';
import { t } from '../api';
import type { DropdownMenuHost } from '../utils/pluginKitEvents';

type Props = {
  node: BuilderNode;
  isDragSession?: boolean;
  className?: string;
};

type PkSelectDetail = {
  value?: string;
};

export function NodeRowActionsMenu({ node, isDragSession = false, className }: Props) {
  const menuRef = useRef<DropdownMenuHost | null>(null);
  const elementType = useBuilderStore((s) => s.state?.elementType ?? '');
  const siteId = useBuilderStore((s) => s.siteId);
  const nodes = useBuilderStore((s) => s.nodes);
  const maxLevels = useBuilderStore((s) => s.state?.menu.maxLevels ?? null);
  const moveNodeUp = useBuilderStore((s) => s.moveNodeUp);
  const moveNodeDown = useBuilderStore((s) => s.moveNodeDown);
  const indent = useBuilderStore((s) => s.indent);
  const outdent = useBuilderStore((s) => s.outdent);
  const duplicateNode = useBuilderStore((s) => s.duplicateNode);
  const deleteNode = useBuilderStore((s) => s.deleteNode);
  const refresh = useBuilderStore((s) => s.refresh);
  const openCopyToSiteDialog = useBuilderStore((s) => s.openCopyToSiteDialog);

  const capabilities = getNodeMoveCapabilities(nodes, node.id, maxLevels);
  const allowNestedActions = maxLevels === null || maxLevels > 1;
  const includeDeepOption = allowNestedActions && node.hasDescendants;
  const nodeTitle = node.title || t('Untitled');
  const duplicateLabel = t('Duplicate');
  const deleteLabel = t('Delete');

  /** Queue work until the menu finishes hiding — avoids remount/teardown races. */
  const afterClose = (action: () => void) => {
    runAfterMenuClose(menuRef.current, action);
  };

  // `pk-select` fires on the menu host (overlay-isolation / WA pattern) — not on items.
  const handleMenuSelect = (event: Event) => {
    const value = (event as CustomEvent<PkSelectDetail>).detail?.value;

    if (!value) {
      return;
    }

    switch (value) {
      case 'edit':
        afterClose(() => {
          openNodeEditor(elementType, node.id, siteId, () => void refresh());
        });
        return;
      case COPY_TO_SITE_MENU_VALUE:
        afterClose(() => {
          openCopyToSiteDialog([node.id], includeDeepOption);
        });
        return;
      case 'move-up':
        afterClose(() => {
          moveNodeUp(node.id);
        });
        return;
      case 'move-down':
        afterClose(() => {
          moveNodeDown(node.id);
        });
        return;
      case 'move-left':
        afterClose(() => {
          outdent(node.id);
        });
        return;
      case 'move-right':
        afterClose(() => {
          indent(node.id);
        });
        return;
      case duplicateLabel:
      case `${duplicateLabel}-shallow`:
        afterClose(() => {
          void duplicateNode(node.id, false);
        });
        return;
      case `${duplicateLabel}-deep`:
        afterClose(() => {
          void duplicateNode(node.id, true);
        });
        return;
      case deleteLabel:
      case `${deleteLabel}-shallow`:
        afterClose(() => {
          void deleteNode(node.id, false);
        });
        return;
      case `${deleteLabel}-deep`:
        afterClose(() => {
          void deleteNode(node.id, true);
        });
        return;
      default:
        return;
    }
  };

  return (
    <div className={cn(isDragSession && 'opacity-0', className)}>
      <DropdownMenu
        ref={(el) => {
          menuRef.current = el as DropdownMenuHost | null;
        }}
        size="sm"
        placement="bottom-end"
        onPkSelect={handleMenuSelect}
      >
        <Button
          slot="trigger"
          type="button"
          variant="transparent"
          size="sm"
          data-no-row-select
          className="[--pk-btn-height:28px] [--pk-btn-padding-inline:7px]"
          aria-label={t('Actions for {title}', { title: nodeTitle })}
          onClick={(event) => event.stopPropagation()}
        >
          <Icon slot="start" icon="ellipsis" />
        </Button>

        <DropdownItem value="edit">
          <Icon slot="prefix" icon="pen" />
          {t('Edit')}
        </DropdownItem>
        <CopyToSiteMenuItems nodeIds={[node.id]} disabled={node.pendingDelete} />
        <DescendantScopeMenuItems
          label={duplicateLabel}
          icon="clone"
          includeDeepOption={includeDeepOption}
          deepAsSubmenu={includeDeepOption}
          shallowLabel={t('This node')}
        />

        <DropdownSeparator />

        <DropdownItem value="move-up" disabled={!capabilities.canMoveUp}>
          <Icon slot="prefix" icon="arrow-up" />
          {t('Move up')}
        </DropdownItem>
        <DropdownItem value="move-down" disabled={!capabilities.canMoveDown}>
          <Icon slot="prefix" icon="arrow-down" />
          {t('Move down')}
        </DropdownItem>
        <DropdownItem value="move-left" disabled={!capabilities.canMoveLeft}>
          <Icon slot="prefix" icon="arrow-left" />
          {t('Move left')}
        </DropdownItem>
        <DropdownItem value="move-right" disabled={!capabilities.canMoveRight}>
          <Icon slot="prefix" icon="arrow-right" />
          {t('Move right')}
        </DropdownItem>

        <DropdownSeparator />

        <DescendantScopeMenuItems
          label={deleteLabel}
          icon="xmark"
          includeDeepOption={includeDeepOption}
          deepAsSubmenu={includeDeepOption}
          shallowLabel={t('This node')}
          destructive
        />
      </DropdownMenu>
    </div>
  );
}
