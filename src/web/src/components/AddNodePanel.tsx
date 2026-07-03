import { useCallback, useEffect, useState } from 'react';
import { Button } from '@verbb/plugin-kit-react/components';
import { SchemaFormEngine, useSchemaFormEngine } from '@verbb/plugin-kit-react/forms';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faPlus } from '@fortawesome/pro-solid-svg-icons';
import { useBuilderStore } from '../store';
import { addNodes, t } from '../api';
import { getCraft } from '../utils/cp';
import { openElementSelector } from '../utils/craft';
import type { BuilderTab } from '../types';

function buildPayload(
  tab: BuilderTab,
  menuId: number,
  siteId: number,
  values: Record<string, unknown>,
): Record<string, unknown> {
  const parentId = values.parentId;

  return {
    menuId,
    siteId,
    type: tab.type,
    parentId: parentId === '' || parentId === 0 || parentId === '0' || parentId == null ? null : parentId,
    newWindow: values.newWindow ?? false,
    enabledForPropagatedSites: values.enabledForPropagatedSites ?? true,
    title: values.title ?? '',
    url: values.url ?? '',
    data: values.data ?? {},
  };
}

export function AddNodePanel({ tab }: { tab: BuilderTab }) {
  const menuId = useBuilderStore((s) => s.menuId);
  const siteId = useBuilderStore((s) => s.siteId);
  const showSiteMenu = useBuilderStore((s) => s.state?.menu.showSiteMenu ?? false);
  const refresh = useBuilderStore((s) => s.refresh);
  const [submitting, setSubmitting] = useState(false);
  const [errors, setErrors] = useState<string[]>([]);

  const form = useSchemaFormEngine({
    schemaIndex: tab.schemaIndex,
    defaultValues: tab.defaultValues,
  });

  const resetForm = useCallback(() => {
    Object.entries(tab.defaultValues).forEach(([key, value]) => {
      form.setFieldValue(key, value);
    });
  }, [form, tab.defaultValues]);

  const submitNodeType = useCallback(
    async (values: Record<string, unknown>) => {
      setSubmitting(true);
      setErrors([]);

      try {
        await addNodes([buildPayload(tab, menuId, siteId, values)]);
        getCraft().cp.displayNotice(t('Node added. Save menu to apply.'));
        resetForm();
        await refresh();
      } catch (error) {
        const response = (error as { response?: { data?: { errors?: Record<string, string[]> } } })
          ?.response;
        const list: string[] = [];

        if (response?.data?.errors) {
          for (const messages of Object.values(response.data.errors)) {
            list.push(...messages);
          }
        }

        setErrors(list.length ? list : [t('Couldn’t add node.')]);
      } finally {
        setSubmitting(false);
      }
    },
    [menuId, refresh, resetForm, siteId, tab],
  );

  useEffect(() => {
    form.onSubmit(submitNodeType);

    return () => {
      form.onSubmit(null);
    };
  }, [form, submitNodeType]);

  const openElementPicker = () => {
    if (!tab.elementType) {
      return;
    }

    const values = form.store.state.values as Record<string, unknown>;

    openElementSelector(tab.elementType, {
      siteId,
      sources: tab.sources,
      criteria: tab.pickerConfig?.criteria ?? undefined,
      condition: tab.pickerConfig?.condition ?? undefined,
      showSiteMenu,
      onSelect: async (elements) => {
        setSubmitting(true);
        setErrors([]);

        try {
          const payloads = elements.map((element) => ({
            ...buildPayload(tab, menuId, siteId, values),
            elementId: element.id,
            elementSiteId: element.siteId,
            url: element.url,
          }));

          await addNodes(payloads);
          getCraft().cp.displayNotice(t('Nodes added. Save menu to apply.'));
          await refresh();
        } catch (error) {
          const response = (error as { response?: { data?: { message?: string } } })?.response;
          setErrors([response?.data?.message ?? t('Couldn’t add node.')]);
        } finally {
          setSubmitting(false);
        }
      },
    });
  };

  return (
    <div className="space-y-4">
      <SchemaFormEngine form={form} withoutForm className="space-y-4" />

      {errors.length > 0 && (
        <ul className="list-disc space-y-1 pl-5 text-sm text-red-600">
          {errors.map((err) => (
            <li key={err}>{err}</li>
          ))}
        </ul>
      )}

      {tab.category === 'nodeType' ? (
        <Button type="button" variant="primary" loading={submitting} onClick={() => form.handleSubmit()}>
          <FontAwesomeIcon icon={faPlus} className="size-3.5" />
          {tab.button}
        </Button>
      ) : (
        <Button type="button" variant="primary" loading={submitting} onClick={openElementPicker}>
          <FontAwesomeIcon icon={faPlus} className="size-3.5" />
          {tab.button}
        </Button>
      )}
    </div>
  );
}
