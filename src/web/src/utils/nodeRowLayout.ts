import { cn } from '@verbb/plugin-kit-react/utils';

export const NODE_LEVEL_INDENT_PX = 20;

/** Matches `grid-cols-[2.5rem_...]` checkbox column width. */
export const NODE_TREE_CHECKBOX_COLUMN_PX = 40;

/** Matches `8rem` type column width. */
export const NODE_TREE_TYPE_COLUMN_PX = 128;

/** Top-level (level 1) is 0; each level deeper adds 20px (level 2 → 20px, level 3 → 40px, …). */
export function getNodeRowPaddingLeft(level: number): number {
  return Math.max(0, level - 1) * NODE_LEVEL_INDENT_PX;
}

export function getNodeTreeGridClass(showTypeColumn: boolean): string {
  return showTypeColumn
    ? 'grid grid-cols-[2.5rem_minmax(0,1fr)_8rem_2.25rem]'
    : 'grid grid-cols-[2.5rem_minmax(0,1fr)_2.25rem]';
}

export function nodeTreeGridClass(showTypeColumn: boolean, className?: string): string {
  return cn(getNodeTreeGridClass(showTypeColumn), className);
}
