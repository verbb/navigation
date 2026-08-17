import {
  registerIcon,
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
  pen,
  plus,
  trash,
  triangleExclamation,
  xmark,
  type PkIcon,
} from '@verbb/plugin-kit-icons';

/**
 * Navigation-only icon: column / view settings control.
 * Kept out of the shared kit set; registered so `<Icon icon="sliders" />`
 * resolves through the same pk-icon lookup as built-ins.
 */
export const sliders: PkIcon = {
  width: 512,
  height: 512,
  path: 'M32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 224zm0 160c-17.7 0-32 14.3-32 32s14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384z',
};

let registered = false;

/**
 * Opt-in kit icons used by the builder, plus Navigation’s custom `sliders`.
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
    pen,
    plus,
    trash,
    // LargeErrorState / StatePanel error variant
    triangleExclamation,
    xmark,
  });
  registerIcon('sliders', sliders);
  registered = true;
}
