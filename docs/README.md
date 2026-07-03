# Navigation Plugin Docs

Navigation’s docs are structured to help you install the plugin, build menus, put them on the site, tune performance, connect them to other systems, and extend them when needed.

These markdown files are consumed on [verbb.io](https://verbb.io); they are not built from this repo for production. For local reading and link checking, use the VitePress preview from the **Navigation plugin root** (the directory that contains the plugin’s main `composer.json`):

```bash
npm install
npm run dev:plugin-docs
```

The dev server runs at [http://localhost:5380](http://localhost:5380).

## Screenshot automation

Screenshots are generated with **`@verbb/docs-screenshots`**, the published npm package for Craft CP screenshot automation. This docs package depends on it in **devDependencies** and the plugin root **`package.json`** exposes it as **`npm run docs:screenshots`**.

The harness creates its own disposable Craft install and database. Point it at a dedicated database server with **`CRAFT_SCREENSHOT_DB_*`** environment variables, then install the browser binary once with **`npx playwright install chromium`**.

**Layout**

- **`@verbb/docs-screenshots`** — CLI and shared capture tooling (see the package’s own README on npm for flags and behaviour).
- **Navigation** — one **`.screenshot.ts` scenario** beside each page it illustrates (for example `menus/overview.screenshot.ts`), plus plugin-local bootstrap and fixtures under **`.screenshots/`** (Navigation-specific helpers under **`.screenshots/navigation/`**). Generated assets land under **`_screenshots/`** in this docs tree.

**Typical workflow**

1. `npm run docs:screenshots -- prepare`
2. `npm run docs:screenshots -- preview --reuse-install last --filter overview`
3. Adjust the scenario while the preview overlay shows the capture region.
4. `npm run docs:screenshots -- capture --reuse-install last --filter overview`

**Useful flags** (see upstream docs for the full set)

- `--preview <id>` — headed preview with capture overlay.
- `--inspect <id>` — preview and pause in Playwright Inspector.
- `--headed` — visible browser without extra debug behaviour.
- `--save-from-preview` — persist framing from preview to the scenario output path.
- `--reuse-install last` — skip Craft bootstrap; reuse the latest prepared install.
- `--keep-install` — keep the temporary install for manual reuse.

**List scenario ids** (from the root of **this** VitePress site — the folder that contains this `README.md`):

```bash
rg -n "id:" . -g "*.screenshot.ts"
```

## Sections

- [Get Started](/get-started/installation-setup) for installation, requirements, configuration, and upgrading from v3.
- [Menus](/menus/overview) for creating menus, menu fields, and node types in the control panel.
- [Templates](/templates/rendering-nodes) for Twig patterns — rendering menus, active state, context sub-nav, and breadcrumbs.
- [Frontend](/frontend/performance-and-caching) for caching, hydration flags, and headless tree output.
- [Getting Elements](/getting-elements/node-queries) for node query parameters.
- [GraphQL](/graphql/query-nodes) for headless node queries, context, and breadcrumbs.
- [Integrations](/integrations/feed-me) for Feed Me import mapping.
- [Reference](/reference/node) for the core public objects most template authors work with.
- [Developers](/developers/events) for extension points, events, custom node types, and command-line tooling.
