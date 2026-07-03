import { useState } from 'react';
import {
  Button,
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@verbb/plugin-kit-react/components';
import { cn } from '@verbb/plugin-kit-react/utils';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
  faArrowDown,
  faArrowLeft,
  faArrowRight,
  faArrowUp,
  faClone,
  faEllipsis,
  faPencil,
  faXmark,
} from '@fortawesome/pro-solid-svg-icons';
import type { BuilderNode } from '../types';
import { useBuilderStore } from '../store';
import { openNodeEditor } from '../utils/craft';
import { getNodeMoveCapabilities } from '../utils/nodeMoveActions';
import { CopyToSiteMenuItems } from './CopyToSiteMenuItems';
import { DescendantScopeMenuItems } from './DescendantScopeMenuItems';
import { t } from '../api';

type Props = {
  node: BuilderNode;
  isDragSession?: boolean;
  className?: string;
};

export function NodeRowActionsMenu({ node, isDragSession = false, className }: Props) {
  const [isOpen, setIsOpen] = useState(false);
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

  const capabilities = getNodeMoveCapabilities(nodes, node.id, maxLevels);
  const allowNestedActions = maxLevels === null || maxLevels > 1;
  const nodeTitle = node.title || t('Untitled');

  return (
    <div className={cn(isDragSession && 'opacity-0', className)}>
      <DropdownMenu modal={false} size="sm" open={isOpen} onOpenChange={setIsOpen}>
        <DropdownMenuTrigger
          render={
            <Button
              type="button"
              variant="transparent"
              size="sm"
              data-no-row-select
              className="h-7 w-7 rounded-lg p-0 text-gray-400 hover:text-gray-600"
              aria-label={t('Actions for {title}', { title: nodeTitle })}
              onClick={(event) => event.stopPropagation()}
            />
          }
        >
          <FontAwesomeIcon icon={faEllipsis} className="size-3.5" />
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" className="min-w-[140px]">
          <DropdownMenuItem onClick={() => openNodeEditor(elementType, node.id, siteId, () => void refresh())}>
            <FontAwesomeIcon icon={faPencil} />
            {t('Edit')}
          </DropdownMenuItem>
          <CopyToSiteMenuItems
            nodeIds={[node.id]}
            disabled={node.pendingDelete}
            includeDeepOption={allowNestedActions && node.hasDescendants}
            onCopied={() => setIsOpen(false)}
          />
          <DescendantScopeMenuItems
            label={t('Duplicate')}
            icon={faClone}
            includeDeepOption={allowNestedActions && node.hasDescendants}
            deepAsSubmenu={allowNestedActions && node.hasDescendants}
            shallowLabel={t('This node')}
            onAction={(deep) => void duplicateNode(node.id, deep)}
          />

          <DropdownMenuSeparator />

          <DropdownMenuItem
            disabled={!capabilities.canMoveUp}
            className={cn(!capabilities.canMoveUp && 'pointer-events-none opacity-50')}
            onClick={() => moveNodeUp(node.id)}
          >
            <FontAwesomeIcon icon={faArrowUp} />
            {t('Move up')}
          </DropdownMenuItem>
          <DropdownMenuItem
            disabled={!capabilities.canMoveDown}
            className={cn(!capabilities.canMoveDown && 'pointer-events-none opacity-50')}
            onClick={() => moveNodeDown(node.id)}
          >
            <FontAwesomeIcon icon={faArrowDown} />
            {t('Move down')}
          </DropdownMenuItem>
          <DropdownMenuItem
            disabled={!capabilities.canMoveLeft}
            className={cn(!capabilities.canMoveLeft && 'pointer-events-none opacity-50')}
            onClick={() => outdent(node.id)}
          >
            <FontAwesomeIcon icon={faArrowLeft} />
            {t('Move left')}
          </DropdownMenuItem>
          <DropdownMenuItem
            disabled={!capabilities.canMoveRight}
            className={cn(!capabilities.canMoveRight && 'pointer-events-none opacity-50')}
            onClick={() => indent(node.id)}
          >
            <FontAwesomeIcon icon={faArrowRight} />
            {t('Move right')}
          </DropdownMenuItem>

          <DropdownMenuSeparator />

          <DescendantScopeMenuItems
            label={t('Delete')}
            icon={faXmark}
            includeDeepOption={allowNestedActions && node.hasDescendants}
            deepAsSubmenu={allowNestedActions && node.hasDescendants}
            shallowLabel={t('This node')}
            variant="destructive"
            onAction={(deep) => void deleteNode(node.id, deep)}
          />

        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  );
}
