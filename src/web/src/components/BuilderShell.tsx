import { useEffect } from 'react';
import { createPortal } from 'react-dom';
import { BuilderApp } from './BuilderApp';
import { BuilderHeader } from './BuilderHeader';
import { BuilderInstructions } from './BuilderInstructions';
import { BuilderActions } from './BuilderActions';
import { Sidebar } from './Sidebar';
import { useBuilderStore } from '../store';

const BUILDER_READY_CLASS = 'navigation-builder-ready';

function BuilderReadyMarker() {
  const loading = useBuilderStore((s) => s.loading);

  useEffect(() => {
    if (loading) {
      document.body.classList.remove(BUILDER_READY_CLASS);
      return;
    }

    document.body.classList.add(BUILDER_READY_CLASS);

    return () => {
      document.body.classList.remove(BUILDER_READY_CLASS);
    };
  }, [loading]);

  return null;
}

type Props = {
  menuId: number;
  siteId: number;
  initialSettingsUrl: string;
  initialCanEditSettings: boolean;
  headerMountNode: HTMLElement | null;
  instructionsMountNode: HTMLElement | null;
  actionsMountNode: HTMLElement | null;
  sidebarMountNode: HTMLElement | null;
};

function HeaderMount() {
  const loading = useBuilderStore((s) => s.loading);
  const error = useBuilderStore((s) => s.error);

  return <BuilderHeader showToolbar={!loading && !error} />;
}

export function BuilderShell({
  menuId,
  siteId,
  initialSettingsUrl,
  initialCanEditSettings,
  headerMountNode,
  instructionsMountNode,
  actionsMountNode,
  sidebarMountNode,
}: Props) {
  return (
    <>
      <BuilderReadyMarker />
      {headerMountNode && createPortal(<HeaderMount />, headerMountNode)}
      {instructionsMountNode && createPortal(<BuilderInstructions />, instructionsMountNode)}
      {actionsMountNode &&
        createPortal(
          <BuilderActions
            initialSettingsUrl={initialSettingsUrl}
            initialCanEditSettings={initialCanEditSettings}
          />,
          actionsMountNode,
        )}
      {sidebarMountNode && createPortal(<Sidebar />, sidebarMountNode)}
      <BuilderApp menuId={menuId} siteId={siteId} />
    </>
  );
}
