import { useEffect } from 'react';
import { useBuilderStore } from '../store';
import { useBuilderSaveShortcut } from '../hooks/useBuilderSaveShortcut';
import { NodeTree } from './NodeTree';
import { MenuContentDialog } from './MenuContentDialog';
import { CopyToSiteDialog } from './CopyToSiteDialog';

type Props = {
  menuId: number;
  siteId: number;
};

export function BuilderApp({ menuId, siteId }: Props) {
  const init = useBuilderStore((s) => s.init);
  const loading = useBuilderStore((s) => s.loading);
  const error = useBuilderStore((s) => s.error);
  const structureDirty = useBuilderStore((s) => s.structureDirty);
  const sessionChangeCount = useBuilderStore((s) => s.state?.session?.changeCount ?? 0);
  const stagingEnabled = useBuilderStore((s) => s.state?.stagingEnabled ?? true);
  const isDirty = structureDirty || sessionChangeCount > 0;
  const menuContentOpen = useBuilderStore((s) => s.menuContentOpen);
  const setMenuContentOpen = useBuilderStore((s) => s.setMenuContentOpen);
  const copyToSiteDialog = useBuilderStore((s) => s.copyToSiteDialog);
  const setCopyToSiteDialogOpen = useBuilderStore((s) => s.setCopyToSiteDialogOpen);
  const refresh = useBuilderStore((s) => s.refresh);

  useBuilderSaveShortcut(!loading && !error && stagingEnabled);

  useEffect(() => {
    void init(menuId, siteId);
  }, [init, menuId, siteId]);

  useEffect(() => {
    const handler = (e: BeforeUnloadEvent) => {
      if (isDirty) {
        e.preventDefault();
        e.returnValue = '';
      }
    };

    window.addEventListener('beforeunload', handler);
    return () => window.removeEventListener('beforeunload', handler);
  }, [isDirty]);

  if (loading) {
    return null;
  }

  if (error) {
    return <div className="rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700">{error}</div>;
  }

  return (
    <>
      <div className="-mx-6 -mb-2.5 overflow-hidden rounded-md border border-black/10 bg-white shadow-[0_0_0_1px_rgba(205,216,228,0.25),0_2px_12px_rgba(205,216,228,0.5)]">
        <NodeTree />
      </div>

      <MenuContentDialog
        open={menuContentOpen}
        menuId={menuId}
        siteId={siteId}
        onOpenChange={setMenuContentOpen}
        onSaved={() => void refresh()}
      />

      <CopyToSiteDialog
        open={copyToSiteDialog.open}
        nodeIds={copyToSiteDialog.nodeIds}
        includeDeepOption={copyToSiteDialog.includeDeepOption}
        onOpenChange={setCopyToSiteDialogOpen}
      />
    </>
  );
}
