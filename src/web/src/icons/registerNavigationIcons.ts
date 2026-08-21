import {
  registerIcons,
  arrowDown,
  arrowLeft,
  arrowRight,
  arrowUp,
  arrowUpRightFromSquare,
  check,
  chevronDown,
  chevronRight,
  clone,
  copy,
  ellipsis,
  gear,
  gripMove,
  pen,
  plus,
  sliders,
  trash,
  triangleExclamation,
  xmark,
} from '@verbb/plugin-kit-icons';

let registered = false;

/**
 * Opt-in kit icons used by the builder.
 * Kit no longer auto-registers the curated set — string `icon="…"` lookup needs these.
 */
export function registerNavigationIcons(): void {
  if (registered) {
    return;
  }

  registerIcons({
    arrowDown,
    arrowLeft,
    arrowRight,
    arrowUp,
    arrowUpRightFromSquare,
    check,
    chevronDown,
    chevronRight,
    clone,
    copy,
    ellipsis,
    gear,
    // Craft CP reorder handle (diamond dots)
    gripMove,
    pen,
    plus,
    // View columns control
    sliders,
    trash,
    // LargeErrorState / StatePanel error variant
    triangleExclamation,
    xmark,
  });
  registered = true;
}
