import assert from 'node:assert/strict';
import { readFile, readdir, access } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../../', import.meta.url));
const sourceRoot = path.join(root, 'src/web/src/');
const dist = path.join(root, 'src/web/assets/cp/dist');
const manifest = JSON.parse(await readFile(path.join(dist, 'manifest.json'), 'utf8'));
assert.ok(manifest['src/main.tsx']?.isEntry, 'Missing builder entry');
for (const entry of Object.values(manifest)) {
  await access(path.join(dist, entry.file));
  for (const file of entry.css ?? []) await access(path.join(dist, file));
  for (const key of [...(entry.imports ?? []), ...(entry.dynamicImports ?? [])]) {
    assert.ok(manifest[key], `Missing manifest dependency: ${key}`);
  }
}

const checked = new Set();
for (const file of await readdir(path.join(dist, 'assets'))) {
  if (!file.endsWith('.js.map')) continue;
  const mapPath = path.join(dist, 'assets', file);
  const map = JSON.parse(await readFile(mapPath, 'utf8'));
  for (const [index, source] of map.sources.entries()) {
    const sourcePath = path.resolve(path.dirname(mapPath), map.sourceRoot ?? '', source);
    if (!sourcePath.startsWith(sourceRoot) || !/\.[jt]sx?$/.test(sourcePath)) continue;
    assert.equal(map.sourcesContent[index], await readFile(sourcePath, 'utf8'),
      `Stale production asset for ${path.relative(root, sourcePath)}; run npm run build`);
    checked.add(path.relative(sourceRoot, sourcePath));
  }
}
for (const required of ['main.tsx', 'store.ts', 'api.ts', 'components/NodeTree.tsx']) {
  assert.ok(checked.has(required), `Missing bundled source: ${required}`);
}
console.log(`PASS: manifest dependencies exist; ${checked.size} bundled source files match the checkout.`);
