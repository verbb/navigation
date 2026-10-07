// Real tree and store, with synthetic Craft transport and controls for boundary tests.
// Mirror the app's `pluginKitStyles` injection without relying on package CSS
// `@import` resolution inside this standalone esbuild harness.
import '../../node_modules/@verbb/plugin-kit-web/dist/tokens.css';
import '../../node_modules/@verbb/plugin-kit-web/dist/utilities/fouce.css';
import '../../node_modules/@verbb/plugin-kit-web/dist/styles/overlay-content.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { Button } from '@verbb/plugin-kit-react/components/Button';
import { Icon } from '@verbb/plugin-kit-react/components/Icon';
import { emptySet, registerIcons, triangleExclamation } from '@verbb/plugin-kit-icons';
import { NodeTree } from '../../src/web/src/components/NodeTree';
import { BuilderApp } from '../../src/web/src/components/BuilderApp';
import { BuilderErrorBoundary } from '../../src/web/src/components/BuilderErrorBoundary';
import { BuilderLoadError } from '../../src/web/src/components/BuilderLoadError';
import { useBuilderStore as store } from '../../src/web/src/store';
import { baselineMoves } from '../../src/web/src/utils/tree';
import { registerNavigationIcons } from '../../src/web/src/icons/registerNavigationIcons';

const nodes = [1, 2, 3].map(id => ({ id, title: `Node ${id}`, type: 'Custom', typeLabel: 'Custom',
  typeClass: 'Custom', typeColorRgb: '0,0,0', typeTextColorRgb: '0,0,0', url: `/node-${id}`,
  level: 1, parentId: null, status: 'live', newWindow: id === 1, classes: null,
  pendingAdd: false, pendingDelete: false, pendingEdit: false, enabled: true,
  enabledForSite: true, hasDescendants: false, isElementLinked: id === 1,
  hasTitleOverride: id === 1 }));
const fixture = { nodes, canCopyToSite: false, copyToSiteTargets: [], elementType: 'Node', menu: { maxLevels: 1 }, builderTabs: [], stagingEnabled: true,
  session: { changeCount: 0, hasStructureMoves: false } };
(window as any).Craft = { t: (_: string, text: string) => text,
  cp: { displayNotice() {}, displayError() {} },
  sendActionRequest: async (_: string, action: string, options: any) => {
    const response = await fetch('/action', {
      method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action, ...options.data }),
    });
    const data = await response.json();

    if (!response.ok) {
      throw {
        message: `Request failed with status code ${response.status}`,
        response: { status: response.status, statusText: response.statusText, data },
      };
    }

    return { data };
  },
};
registerNavigationIcons();
registerIcons({ emptySet, triangleExclamation });
function reset() {
  store.setState({ menuId: 1, siteId: 1, loading: false, state: fixture as any, nodes,
    baselineStructureMoves: baselineMoves(nodes as any), structureDirty: false, selectedNodeIds: [],
    statusFilter: 'all' });
}
reset();
if (new URLSearchParams(window.location.search).has('empty')) {
  store.setState({ nodes: [], state: { ...fixture, nodes: [] } as any });
}
(window as any).boundary = { store, nodes, fixture, reset };

function RenderCrash(): never {
  throw new Error('Synthetic render exception from the Navigation builder.');
}

function LegacyErrorBoundaryState() {
  return (
    <div style={{ display: 'flex', flex: 1, alignItems: 'center', justifyContent: 'center', padding: '3rem 0' }}>
      <div style={{ display: 'flex', width: '90%', maxWidth: 560, flexDirection: 'column', alignItems: 'center', textAlign: 'center' }}>
        <div
          style={{
            display: 'flex', alignItems: 'center', justifyContent: 'center', width: 40, height: 40,
            marginBottom: 12, borderRadius: 10,
            backgroundColor: 'color-mix(in srgb, var(--pk-color-rose-500) 12%, transparent)',
          }}
        >
          <Icon icon="triangle-exclamation" style={{ width: 20, height: 20, color: 'var(--pk-color-rose-600)' }} />
        </div>
        <h2 style={{ marginBottom: 8, fontSize: 16, fontWeight: 500, color: 'var(--pk-color-gray-900)' }}>Something went wrong</h2>
        <p style={{ marginBottom: 16, maxWidth: 560, fontSize: 14, color: 'var(--pk-color-gray-500)' }}>
          The navigation builder failed to load. Please refresh the page or try again.
        </p>
        <details style={{ marginBottom: 16, width: '100%', textAlign: 'center', fontSize: 12, color: 'var(--pk-color-rose-600)' }}>
          <summary style={{ cursor: 'pointer' }}>Show error details</summary>
          <div style={{ marginTop: 8, whiteSpace: 'pre-wrap', textAlign: 'left' }}>
            <p style={{ marginBottom: 8 }}>Error: Synthetic render exception from the Navigation builder.</p>
            <div>at RenderCrash (browser-harness.tsx:1:1)</div>
          </div>
        </details>
        <div style={{ marginTop: 8, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }}>
          <Button type="button" variant="primary">Reload</Button>
        </div>
      </div>
    </div>
  );
}

function Harness() {
  const dirty = store(s => s.structureDirty);
  const search = new URLSearchParams(window.location.search);
  const errorType = search.get('error');

  if (search.get('legacy') === 'load-error') {
    return (
      <div style={{ borderRadius: 4, border: '1px solid #fecaca', background: '#fef2f2', padding: 16, fontSize: 14, color: '#b91c1c' }}>
        Couldn’t load menu builder.
      </div>
    );
  }

  if (search.get('legacy') === 'boundary') {
    return <LegacyErrorBoundaryState />;
  }

  if (search.get('legacy') === 'empty') {
    return (
      <div style={{ overflow: 'hidden', borderRadius: 6, border: '1px solid rgba(0,0,0,.1)', background: '#fff', boxShadow: '0 0 0 1px rgba(205,216,228,.25),0 2px 12px rgba(205,216,228,.5)' }}>
        <div style={{ display: 'flex', minHeight: 320, alignItems: 'center', justifyContent: 'center', border: '1px dashed #cdd8e4', padding: 48, fontSize: 14, color: '#606d7b' }}>
          <p style={{ margin: 0 }}>No nodes yet. Use the sidebar to add your first node.</p>
        </div>
      </div>
    );
  }

  if (search.has('init-error')) {
    return <BuilderApp menuId={1} siteId={1} />;
  }

  if (search.has('empty')) {
    return (
      <div className="-mx-6 -mb-2.5 overflow-hidden rounded-md border border-black/10 bg-white shadow-[0_0_0_1px_rgba(205,216,228,0.25),0_2px_12px_rgba(205,216,228,0.5)]">
        <NodeTree />
      </div>
    );
  }

  if (errorType === 'boundary') {
    return <BuilderErrorBoundary><RenderCrash /></BuilderErrorBoundary>;
  }

  if (errorType || search.has('load-error')) {
    const errors: Record<string, unknown> = {
      js: Object.assign(new Error("Cannot read properties of undefined (reading 'map')"), {
        stack: "TypeError: Cannot read properties of undefined (reading 'map')\n    at normalizeNodes (builder.js:214:27)\n    at initializeBuilder (builder.js:88:16)\n    at async init (store.js:73:9)",
      }),
      api: {
        message: 'Request failed with status code 500',
        response: {
          status: 500,
          statusText: 'Internal Server Error',
          data: {
            name: 'Twig\\Error\\RuntimeError',
            message: 'Variable "siteUrl" does not exist.',
            file: '/var/www/html/vendor/twig/twig/src/ExpressionParser.php',
            line: 223,
            trace: [
              { file: '/var/www/html/vendor/twig/twig/src/Environment.php', line: 420, function: 'parse' },
              { file: '/var/www/html/vendor/verbb/navigation/src/helpers/NodeOutputSafety.php', line: 96, function: 'renderAuthorTemplate' },
            ],
          },
        },
      },
      boot: Object.assign(new TypeError('Builder boot data is missing the required “nodes” array.'), {
        stack: "TypeError: Builder boot data is missing the required “nodes” array.\n    at validateBuilderState (store.js:51:11)\n    at initializeBuilder (store.js:70:5)\n    at async init (store.js:73:9)",
      }),
    };

    return <BuilderLoadError error={errors[errorType ?? 'api'] ?? errors.api} onRetry={() => {}} />;
  }

  return <><button id="delete" onClick={() => void store.getState().deleteNode(3)}>Delete third node</button>
    <button id="discard" onClick={() => void store.getState().discard()}>Discard</button>
    <output id="dirty">{String(dirty)}</output><NodeTree /></>;
}
createRoot(document.getElementById('root')!).render(<Harness />);
