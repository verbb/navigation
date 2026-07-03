import builderStyles from './css/style.css?inline';

import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import {
  bootstrapBuilderShadowHost,
  ensureCraftNamespace,
  mountBuilderShadowHost,
} from './utils/bootstrap';
import { ensureNavigationCraftBridge } from './utils/navigationCraftBridge';
import { BuilderShell } from './components/BuilderShell';

ensureCraftNamespace('Navigation');
ensureNavigationCraftBridge();

const builderStyleConfig = {
  pluginHandle: 'navigation',
  styleTexts: [builderStyles],
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

const menuId = Number(appEl?.dataset.menuId ?? window.NavigationBuilderConfig?.menuId);
const siteId = Number(appEl?.dataset.siteId ?? window.NavigationBuilderConfig?.siteId);
const initialSettingsUrl = appEl?.dataset.settingsUrl ?? actionsEl?.dataset.settingsUrl ?? '';
const initialCanEditSettings =
  (appEl?.dataset.canEditSettings ?? actionsEl?.dataset.canEditSettings) === '1';

// App shadow first so plugin-kit portals target the main builder, not a chrome slot.
const boot = bootstrapBuilderShadowHost({
  ...builderStyleConfig,
  containerSelector: '#navigation-builder-app',
  missingContainerMessage: 'Navigation builder container not found: #navigation-builder-app',
});

const headerMountNode = headerEl ? mountBuilderShadowHost(headerEl, builderStyleConfig).mountNode : null;
const instructionsMountNode = instructionsEl
  ? mountBuilderShadowHost(instructionsEl, builderStyleConfig).mountNode
  : null;
const actionsMountNode = actionsEl ? mountBuilderShadowHost(actionsEl, builderStyleConfig).mountNode : null;
const sidebarMountNode = sidebarEl ? mountBuilderShadowHost(sidebarEl, builderStyleConfig).mountNode : null;

if (boot) {
  createRoot(boot.mountNode).render(
    <StrictMode>
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
    </StrictMode>,
  );
}
