import { DropdownMenuItem } from '@verbb/plugin-kit-react/components';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faCopy } from '@fortawesome/pro-solid-svg-icons';
import { useBuilderStore } from '../store';
import { t } from '../api';

type Props = {
  nodeIds: number[];
  disabled?: boolean;
  includeDeepOption?: boolean;
  showIcon?: boolean;
  onCopied?: () => void;
};

export function CopyToSiteMenuItems({
  nodeIds,
  disabled = false,
  includeDeepOption = false,
  showIcon = true,
  onCopied,
}: Props) {
  const canCopyToSite = useBuilderStore((s) => s.state?.canCopyToSite ?? false);
  const copyToSiteTargets = useBuilderStore((s) => s.state?.copyToSiteTargets ?? []);
  const openCopyToSiteDialog = useBuilderStore((s) => s.openCopyToSiteDialog);

  if (!canCopyToSite || copyToSiteTargets.length === 0) {
    return null;
  }

  return (
    <DropdownMenuItem
      disabled={disabled || nodeIds.length === 0}
      onClick={() => {
        openCopyToSiteDialog(nodeIds, includeDeepOption);
        onCopied?.();
      }}
    >
      {showIcon && <FontAwesomeIcon icon={faCopy} />}
      {t('Copy to site…')}
    </DropdownMenuItem>
  );
}
