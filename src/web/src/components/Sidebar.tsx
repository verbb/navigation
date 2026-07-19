import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { cn } from '../utils/cn';
import { useBuilderStore, useBuilderTabs } from '../store';
import { AddNodePanel } from './AddNodePanel';
import type { BuilderTab } from '../types';

export function Sidebar() {
  const tabs = useBuilderTabs();
  const activeTabId = useBuilderStore((s) => s.activeTabId);
  const setActiveTab = useBuilderStore((s) => s.setActiveTab);
  const loading = useBuilderStore((s) => s.loading);

  if (loading) {
    return null;
  }

  return (
    <aside id="navigation-builder-sidebar" className="w-full">
      <nav
        id="accordion"
        className="relative z-[1] min-h-10 overflow-hidden rounded-[5px] shadow-[inset_0_-1px_0_#e3e5e8,0_0_0_1px_rgba(205,216,228,0.25),0_2px_12px_rgba(205,216,228,0.5)]"
      >
        <ul className="m-0 block max-w-full list-none p-0">
          {tabs.map((tab) => (
            <SidebarAccordionItem
              key={tab.id}
              tab={tab}
              expanded={activeTabId === tab.id}
              onToggle={() => setActiveTab(activeTabId === tab.id ? null : tab.id)}
            />
          ))}
        </ul>
      </nav>
    </aside>
  );
}

function SidebarAccordionItem({
  tab,
  expanded,
  onToggle,
}: {
  tab: BuilderTab;
  expanded: boolean;
  onToggle: () => void;
}) {
  return (
    <li className="block">
      <button
        type="button"
        className={cn(
          'relative flex w-full max-w-full items-center justify-between gap-2 overflow-hidden border-b border-black/10 bg-[#f3f7fc] px-5 py-2.5 text-left text-[#576575] no-underline hover:bg-[#edf2f8] hover:no-underline',
          expanded && 'pb-2.5 text-[#29323d]',
        )}
        data-active={expanded ? 'true' : 'false'}
        onClick={onToggle}
      >
        <span className="min-w-0 truncate">{tab.label}</span>
        <Icon
          icon="chevron-down"
          className={cn('size-3.5 shrink-0', expanded && 'rotate-180')}
        />
      </button>

      {expanded && (
        <div className="border-b border-black/10 bg-white p-5">
          <AddNodePanel tab={tab} />
        </div>
      )}
    </li>
  );
}
