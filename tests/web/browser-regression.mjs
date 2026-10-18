import { build } from 'esbuild';
import { chromium } from 'playwright';
import { createServer } from 'node:http';
import assert from 'node:assert/strict';
import path from 'node:path';

const output = await build({ entryPoints: ['tests/web/browser-harness.tsx'], bundle: true, write: false,
  outdir: 'out', format: 'iife', jsx: 'automatic', define: { 'process.env.NODE_ENV': '"test"' },
  alias: {
    react: path.resolve('node_modules/react'),
    'react-dom': path.resolve('node_modules/react-dom'),
    '@verbb/plugin-kit-web': path.resolve('node_modules/@verbb/plugin-kit-web/dist'),
  } });
const harnessJs = output.outputFiles.find(file => file.path.endsWith('.js'))?.text;
const harnessCss = output.outputFiles.find(file => file.path.endsWith('.css'))?.text ?? '';
const server = createServer((request, response) => {
  if (request.url === '/harness.js') {
    response.setHeader('Content-Type', 'application/javascript'); response.end(harnessJs);
  } else if (request.url === '/harness.css') {
    response.setHeader('Content-Type', 'text/css'); response.end(harnessCss);
  } else {
    response.setHeader('Content-Type', 'text/html');
    response.end('<html><head><link rel="stylesheet" href="/harness.css"><style>[role=row]{display:grid;grid-template-columns:40px 350px 120px;height:40px}pk-button{display:inline-block;min-width:24px;min-height:24px}[role=cell]{display:flex;align-items:center;gap:8px}</style></head><body><div id="root"></div><script src="/harness.js"></script></body></html>');
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
  let copyAttempts = 0;
  const copyPayloads = [];
  let finishDelete;
  const delayed = new Promise(resolve => { finishDelete = resolve; });
  await page.route('**/action', async route => {
    const body = route.request().postDataJSON();
    if (body.action.endsWith('get-state') && new URL(page.url()).searchParams.has('init-error')) {
      await route.fulfill({
        status: 500,
        json: {
          name: 'Twig\\Error\\RuntimeError',
          message: 'Variable "siteUrl" does not exist.',
          file: '/var/www/html/vendor/twig/twig/src/ExpressionParser.php',
          line: 223,
          trace: [
            { file: '/var/www/html/vendor/twig/twig/src/Environment.php', line: 420, function: 'parse' },
            { file: '/var/www/html/vendor/verbb/navigation/src/helpers/NodeOutputSafety.php', line: 96, function: 'renderAuthorTemplate' },
          ],
        },
      });
      return;
    }
    if (body.action.endsWith('copy-to-site')) {
      copyPayloads.push(body);
      copyAttempts += 1;
      await route.fulfill(copyAttempts === 1
        ? { status: 503, json: { message: 'Temporary connection failure' } }
        : { json: { copiedNodeIds: [4, 5], message: 'Nodes copied.' } });
      return;
    }
    if (body.action.endsWith('stage-delete')) await delayed;
    const fixture = await page.evaluate(() => window.boundary.fixture);
    const data = body.action.endsWith('get-state') ? fixture : {
      nodes: fixture.nodes.map(n => ({ ...n, pendingDelete: n.id === 3 })), session: { changeCount: 1 },
    };
    await route.fulfill({ json: data });
  });
  await page.goto(`http://127.0.0.1:${server.address().port}`);
  await page.getByRole('row').filter({ hasText: 'Node 2' }).waitFor();
  const titleOverride = page.locator('[data-node-id="1"] [data-title-override]');
  assert.equal(await titleOverride.count(), 1);
  assert.equal(await page.locator('[data-node-id="2"] [data-title-override]').count(), 0);
  assert.equal(await titleOverride.getAttribute('tabindex'), '0');
  await titleOverride.focus();
  await page.getByText('Custom title', { exact: true }).waitFor({ state: 'visible' });
  assert.equal(await titleOverride.locator('[title], svg title').count(), 0);
  assert.equal(await titleOverride.getAttribute('title'), null);
  const newWindow = page.locator('[data-node-id="1"] [aria-label="Opens in a new window"]');
  await newWindow.focus();
  await page.getByText('Opens in a new window', { exact: true }).waitFor({ state: 'visible' });
  assert.equal(await newWindow.locator('[title], svg title').count(), 0);
  assert.equal(await newWindow.getAttribute('title'), null);

  const selectedNodeIds = () => page.evaluate(
    () => window.boundary.store.getState().selectedNodeIds,
  );
  const firstCheckbox = page.locator('[data-node-id="1"] pk-checkbox');
  const secondCheckbox = page.locator('[data-node-id="2"] pk-checkbox');
  const thirdCheckbox = page.locator('[data-node-id="3"] pk-checkbox');

  await firstCheckbox.click();
  await secondCheckbox.click();
  assert.deepEqual(await selectedNodeIds(), [1, 2]);

  const thirdSelectionCell = thirdCheckbox.locator('xpath=..');
  const [thirdCellBox, thirdCheckboxBox] = await Promise.all([
    thirdSelectionCell.boundingBox(),
    thirdCheckbox.boundingBox(),
  ]);
  assert.ok(thirdCellBox && thirdCheckboxBox);
  const cellPaddingX = thirdCellBox.x + thirdCellBox.width - 3;
  assert.ok(cellPaddingX > thirdCheckboxBox.x + thirdCheckboxBox.width);
  await thirdSelectionCell.click({
    position: { x: thirdCellBox.width - 3, y: thirdCellBox.height / 2 },
  });
  await page.waitForTimeout(250);
  assert.deepEqual(await selectedNodeIds(), [1, 2, 3]);

  await page.evaluate(() => window.boundary.store.getState().clearSelection());
  await firstCheckbox.click();
  await page.keyboard.down('Shift');
  try {
    await thirdCheckbox.click();
    assert.deepEqual(await selectedNodeIds(), [1, 2, 3]);
    assert.equal(await thirdCheckbox.evaluate(element => element.checked), true);
    await secondCheckbox.click();
    assert.deepEqual(await selectedNodeIds(), [1, 2]);
    assert.equal(await secondCheckbox.evaluate(element => element.checked), true);
    await thirdCheckbox.click();
  } finally {
    await page.keyboard.up('Shift');
  }
  assert.deepEqual(await selectedNodeIds(), [1, 2, 3]);
  assert.equal(await thirdCheckbox.evaluate(element => element.checked), true);
  await page.evaluate(() => window.boundary.store.getState().clearSelection());

  await page.locator('#delete').click();
  // Exercise the actual tree drag handlers with a browser DataTransfer object.
  const source = page.getByRole('row').filter({ hasText: 'Node 2' });
  const target = page.getByRole('row').filter({ hasText: 'Node 1' });
  const dataTransfer = await page.evaluateHandle(() => new DataTransfer());
  await source.locator('[draggable="true"][aria-label="Drag to reorder"]').dispatchEvent('dragstart', { dataTransfer });
  const box = await target.boundingBox();
  await target.dispatchEvent('dragover', { dataTransfer, clientX: box.x + 100, clientY: box.y + 1 });
  await target.dispatchEvent('drop', { dataTransfer, clientX: box.x + 100, clientY: box.y + 1 });
  await source.locator('[draggable="true"][aria-label="Drag to reorder"]').dispatchEvent('dragend', { dataTransfer });
  await page.waitForFunction(() => window.boundary.store.getState().nodes[0]?.id === 2);
  finishDelete();
  await page.waitForFunction(() => window.boundary.store.getState().state.session.changeCount === 1);
  assert.deepEqual(await page.evaluate(() => window.boundary.store.getState().nodes.map(n => n.id)), [2, 1, 3]);
  assert.equal(await page.locator('#dirty').textContent(), 'true');
  page.on('dialog', dialog => dialog.accept());
  await page.locator('#discard').click();
  await page.waitForFunction(() => !window.boundary.store.getState().structureDirty);
  assert.deepEqual(await page.evaluate(() => window.boundary.store.getState().nodes.map(n => n.id)), [1, 2, 3]);

  await page.evaluate(() => window.boundary.store.setState({
    nodes: [],
    state: { ...window.boundary.fixture, nodes: [] },
  }));
  await page.getByRole('heading', { name: 'No nodes yet' }).waitFor();
  await page.evaluate(() => window.boundary.reset());
  await page.evaluate(() => window.boundary.store.setState({ statusFilter: 'disabled' }));
  await page.getByRole('heading', { name: 'No nodes match the selected status.' }).waitFor();

  await page.goto(`http://127.0.0.1:${server.address().port}?controls=1`);
  await page.locator('[data-node-id="2"] pk-checkbox').click();
  const nodeOrder = () => page.evaluate(() => window.boundary.store.getState().nodes.map(node => node.id));
  const titleInput = page.getByRole('textbox', { name: 'New title', exact: true });
  await titleInput.fill('Writing a title');
  await titleInput.press('Alt+ArrowUp');
  assert.deepEqual(await nodeOrder(), [1, 2, 3], 'Text editing must not reorder the selected node');
  for (const name of ['Dialog action', 'Menu action']) {
    await page.getByRole('button', { name, exact: true }).press('Alt+ArrowUp');
    assert.deepEqual(await nodeOrder(), [1, 2, 3], 'Overlay controls must not reorder the underlying tree');
  }
  await page.locator('[data-node-id="2"]').press('Alt+ArrowUp');
  assert.deepEqual(await nodeOrder(), [2, 1, 3], 'Tree keyboard reordering must still work');
  await page.getByRole('button', { name: 'View', exact: true }).click();
  const columnCheckbox = page.locator('.navigation-node-tree-view-popover').getByRole('checkbox');
  await columnCheckbox.waitFor();
  await page.locator('.navigation-node-tree-view-popover').getByText('Type', { exact: true }).click();
  assert.equal(await columnCheckbox.isChecked(), false, 'The visible column label must toggle the checkbox');
  assert.equal(await page.getByRole('checkbox', { name: 'Type', exact: true }).count(), 1);
  assert.equal(await page.getByRole('columnheader', { name: 'Type', exact: true }).count(), 0);
  await columnCheckbox.press('Space');
  await page.getByRole('columnheader', { name: 'Type', exact: true }).waitFor();

  await page.goto(`http://127.0.0.1:${server.address().port}?copy-dialog=1`);
  await page.getByRole('dialog').waitFor();
  const descendants = page.getByRole('checkbox', { name: 'Include descendants', exact: true });
  // Click the visible label, not the custom element's surrounding whitespace.
  await page.locator('pk-checkbox [part="label"]').click();
  await descendants.and(page.locator(':checked')).waitFor();
  const failedCopy = page.waitForResponse(response => response.status() === 503);
  await page.getByRole('button', { name: 'Copy', exact: true }).click();
  await failedCopy;
  assert.equal(await page.getByRole('dialog').isVisible(), true);
  assert.equal(await descendants.isChecked(), true);
  await page.getByRole('button', { name: 'Copy', exact: true }).click();
  await page.getByRole('dialog').waitFor({ state: 'hidden' });
  assert.equal(copyPayloads.length, 2);
  assert.ok(copyPayloads.every(payload => payload.siteId === 2 && payload.deep === true));

  await page.context().grantPermissions(['clipboard-read', 'clipboard-write']);
  await page.goto(`http://127.0.0.1:${server.address().port}?init-error=1`);
  await page.getByText('Couldn’t load menu builder.').waitFor();
  assert.equal(await page.getByText('Variable "siteUrl" does not exist.').isVisible(), false);
  await page.getByText('Show error details').click();
  await page.getByText('Variable "siteUrl" does not exist.').waitFor();
  await page.getByRole('button', { name: 'Copy error details' }).click();
  const copiedError = await page.evaluate(() => navigator.clipboard.readText());
  assert.match(copiedError, /HTTP 500 Internal Server Error/);
  assert.match(copiedError, /Variable "siteUrl" does not exist\./);
  assert.match(copiedError, /NodeOutputSafety\.php:96/);

  await page.goto(`http://127.0.0.1:${server.address().port}?error=boundary`);
  await page.getByText('Something went wrong').waitFor();
  await page.getByText('Show error details').click();
  await page.getByText('Synthetic render exception from the Navigation builder.').waitFor();
  await page.getByRole('button', { name: 'Reload' }).waitFor();
  assert.deepEqual(errors, []);
  console.log('PASS: builder interactions and shared initialization/render error states (synthetic Craft HTTP responses).');
} catch (error) {
  console.error({ errors, body: await page?.locator('body').innerText() });
  throw error;
} finally {
  await browser?.close();
  await new Promise(resolve => server.close(resolve));
}
