import {
  Button,
  Checkbox,
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@verbb/plugin-kit-react/components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faChevronDown, faSliders } from '@fortawesome/pro-solid-svg-icons';
import { useBuilderStore } from '../store';
import type { TreeColumnId } from '../types';
import { t } from '../api';

const COLUMN_OPTIONS: Array<{ id: TreeColumnId; label: string }> = [
  { id: 'type', label: 'Type' },
];

export function NodeTreeViewPopover() {
  const visibleColumns = useBuilderStore((s) => s.visibleColumns);
  const setColumnVisible = useBuilderStore((s) => s.setColumnVisible);

  return (
    <Popover modal={false}>
      <PopoverTrigger
        render={
          <Button type="button" variant="default" className="gap-2">
            <FontAwesomeIcon icon={faSliders} className="size-3.5 text-gray-500" />
            <span>{t('View')}</span>
            <FontAwesomeIcon icon={faChevronDown} className="size-2.5 opacity-70" />
          </Button>
        }
      />

      <PopoverContent align="end" className="w-[min(92vw,320px)] p-0">
        <div className="grid grid-cols-[120px_minmax(0,1fr)]">
          <div className="border-r border-gray-200 bg-gray-50 px-4 py-4 text-sm text-gray-600">
            {t('Table Columns')}
          </div>
          <div className="space-y-1 px-4 py-3">
            {COLUMN_OPTIONS.map((column) => (
              <label
                key={column.id}
                className="flex min-h-8 cursor-pointer items-center gap-2 text-[13px] leading-tight text-gray-900"
              >
                <Checkbox
                  checked={visibleColumns[column.id]}
                  onCheckedChange={(checked) => setColumnVisible(column.id, Boolean(checked))}
                />
                <span className="min-w-0 flex-1 truncate">{t(column.label)}</span>
              </label>
            ))}
          </div>
        </div>
      </PopoverContent>
    </Popover>
  );
}
