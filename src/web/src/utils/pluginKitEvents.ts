import type { PkStatusVariant } from '@verbb/plugin-kit-react/components/Status';

export type PkOpenChangeEvent = CustomEvent<{ open: boolean }>;
export type PkCheckedChangeEvent = CustomEvent<{ checked: boolean }>;

/**
 * Imperative APIs we call on React `<Dialog>` / `<DropdownMenu>` refs.
 * Structural (not Lit class imports) so author code stays on the React package.
 */
export type DialogHost = {
  show?: () => void | Promise<void>;
  hide?: () => void | Promise<void>;
};

export type DropdownMenuHost = {
  open?: boolean;
  addEventListener: HTMLElement['addEventListener'];
  removeEventListener: HTMLElement['removeEventListener'];
  forceDismissCleanup?: () => void;
};

export const asPkStatusVariant = (status: string): PkStatusVariant => status as PkStatusVariant;
