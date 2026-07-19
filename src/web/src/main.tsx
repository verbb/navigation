// Head FOUCE tokens. Component imports register custom elements.
import '@verbb/plugin-kit-react/style.css';
import builderStyles from './css/style.css?inline';
import pluginKitStyles from '@verbb/plugin-kit-react/style.css?inline';

import { createRoot } from 'react-dom/client';
import { StrictMode } from 'react';
import {
  createCraftHostBridge,
  mountShadowApp,
  PluginKitProvider,
} from '@verbb/plugin-kit-react/utils';

import { BuilderShell } from './components/BuilderShell';
import { registerNavigationIcons } from './icons/registerNavigationIcons';
import {
  ensureCraftNamespace,
  mountBuilderShadowHost,
} from './utils/bootstrap';
import { ensureNavigationCraftBridge } from './utils/navigationCraftBridge';

ensureCraftNamespace('Navigation');
ensureNavigationCraftBridge();
registerNavigationIcons();

const builderStyleConfig = {
  pluginHandle: 'navigation',
  // Tokens into each shadow root — head FOUCE CSS alone does not pierce shadow.
  styleTexts: [pluginKitStyles, builderStyles],
  styleNamespace: 'navigation',
  styleAttr: 'data-navigation-shadow-style',
  rootAttr: 'data-navigation-shadow-root',
  portalClassName: 'navigation-ui',
  translationCategory: 'navigation',
};

const appEl = document.querySelector('#navigation-builder-app') as HTMLElement | null;
const headerEl = document.querySelector('#navigation-builder-header-root') as HTMLElement | null;
const instructionsEl = document.querySelector('#navigation-builder-instructions-root') as HTMLElement | null;
const actionsEl = document.querySelector('#navigation-builder-actions-root') as HTMLElement | null;
const sidebarEl = document.querySelector('#navigation-builder-sidebar-root') as HTMLElement | null;

if (!appEl) {
  console.error('Navigation builder container not found: #navigation-builder-app');
} else {
  const menuId = Number(appEl.dataset.menuId ?? window.NavigationBuilderConfig?.menuId);
  const siteId = Number(appEl.dataset.siteId ?? window.NavigationBuilderConfig?.siteId);
  const initialSettingsUrl = appEl.dataset.settingsUrl ?? actionsEl?.dataset.settingsUrl ?? '';
  const initialCanEditSettings =
    (appEl.dataset.canEditSettings ?? actionsEl?.dataset.canEditSettings) === '1';

  const { mountNode, portalContainer } = mountShadowApp({
    element: appEl,
    styles: builderStyleConfig.styleTexts,
    styleAttr: builderStyleConfig.styleAttr,
    rootAttr: builderStyleConfig.rootAttr,
  });

  const headerMountNode = headerEl ? mountBuilderShadowHost(headerEl, builderStyleConfig).mountNode : null;
  const instructionsMountNode = instructionsEl
    ? mountBuilderShadowHost(instructionsEl, builderStyleConfig).mountNode
    : null;
  const actionsMountNode = actionsEl ? mountBuilderShadowHost(actionsEl, builderStyleConfig).mountNode : null;
  const sidebarMountNode = sidebarEl ? mountBuilderShadowHost(sidebarEl, builderStyleConfig).mountNode : null;

  // SchemaForm / Craft action helpers need the host bridge.
  createRoot(mountNode).render(
    <StrictMode>
      <PluginKitProvider
        translationCategory={builderStyleConfig.translationCategory}
        portalContainer={portalContainer}
        portalClassName={builderStyleConfig.portalClassName}
        shadowRootSelectors={[`[${builderStyleConfig.rootAttr}]`]}
        hostBridge={createCraftHostBridge()}
      >
        <BuilderShell
          menuId={menuId}
          siteId={siteId}
          initialSettingsUrl={initialSettingsUrl}
          initialCanEditSettings={initialCanEditSettings}
          headerMountNode={headerMountNode}
          instructionsMountNode={instructionsMountNode}
          actionsMountNode={actionsMountNode}
          sidebarMountNode={sidebarMountNode}
        />
      </PluginKitProvider>
    </StrictMode>,
  );
}
