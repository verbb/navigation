// eslint-disable-next-line @typescript-eslint/no-explicit-any
type AnyJQuery = any;

import { getCraft } from './cp';

export function openNodeEditor(
  elementType: string,
  nodeId: number,
  siteId: number,
  onSave?: () => void,
): void {
  (document.activeElement as HTMLElement | null)?.blur?.();

  const $element = (window.$ as AnyJQuery)('<div/>').data({
    id: nodeId,
    siteId,
    type: elementType,
    editable: true,
    savable: true,
  });

  getCraft().createElementEditor(elementType, $element, {
    onSaveElement: () => {
      onSave?.();
    },
  });
}

export function openElementSelector(
  elementType: string,
  settings: {
    siteId: number;
    sources?: string[] | null;
    criteria?: Record<string, unknown>;
    condition?: Record<string, unknown> | null;
    showSiteMenu: boolean;
    onSelect: (elements: Array<{ id: number; siteId: number; url?: string }>) => void;
  },
): void {
  getCraft().createElementSelectorModal(elementType, {
    defaultSiteId: settings.siteId,
    sources: settings.sources ?? undefined,
    criteria: settings.criteria ?? undefined,
    condition: settings.condition ?? undefined,
    multiSelect: true,
    showSiteMenu: settings.showSiteMenu,
    onSelect: (elements: Array<{ id?: number; siteId?: number; url?: string }>) => {
      settings.onSelect(
        elements
          .filter((element): element is { id: number; siteId: number; url?: string } =>
            typeof element.id === 'number' && typeof element.siteId === 'number',
          )
          .map((element) => ({
            id: element.id,
            siteId: element.siteId,
            url: element.url,
          })),
      );
    },
  });
}
