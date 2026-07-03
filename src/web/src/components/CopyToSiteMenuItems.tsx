import {
  DropdownMenuItem,
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
  onCopied?: () => void;
};

export function CopyToSiteMenuItems({ nodeIds, disabled = false, onCopied }: Props) {
  const canCopyToSite = useBuilderStore((s) => s.state?.canCopyToSite ?? false);
  const copyToSiteTargets = useBuilderStore((s) => s.state?.copyToSiteTargets ?? []);
  const copyNodesToSite = useBuilderStore((s) => s.copyNodesToSite);

  if (!canCopyToSite || copyToSiteTargets.length === 0) {
    return null;
  }

  return (
    <DropdownMenuSub>
      <DropdownMenuSubTrigger disabled={disabled || nodeIds.length === 0}>
        <FontAwesomeIcon icon={faCopy} />
        {t('Copy to site')}
      </DropdownMenuSubTrigger>
      <DropdownMenuSubContent className="min-w-44">
        {copyToSiteTargets.map((site) => (
          <DropdownMenuItem
            key={site.id}
            onClick={() => {
              void copyNodesToSite(nodeIds, site.id).then(() => {
                onCopied?.();
              });
            }}
          >
            {site.name}
          </DropdownMenuItem>
        ))}
      </DropdownMenuSubContent>
    </DropdownMenuSub>
  );
}
