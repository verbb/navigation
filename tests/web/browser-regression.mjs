import { build } from 'esbuild';
import { chromium } from 'playwright';
import { createServer } from 'node:http';
import assert from 'node:assert/strict';

const output = await build({ entryPoints: ['tests/web/browser-harness.tsx'], bundle: true, write: false,
  format: 'iife', jsx: 'automatic', define: { 'process.env.NODE_ENV': '"test"' } });
const server = createServer((request, response) => {
  if (request.url === '/harness.js') {
    response.setHeader('Content-Type', 'application/javascript'); response.end(output.outputFiles[0].text);
  } else {
    response.setHeader('Content-Type', 'text/html');
    response.end('<html><style>[role=row]{display:grid;grid-template-columns:40px 350px 120px;height:40px}pk-button{display:inline-block;min-width:24px;min-height:24px}[role=cell]{display:flex;align-items:center;gap:8px}</style><div id="root"></div><script src="/harness.js"></script></html>');
  }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
let browser;
let page;
const errors = [];
try {
  browser = await chromium.launch({ headless: true });
  page = await browser.newPage();
  page.setDefaultTimeout(8000);
  page.on('pageerror', error => errors.push(error.message));
  let finishDelete;
  const delayed = new Promise(resolve => { finishDelete = resolve; });
  await page.route('**/action', async route => {
    const body = route.request().postDataJSON();
    if (body.action.endsWith('stage-delete')) await delayed;
    const fixture = await page.evaluate(() => window.boundary.fixture);
    const data = body.action.endsWith('get-state') ? fixture : {
      nodes: fixture.nodes.map(n => ({ ...n, pendingDelete: n.id === 3 })), session: { changeCount: 1 },
    };
    await route.fulfill({ json: data });
  });
  await page.goto(`http://127.0.0.1:${server.address().port}`);
  await page.getByRole('row').filter({ hasText: 'Node 2' }).waitFor();
  await page.locator('#delete').click();
  // Exercise the actual tree drag handlers with a browser DataTransfer object.
  const source = page.getByRole('row').filter({ hasText: 'Node 2' });
  const target = page.getByRole('row').filter({ hasText: 'Node 1' });
  const dataTransfer = await page.evaluateHandle(() => new DataTransfer());
  await source.locator('[title="Drag to reorder"]').dispatchEvent('dragstart', { dataTransfer });
  const box = await target.boundingBox();
  await target.dispatchEvent('dragover', { dataTransfer, clientX: box.x + 100, clientY: box.y + 1 });
  await target.dispatchEvent('drop', { dataTransfer, clientX: box.x + 100, clientY: box.y + 1 });
  await source.locator('[title="Drag to reorder"]').dispatchEvent('dragend', { dataTransfer });
  await page.waitForFunction(() => window.boundary.store.getState().nodes[0]?.id === 2);
  finishDelete();
  await page.waitForFunction(() => window.boundary.store.getState().state.session.changeCount === 1);
  assert.deepEqual(await page.evaluate(() => window.boundary.store.getState().nodes.map(n => n.id)), [2, 1, 3]);
  assert.equal(await page.locator('#dirty').textContent(), 'true');
  page.on('dialog', dialog => dialog.accept());
  await page.locator('#discard').click();
  await page.waitForFunction(() => !window.boundary.store.getState().structureDirty);
  assert.deepEqual(await page.evaluate(() => window.boundary.store.getState().nodes.map(n => n.id)), [1, 2, 3]);
  assert.deepEqual(errors, []);
  console.log('PASS: real tree drag during delayed delete; discard restores baseline (synthetic Craft HTTP responses).');
} catch (error) {
  console.error({ errors, body: await page?.locator('body').innerText() });
  throw error;
} finally {
  await browser?.close();
  await new Promise(resolve => server.close(resolve));
}
