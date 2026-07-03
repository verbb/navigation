export type NavigationCraft = {
  t: (category: string, message: string, params?: Record<string, unknown>) => string;
  cp: {
    displayNotice: (message: string) => void;
    displayError: (message?: string) => void;
  };
  sendActionRequest: (
    method: 'GET' | 'POST',
    action: string,
    options?: { data?: Record<string, unknown> },
  ) => Promise<{ data: Record<string, unknown> }>;
  createElementEditor: (
    elementType: string,
    $element: unknown,
    settings?: Record<string, unknown>,
  ) => unknown;
  createElementSelectorModal: (
    elementType: string,
    settings: Record<string, unknown>,
  ) => unknown;
  initUiElements: (container?: unknown) => void;
};

export function getCraft(): NavigationCraft {
  return (window as unknown as { Craft: NavigationCraft }).Craft;
}

export function getGarnish() {
  return (window as Window & { Garnish: { getPostData: (form: unknown) => string } }).Garnish;
}

export function getJquery() {
  return (window as Window & { $: (selector: unknown) => { length: number; find: (sel: string) => unknown } }).$;
}
