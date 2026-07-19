import { useEffect, useMemo, useRef, useState } from 'react';
import { Button } from '@verbb/plugin-kit-react/components/Button';
import { CheckboxInput } from '@verbb/plugin-kit-react/components/CheckboxInput';
import { Dialog } from '@verbb/plugin-kit-react/components/Dialog';
import { Lightswitch } from '@verbb/plugin-kit-react/components/Lightswitch';
import { SelectInput } from '@verbb/plugin-kit-react/components/SelectInput';
import { useBuilderStore } from '../store';
import { t } from '../api';
import { type DialogHost, type PkOpenChangeEvent } from '../utils/pluginKitEvents';

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
  const dialogRef = useRef<DialogHost | null>(null);
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

    // Imperative show() matches overlay-isolation after the menu → dialog handoff.
    void dialogRef.current?.show?.();
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
    <Dialog
      ref={(el) => {
        dialogRef.current = el as DialogHost | null;
      }}
      open={open}
      label={t('Copy to site')}
      onPkOpenChange={(event) => onOpenChange((event as PkOpenChangeEvent).detail.open)}
    >
      <div className="flex flex-col gap-4">
        <div className="flex flex-col gap-1.5">
          <span className="text-sm font-medium text-gray-800">{t('Target site')}</span>
          <SelectInput
            id="copy-to-site-target"
            options={targetSiteOptions}
            value={targetSiteId ?? undefined}
            onChange={(value) => setTargetSiteId(Number(value))}
            placeholder={t('Select a site')}
            aria-label={t('Target site')}
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

      <Button slot="footer" type="button" variant="default" onClick={() => onOpenChange(false)} disabled={submitting}>
        {t('Cancel')}
      </Button>
      <Button
        slot="footer"
        type="button"
        variant="primary"
        onClick={() => void handleCopy()}
        disabled={!targetSiteId || submitting}
      >
        {t('Copy')}
      </Button>
    </Dialog>
  );
}
