import type { DropdownMenuHost } from './pluginKitEvents';

/**
 * Run an action after a dropdown menu has fully closed.
 *
 * Matches plugin-kit overlay-isolation Menu → dialog glue:
 * `pk-after-hide` → `setTimeout(0)` → action.
 *
 * Also falls back if hide animation never completes (missing after-hide),
 * which previously left row actions dead after Move.
 */
export function runAfterMenuClose(
  menu: DropdownMenuHost | null | undefined,
  action: () => void,
): void {
  let settled = false;

  const run = (): void => {
    if (settled) {
      return;
    }

    settled = true;
    window.clearTimeout(fallbackId);
    menu?.removeEventListener?.('pk-after-hide', onAfterHide);

    window.setTimeout(() => {
      // Ensure popup/dismiss listeners are fully cleared before tree remounts.
      menu?.forceDismissCleanup?.();
      action();
    }, 0);
  };

  const onAfterHide = (): void => {
    run();
  };

  // If hide never finishes (animation hang), still run so Move/Edit aren't lost
  // and the menu can remount cleanly.
  const fallbackId = window.setTimeout(run, 450);

  if (!menu) {
    run();
    return;
  }

  // Select handler runs before the menu sets `open = false`, so we can still
  // subscribe to the coming after-hide. If already closed, run on the next task.
  if (!menu.open) {
    run();
    return;
  }

  menu.addEventListener('pk-after-hide', onAfterHide, { once: true });
}

/** @deprecated Prefer `runAfterMenuClose` — same implementation. */
export const openDialogAfterMenuClose = runAfterMenuClose;
