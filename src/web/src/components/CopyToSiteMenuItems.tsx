import {
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
} from '@verbb/plugin-kit-react/components';
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
  const copyNodesToSite = useBuilderStore((s) => s.copyNodesToSite);

  if (!canCopyToSite || copyToSiteTargets.length === 0) {
    return null;
  }

  const handleCopy = (targetSiteId: number, deep: boolean) => {
    void copyNodesToSite(nodeIds, targetSiteId, deep).then(() => {
      onCopied?.();
    });
  };

  const siteItems = (deep: boolean) =>
    copyToSiteTargets.map((site) => (
      <DropdownMenuItem key={`${deep ? 'deep' : 'shallow'}-${site.id}`} onClick={() => handleCopy(site.id, deep)}>
        {site.name}
      </DropdownMenuItem>
    ));

  return (
    <DropdownMenuSub>
      <DropdownMenuSubTrigger disabled={disabled || nodeIds.length === 0}>
        {showIcon && <FontAwesomeIcon icon={faCopy} />}
        {t('Copy to site')}
      </DropdownMenuSubTrigger>
      <DropdownMenuSubContent className="min-w-44">
        {siteItems(false)}
        {includeDeepOption && (
          <>
            <DropdownMenuSeparator />
            <DropdownMenuSub>
              <DropdownMenuSubTrigger>{t('With descendants')}</DropdownMenuSubTrigger>
              <DropdownMenuSubContent className="min-w-44">{siteItems(true)}</DropdownMenuSubContent>
            </DropdownMenuSub>
          </>
        )}
      </DropdownMenuSubContent>
    </DropdownMenuSub>
  );
}
