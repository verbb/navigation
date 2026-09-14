import { chromium } from 'playwright';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';

// Fixed to the owned test application; never accept an arbitrary customer/site URL.
const base = 'https://navigation-react-tests.ddev.site';
const output = new URL('../../.cache/builder-craft/', import.meta.url);
await fs.mkdir(output, { recursive: true });
await fs.rm(new URL('result.json', output), { force: true });
const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 1000 } });
const errors = [];
let phase = 'login';
page.on('pageerror', error => errors.push({ phase, stack: error.stack || error.message }));
let menuId;
try {
  const login = await page.goto(`${base}/admin/login`);
  assert.equal(login.status(), 200, 'Run ddev test first and wait for it to finish.');
  await page.locator('input[name="username"]:visible').fill('admin');
  await page.locator('input[name="password"]:visible').fill('testing-only-password');
  await page.locator('input[name="password"]:visible').press('Enter');
  await page.waitForURL(url => !url.pathname.includes('/login'));

  phase = 'create menu';
  const name = `Browser workflow ${Date.now()}`;
  await page.goto(`${base}/admin/navigation/menus/new`);
  await page.locator('#name').fill(name);
  await page.locator('#handle').fill(`browserWorkflow${Date.now()}`);
  await page.getByRole('button', { name: 'Save', exact: true }).click();
  await page.waitForURL(url => !url.pathname.endsWith('/new'));
  await page.goto(`${base}/admin/navigation`);
  await page.getByRole('link', { name, exact: true }).click();
  menuId = Number(page.url().match(/\/build\/(\d+)/)[1]);
  const builderUrl = page.url();
  const sidebar = page.locator('#navigation-builder-sidebar');
  await sidebar.getByRole('button', { name: 'Custom URL', exact: true }).click();
  const row = title => page.locator('[data-tree-row]').filter({ hasText: title });

  phase = 'add nodes';
  for (const [title, url] of [['Workflow parent', '/workflow-parent'], ['Workflow child', '/workflow-child']]) {
    await sidebar.getByRole('textbox').nth(0).fill(title);
    await sidebar.getByRole('textbox').nth(1).fill(url);
    await sidebar.getByRole('button', { name: /Add/ }).click();
    await row(title).waitFor();
  }

  // Use actual pointer-driven HTML drag/drop, then the native Craft element editor.
  phase = 'drag';
  const target = row('Workflow parent');
  const targetBox = await target.boundingBox();
  await row('Workflow child').locator('[aria-label="Drag to reorder"]').dragTo(target, {
    targetPosition: { x: 180, y: targetBox.height / 2 },
  });
  await page.locator('[data-tree-row][data-level="2"]').filter({ hasText: 'Workflow child' }).waitFor();
  phase = 'native edit';
  await row('Workflow child').getByText('Workflow child', { exact: true }).dblclick();
  const slideout = page.locator('.slideout:visible');
  await slideout.getByRole('textbox', { name: /^Title/ }).fill('Workflow child edited');
  await slideout.getByRole('textbox', { name: /^URL/ }).fill('/workflow-child-edited');
  await slideout.getByRole('textbox', { name: /^URL/ }).press('Tab');
  // Let Craft finish its observed autosave before submitting the native form.
  await page.waitForFunction(() => {
    const form = document.querySelector('.slideout [data-element-editor], .slideout[data-element-editor]');
    const editor = form && window.$(form).data('elementEditor');
    return editor && !editor.savingDraft && editor.lastSerializedValue === editor.serializeForm(true);
  });
  await slideout.getByRole('button', { name: 'Save', exact: true }).click();
  await slideout.waitFor({ state: 'hidden' });
  await row('Workflow child edited').waitFor();

  phase = 'publish';
  const [response] = await Promise.all([
    page.waitForResponse(response => decodeURIComponent(response.url()).includes('navigation/build-sessions/publish')),
    page.locator('#navigation-builder-actions-root').getByRole('button', { name: /^Save(?: \(\d+\))?$/ }).click(),
  ]);
  assert.equal(response.status(), 200);
  assert.notEqual((await response.json()).success, false);
  await page.goto(builderUrl);
  await row('Workflow child edited').waitFor();
  assert.equal(await row('Workflow parent').getAttribute('data-level'), '1');
  assert.equal(await row('Workflow child edited').getAttribute('data-level'), '2');

  // Read-only server verification independently checks the UI-authored persisted tree.
  const state = await page.evaluate(async menuId => (await Craft.sendActionRequest('POST', 'navigation/builder/get-state', { data: { menuId, siteId: Craft.siteId } })).data, menuId);
  assert.deepEqual(state.nodes.map(node => ({ title: node.title, url: node.url, level: node.level, parentId: node.parentId, pendingAdd: node.pendingAdd, pendingEdit: node.pendingEdit })), [
    { title: 'Workflow parent', url: '/workflow-parent', level: 1, parentId: null, pendingAdd: false, pendingEdit: false },
    { title: 'Workflow child edited', url: '/workflow-child-edited', level: 2, parentId: state.nodes[0].id, pendingAdd: false, pendingEdit: false },
  ]);
  assert.deepEqual(errors, []);
  await page.screenshot({ path: new URL('passed.png', output).pathname, fullPage: true });
} catch (error) {
  await page.screenshot({ path: new URL('failure.png', output).pathname, fullPage: true });
  await fs.writeFile(new URL('failure.txt', output), `${error.stack}\n${JSON.stringify(errors, null, 2)}\n${await page.locator('body').innerText()}`);
  throw error;
} finally {
  try {
    if (menuId) {
      await page.evaluate(async id => {
        const response = await Craft.sendActionRequest('POST', 'navigation/menus/delete-menu', { data: { id } });
        if (response.status !== 200 || response.data.success === false) throw new Error('Could not clean up browser fixture menu.');
      }, menuId);
    }
  } finally {
    await browser.close();
  }
}
await fs.writeFile(new URL('result.json', output), JSON.stringify({ pass: true, checks: ['UI create', 'UI add', 'nested pointer drag', 'native edit', 'UI publish', 'reload and persisted hierarchy'], errors }, null, 2));
console.log('PASS: real Craft add, nested drag, edit, publish and reload.');
