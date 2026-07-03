import { configurePluginKitReact, createCraftHostBridge } from '@verbb/plugin-kit-react/utils';

export const ensureCraftNamespace = (namespacePath: string) => {
  const parts = namespacePath.split('.').map((part) => part.trim()).filter(Boolean);
  let current = window.Craft as Record<string, unknown>;

  parts.forEach((part) => {
    if (typeof current[part] === 'undefined') {
      current[part] = {};
    }

    current = current[part] as Record<string, unknown>;
  });

  return current;
};

export type BuilderShadowConfig = {
  pluginHandle: string;
  styleTexts: string[];
  styleNamespace?: string;
  styleAttr?: string;
  rootAttr?: string;
  portalClassName?: string;
  translationCategory?: string;
};

type ResolvedBuilderShadowConfig = Required<
  Pick<BuilderShadowConfig, 'styleNamespace' | 'styleAttr' | 'rootAttr' | 'portalClassName' | 'translationCategory'>
> &
  BuilderShadowConfig;

const resolveBuilderShadowConfig = (config: BuilderShadowConfig): ResolvedBuilderShadowConfig => {
  const pluginHandle = config.pluginHandle;

  return {
    ...config,
    styleNamespace: config.styleNamespace || pluginHandle,
    styleAttr: config.styleAttr || `data-${pluginHandle}-shadow-style`,
    rootAttr: config.rootAttr || `data-${pluginHandle}-shadow-root`,
    portalClassName: config.portalClassName || `${pluginHandle}-ui`,
    translationCategory: config.translationCategory || pluginHandle,
  };
};

const injectStyles = (
  parent: HTMLElement | ShadowRoot,
  styleTexts: string[],
  styleAttr: string,
  styleNamespace: string,
) => {
  parent.querySelectorAll(`[${styleAttr}]`).forEach((node) => node.remove());

  styleTexts.forEach((cssText, index) => {
    if (!cssText) {
      return;
    }

    const style = document.createElement('style');
    style.setAttribute(styleAttr, `${styleNamespace}-${index}`);
    style.textContent = cssText;
    parent.appendChild(style);
  });
};

let pluginKitConfigured = false;

const ensurePluginKitConfigured = (shadowRoot: ShadowRoot, config: ResolvedBuilderShadowConfig) => {
  if (pluginKitConfigured) {
    return;
  }

  configurePluginKitReact({
    portalClassName: config.portalClassName,
    portalContainer: shadowRoot,
    shadowRootSelectors: [`[${config.rootAttr}]`],
    translationCategory: config.translationCategory,
    hostBridge: createCraftHostBridge(),
  });

  pluginKitConfigured = true;
};

export const mountBuilderShadowHost = (
  container: HTMLElement,
  config: BuilderShadowConfig,
): { mountNode: HTMLElement; shadowRoot: ShadowRoot | null } => {
  const resolved = resolveBuilderShadowConfig(config);

  if (!container.attachShadow) {
    injectStyles(container, resolved.styleTexts, resolved.styleAttr, resolved.styleNamespace);

    let mountNode = container.querySelector(`[${resolved.rootAttr}]`) as HTMLElement | null;

    if (!mountNode) {
      mountNode = document.createElement('div');
      mountNode.setAttribute(resolved.rootAttr, '');
      mountNode.className = 'w-full';
      container.appendChild(mountNode);
    }

    return { mountNode, shadowRoot: null };
  }

  const shadowRoot = container.shadowRoot ?? container.attachShadow({ mode: 'open' });

  injectStyles(shadowRoot, resolved.styleTexts, resolved.styleAttr, resolved.styleNamespace);

  let mountNode = shadowRoot.querySelector(`[${resolved.rootAttr}]`) as HTMLElement | null;

  if (!mountNode) {
    mountNode = document.createElement('div');
    mountNode.setAttribute(resolved.rootAttr, '');
    mountNode.className = 'w-full';
    shadowRoot.appendChild(mountNode);
  }

  ensurePluginKitConfigured(shadowRoot, resolved);

  return { mountNode, shadowRoot };
};

export const bootstrapBuilderShadowHost = ({
  containerSelector,
  missingContainerMessage = null,
  ...config
}: BuilderShadowConfig & {
  containerSelector: string;
  missingContainerMessage?: string | null;
}) => {
  const targetContainer = document.querySelector(containerSelector) as HTMLElement | null;

  if (!targetContainer) {
    if (missingContainerMessage) {
      console.error(missingContainerMessage);
    }

    return null;
  }

  const { mountNode, shadowRoot } = mountBuilderShadowHost(targetContainer, config);

  return { targetContainer, mountNode, shadowRoot };
};
