import { DropdownItem } from '@verbb/plugin-kit-react/components/DropdownMenu';
import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { useBuilderStore } from '../store';
import { t } from '../api';

/** Menu item value listened for on the parent `DropdownMenu` `pk-select`. */
export const COPY_TO_SITE_MENU_VALUE = 'copy-to-site';

type Props = {
  nodeIds: number[];
  disabled?: boolean;
  showIcon?: boolean;
};

/**
 * Presentational copy-to-site item. Open the dialog from the parent menu via
 * `pk-select` + `runAfterMenuClose` — item-level `onPkSelect` never runs because
 * `pk-select` fires on the menu host.
 */
export function CopyToSiteMenuItems({
  nodeIds,
  disabled = false,
  showIcon = true,
}: Props) {
  const canCopyToSite = useBuilderStore((s) => s.state?.canCopyToSite ?? false);
  const copyToSiteTargets = useBuilderStore((s) => s.state?.copyToSiteTargets ?? []);

  if (!canCopyToSite || copyToSiteTargets.length === 0) {
    return null;
  }

  return (
    <DropdownItem value={COPY_TO_SITE_MENU_VALUE} disabled={disabled || nodeIds.length === 0}>
      {showIcon && <Icon slot="prefix" icon="copy" />}
      {t('Copy to site…')}
    </DropdownItem>
  );
}
