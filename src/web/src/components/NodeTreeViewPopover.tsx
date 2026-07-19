import { Button } from '@verbb/plugin-kit-react/components/Button';
import { Checkbox } from '@verbb/plugin-kit-react/components/Checkbox';
import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { Popover } from '@verbb/plugin-kit-react/components/Popover';
import { useBuilderStore } from '../store';
import type { TreeColumnId } from '../types';
import { type PkCheckedChangeEvent } from '../utils/pluginKitEvents';
import { t } from '../api';

const COLUMN_OPTIONS: Array<{ id: TreeColumnId; label: string }> = [
  { id: 'type', label: 'Type' },
];

export function NodeTreeViewPopover() {
  const visibleColumns = useBuilderStore((s) => s.visibleColumns);
  const setColumnVisible = useBuilderStore((s) => s.setColumnVisible);

  return (
    <Popover placement="bottom-end" className="navigation-node-tree-view-popover">
      <Button slot="trigger" type="button" variant="default" withCaret>
        <Icon slot="start" icon="sliders" className="size-3.5 text-gray-500" />
        <span>{t('View')}</span>
      </Button>

      <div className="grid w-full grid-cols-[120px_minmax(0,1fr)]">
        <div className="flex items-center self-stretch border-r border-gray-200 bg-gray-50 px-4 py-3 text-sm leading-snug text-gray-600">
          {t('Table Columns')}
        </div>
        <div className="flex flex-col justify-center px-4 py-2">
          {COLUMN_OPTIONS.map((column) => (
            <label
              key={column.id}
              className="flex min-h-9 cursor-pointer items-center gap-2 text-[13px] leading-tight text-gray-900"
            >
              <Checkbox
                checked={visibleColumns[column.id]}
                onPkChange={(event) => setColumnVisible(column.id, (event as PkCheckedChangeEvent).detail.checked)}
              />
              <span className="min-w-0 flex-1 truncate">{t(column.label)}</span>
            </label>
          ))}
        </div>
      </div>
    </Popover>
  );
}
