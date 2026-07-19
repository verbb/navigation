import { ensureCraftNamespace } from './bootstrap';
import { getJquery } from './cp';

type ElementSelectWidget = {
  on: (event: string, handler: (event: { elements?: Array<{ siteId: number }> }) => void) => void;
};

/**
 * Legacy node editor fields (NodeTypeElements) emit inline JS that expects
 * Craft.Navigation.ElementSelect from navigation.js. The React builder bundle
 * does not load that file, so register the small bridge here.
 */
export function ensureNavigationCraftBridge(): void {
  ensureCraftNamespace('Navigation');

  const craft = (window as typeof window & {
    Craft?: {
      Navigation?: {
        ElementSelect?: unknown;
      };
    };
  }).Craft;

  if (!craft) {
    return;
  }

  if (craft.Navigation?.ElementSelect) {
    return;
  }

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const Garnish = (window as any).Garnish;

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  const $ = getJquery() as any;

  craft.Navigation ??= {};
  craft.Navigation.ElementSelect = Garnish.Base.extend({
    init(elementSelect: string, siteId: string) {
      const $elementSelect = $(elementSelect);
      const $siteId = $(siteId);

      if (!$elementSelect.length) {
        return;
      }

      setTimeout(() => {
        const widget = $elementSelect.data('elementSelect') as ElementSelectWidget | undefined;

        if (!widget) {
          return;
        }

        widget.on('selectElements', (event) => {
          if (event.elements?.length) {
            $siteId.val(String(event.elements[0].siteId));
          }
        });
      }, 100);
    },
  });
};
