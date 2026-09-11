import { test } from 'node:test';
import assert from 'node:assert/strict';
import { useBuilderStore as store } from '../../src/web/src/store';
import { baselineMoves } from '../../src/web/src/utils/tree';
import type { BuilderNode, BuilderState } from '../../src/web/src/types';

const nodes = [1, 2, 3].map(id => ({ id, title: `Node ${id}`, level: 1, parentId: null,
  hasDescendants: false, pendingDelete: false })) as BuilderNode[];
const state = () => ({ nodes, stagingEnabled: true, session: { changeCount: 0, hasStructureMoves: false },
  menu: { maxLevels: null }, builderTabs: [] }) as unknown as BuilderState;
const tick = () => new Promise(resolve => setImmediate(resolve));
function reset(send: (method: string, action: string, options: any) => Promise<any>) {
  (globalThis as any).window = { Craft: { t: (_: string, s: string) => s,
    cp: { displayNotice() {}, displayError() {} }, sendActionRequest: send } };
  (globalThis as any).confirm = () => true;
  store.setState({ menuId: 1, siteId: 1, nodes, baselineStructureMoves: baselineMoves(nodes),
    structureDirty: false, state: state(), publishing: false, discarding: false });
}
const ids = () => store.getState().nodes.map(n => n.id);

test('a drag during delete survives the response and remains dirty', async () => {
  let complete!: (response: any) => void;
  reset(() => new Promise(resolve => { complete = resolve; }));
  const request = store.getState().deleteNode(3);
  store.getState().setNodes([nodes[1], nodes[0], nodes[2]]);
  complete({ data: { nodes: [nodes[0], nodes[1], { ...nodes[2], pendingDelete: true }], session: { changeCount: 1 } } });
  await request;
  assert.deepEqual(ids(), [2, 1, 3]);
  assert.equal(store.getState().structureDirty, true);
  assert.equal(store.getState().state?.session?.changeCount, 1);
});

test('old menu responses cannot overwrite the current menu', async () => {
  let complete!: (response: any) => void;
  reset(() => new Promise(resolve => { complete = resolve; }));
  const request = store.getState().deleteNode(3);
  store.setState({ menuId: 99, siteId: 2, nodes: [{ ...nodes[0], id: 99 }] });
  complete({ data: { nodes, session: { changeCount: 1 } } });
  await request;
  assert.deepEqual(ids(), [99]);
});

test('mutations for one menu reach the server in invocation order', async () => {
  const calls: Array<{ id: number; complete: (response: any) => void }> = [];
  reset((_method, _action, options) => new Promise(complete => calls.push({ id: options.data.nodeId, complete })));
  const first = store.getState().deleteNode(1);
  const second = store.getState().deleteNode(2);
  assert.deepEqual(calls.map(c => c.id), [1]);
  calls[0].complete({ data: { nodes: nodes.slice(1), session: null } });
  await tick();
  assert.deepEqual(calls.map(c => c.id), [1, 2]);
  calls[1].complete({ data: { nodes: nodes.slice(2), session: null } });
  await Promise.all([first, second]);
  assert.deepEqual(ids(), [3]);
  assert.equal(store.getState().state?.session, null);
});

test('discard resets structure when no newer edit was made', async () => {
  reset(async (_method, action) => ({ data: action.endsWith('get-state') ? state() : {} }));
  store.getState().setNodes([nodes[1], nodes[0], nodes[2]]);
  await store.getState().discard();
  assert.deepEqual(ids(), [1, 2, 3]);
  assert.equal(store.getState().structureDirty, false);
});

test('publish refresh preserves a drag made after the save started', async () => {
  let complete!: (response: any) => void;
  reset(async (_method, action) => action.endsWith('get-state')
    ? { data: state() } : new Promise(resolve => { complete = resolve; }));
  const request = store.getState().publish();
  store.getState().setNodes([nodes[2], nodes[0], nodes[1]]);
  complete({ data: {} });
  await request;
  assert.deepEqual(ids(), [3, 1, 2]);
  assert.equal(store.getState().structureDirty, true);
});

test('refresh reconciles against edits made while it was loading', async () => {
  let complete!: (response: any) => void;
  reset(() => new Promise(resolve => { complete = resolve; }));
  const request = store.getState().refresh();
  store.getState().setNodes([nodes[1], nodes[0], nodes[2]]);
  complete({ data: state() });
  await request;
  assert.deepEqual(ids(), [2, 1, 3]);
  assert.equal(store.getState().structureDirty, true);
});

test('returning to the old baseline during publish remains a new unsaved edit', async () => {
  let complete!: (response: any) => void;
  const savedOrder = [nodes[1], nodes[0], nodes[2]];
  reset(async (_method, action) => action.endsWith('get-state')
    ? { data: { ...state(), nodes: savedOrder } } : new Promise(resolve => { complete = resolve; }));
  store.getState().setNodes(savedOrder);
  const request = store.getState().publish();
  store.getState().setNodes(nodes);
  assert.equal(store.getState().structureDirty, false);
  complete({ data: {} });
  await request;
  assert.deepEqual(ids(), [1, 2, 3]);
  assert.equal(store.getState().structureDirty, true);
});

test('queued live structure writes carry forward the preceding successful revision', async () => {
  const api = await import('../../src/web/src/api');
  const sent: string[] = [];
  let complete!: (value: any) => void;
  reset(async (_method, action, options) => {
    if (action.endsWith('get-state')) return { data: { ...state(), structureRevision: 'baseline' } };
    sent.push(options.data.structureRevision);
    if (sent.length === 1) return new Promise(resolve => { complete = resolve; });
    return { data: { structureRevision: 'second' } };
  });
  await api.fetchBuilderState(1, 1);
  const first = api.applyStructure(1, 1, [], 'baseline');
  const second = api.applyStructure(1, 1, [], 'baseline');
  complete({ data: { structureRevision: 'first' } });
  await Promise.all([first, second]);
  assert.deepEqual(sent, ['baseline', 'first']);
});


test('publishing after duplication uses the returned structure baseline', async () => {
  const api = await import('../../src/web/src/api');
  let sent: string | undefined;
  reset(async (_method, action, options) => {
    if (action.endsWith('get-state')) return { data: { ...state(), structureRevision: 'before-duplicate' } };
    if (action.endsWith('duplicate-nodes')) return { data: { structureRevision: 'after-duplicate' } };
    sent = options.data.structureRevision;
    return { data: {} };
  });
  await api.fetchBuilderState(1, 1);
  await api.duplicateNodes(1, 1, [1]);
  await api.publishMenu(1, 1, true, [], 'before-duplicate');
  assert.equal(sent, 'after-duplicate');
});
