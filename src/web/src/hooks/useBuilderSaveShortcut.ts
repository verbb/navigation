import { useEffect } from 'react';
import { useBuilderStore } from '../store';

type GarnishShortcutEvent = KeyboardEvent & {
  bubbleShortcut?: () => void;
};

type GarnishUiLayerManager = {
  registerShortcut: (shortcut: { keyCode: number; ctrl: boolean }, handler: (event: GarnishShortcutEvent) => void) => void;
  unregisterShortcut: (shortcut: { keyCode: number; ctrl: boolean }, layer: number) => void;
};

type NavigationGarnish = {
  S_KEY: number;
  uiLayerManager: GarnishUiLayerManager;
};

function getGarnish(): NavigationGarnish | null {
  const garnish = (window as unknown as { Garnish?: NavigationGarnish }).Garnish;

  if (!garnish?.uiLayerManager?.registerShortcut) {
    return null;
  }

  return garnish;
}

/** Cmd/Ctrl+S publishes staged builder changes, matching the legacy element index. */
export function useBuilderSaveShortcut(enabled: boolean): void {
  useEffect(() => {
    if (!enabled) {
      return;
    }

    const garnish = getGarnish();

    if (!garnish) {
      return;
    }

    const shortcut = { keyCode: garnish.S_KEY, ctrl: true };

    const handler = (event: GarnishShortcutEvent) => {
      const { publishing, discarding, structureDirty, state } = useBuilderStore.getState();
      const isDirty = structureDirty || (state?.session?.changeCount ?? 0) > 0;

      if (!isDirty || publishing || discarding) {
        event.bubbleShortcut?.();
        return;
      }

      event.preventDefault();
      void useBuilderStore.getState().publish();
    };

    garnish.uiLayerManager.registerShortcut(shortcut, handler);

    return () => {
      garnish.uiLayerManager.unregisterShortcut(shortcut, 0);
    };
  }, [enabled]);
}
