// Real tree and store, with synthetic Craft transport and controls for boundary tests.
import React from 'react';
import { createRoot } from 'react-dom/client';
import { NodeTree } from '../../src/web/src/components/NodeTree';
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
  sendActionRequest: async (_: string, action: string, options: any) => ({ data: await fetch('/action', {
    method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action, ...options.data }),
  }).then(response => response.json()) }),
};
registerNavigationIcons();
function reset() {
  store.setState({ menuId: 1, siteId: 1, loading: false, state: fixture as any, nodes,
    baselineStructureMoves: baselineMoves(nodes as any), structureDirty: false, selectedNodeIds: [] });
}
reset();
(window as any).boundary = { store, nodes, fixture, reset };
function Harness() {
  const dirty = store(s => s.structureDirty);
  return <><button id="delete" onClick={() => void store.getState().deleteNode(3)}>Delete third node</button>
    <button id="discard" onClick={() => void store.getState().discard()}>Discard</button>
    <output id="dirty">{String(dirty)}</output><NodeTree /></>;
}
createRoot(document.getElementById('root')!).render(<Harness />);
