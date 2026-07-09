import { useEffect, useMemo, useState } from 'react';
import {
  Button,
  CheckboxInput,
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  Label,
  Lightswitch,
  SelectInput,
} from '@verbb/plugin-kit-react/components';
import { useBuilderStore } from '../store';
import { t } from '../api';

const DEFAULT_REMAP_LINKED_ELEMENTS = true;

type Props = {
  open: boolean;
  nodeIds: number[];
  includeDeepOption?: boolean;
  onOpenChange: (open: boolean) => void;
};

export function CopyToSiteDialog({
  open,
  nodeIds,
  includeDeepOption = false,
  onOpenChange,
}: Props) {
  const copyToSiteTargets = useBuilderStore((s) => s.state?.copyToSiteTargets ?? []);
  const nodes = useBuilderStore((s) => s.nodes);
  const copyNodesToSite = useBuilderStore((s) => s.copyNodesToSite);

  const selectedNodes = useMemo(
    () => nodes.filter((node) => nodeIds.includes(node.id)),
    [nodes, nodeIds],
  );

  const hasElementLinkedNodes = selectedNodes.some((node) => node.isElementLinked);
  const showDescendantsOption =
    includeDeepOption && selectedNodes.some((node) => node.hasDescendants);

  const targetSiteOptions = useMemo(
    () => copyToSiteTargets.map((site) => ({ value: site.id, label: site.name })),
    [copyToSiteTargets],
  );

  const [targetSiteId, setTargetSiteId] = useState<number | null>(null);
  const [includeDescendants, setIncludeDescendants] = useState(false);
  const [remapLinkedElements, setRemapLinkedElements] = useState(DEFAULT_REMAP_LINKED_ELEMENTS);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    if (!open) {
      return;
    }

    setTargetSiteId(copyToSiteTargets[0]?.id ?? null);
    setIncludeDescendants(false);
    setRemapLinkedElements(DEFAULT_REMAP_LINKED_ELEMENTS);
    setSubmitting(false);
  }, [open, copyToSiteTargets]);

  const handleCopy = async () => {
    if (!targetSiteId || !nodeIds.length || submitting) {
      return;
    }

    setSubmitting(true);

    try {
      await copyNodesToSite(nodeIds, targetSiteId, includeDescendants, remapLinkedElements);
      onOpenChange(false);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent showCloseButton className="max-w-md" portalClassName="navigation-copy-to-site-dialog">
        <DialogHeader showCloseButton={false}>
          <DialogTitle>{t('Copy to site')}</DialogTitle>
        </DialogHeader>

        <div className="flex flex-col gap-4 p-4">
          <div className="flex flex-col gap-1.5">
            <Label htmlFor="copy-to-site-target">{t('Target site')}</Label>
            <SelectInput
              id="copy-to-site-target"
              options={targetSiteOptions}
              value={targetSiteId ?? undefined}
              onChange={(value) => setTargetSiteId(Number(value))}
              placeholder={t('Select a site')}
              triggerClassName="w-full"
              modal={false}
            />
          </div>

          {showDescendantsOption && (
            <CheckboxInput
              label={t('Include descendants')}
              checked={includeDescendants}
              onCheckedChange={(checked) => setIncludeDescendants(!!checked)}
            />
          )}

          {hasElementLinkedNodes && (
            <div className="flex items-start justify-between gap-4 rounded-lg border border-black/10 p-3">
              <div className="min-w-0 flex-1">
                <div className="text-sm font-medium">{t('Use elements from target site')}</div>
                <p className="mt-1 text-xs text-gray-500">
                  {t('Element-linked nodes will use each linked element on the selected site, when available.')}
                </p>
              </div>
              <Lightswitch
                checked={remapLinkedElements}
                onCheckedChange={(checked) => setRemapLinkedElements(!!checked)}
                aria-label={t('Use elements from target site')}
              />
            </div>
          )}
        </div>

        <DialogFooter className="flex flex-row gap-2">
          <Button type="button" variant="default" onClick={() => onOpenChange(false)} disabled={submitting}>
            {t('Cancel')}
          </Button>
          <Button
            type="button"
            variant="primary"
            onClick={() => void handleCopy()}
            disabled={!targetSiteId || submitting}
          >
            {t('Copy')}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
