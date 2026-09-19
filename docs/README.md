# Navigation Plugin Docs

Navigation’s docs are structured to help you install the plugin, build menus, put them on the site, tune performance, connect them to other systems, and extend them when needed.

These markdown files are consumed on [verbb.io](https://verbb.io); they are not built from this repo for production. For local reading and link checking, use the VitePress preview from the **Navigation plugin root** (the directory that contains the plugin’s main `composer.json`):

```bash
npm install
npm run dev:plugin-docs
```

The dev server runs at `http://localhost:5380`.

## Screenshot Automation

All product captures use the shared **`@verbb/craft-screenshots`** package from the plugin’s top-level **`screenshots/`** directory. Documentation scenarios live under **`screenshots/scenarios/docs/`**, Navigation-specific fixtures and framing live under **`screenshots/support/docs/`**, and generated documentation images live under **`screenshots/output/docs/`**.

Run the workflow from the Navigation plugin root:

```bash
npm run screenshots -- prepare
npm run screenshots -- preview --reuse-install last --filter docs/feature-tour/overview
npm run screenshots -- capture --reuse-install last --filter docs/feature-tour/overview
```

The filter matches the scenario file path. The shared package owns the disposable Craft installation, Verbb capture identity, retina enforcement, and timeless control-panel cleanup; Navigation owns its scenarios, deterministic fixtures, framing, and outputs.

## Sections

- [Get Started](/get-started/installation-setup) for installation, requirements, configuration, and upgrading from v3.
- [Feature Tour](/feature-tour/overview) for creating menus, menu fields, and node types in the control panel.
- [Template Guides](/template-guides/rendering-nodes) for Twig patterns — rendering menus, active state, context sub-nav, and breadcrumbs.
- [Frontend](/frontend/performance-and-caching) for caching, loading options, and headless tree output.
- [Getting Elements](/getting-elements/node-queries) for node query parameters.
- [GraphQL](/graphql/query-nodes) for headless node queries, context, and breadcrumbs.
- [Integrations](/integrations/feed-me) for Feed Me import mapping.
- [Reference](/reference/node) for the core public objects most template authors work with.
- [Developers](/developers/events) for extension points, events, custom node types, and command-line tooling.

The sidebar uses **Feature Tour**, **Template Guides**, and **User Guides** with matching `feature-tour/` and `template-guides/` paths. User Guides uses the matching `user-guides/` folder and public path. The website supports historical `guides/` sources and URLs as legacy inputs. Guide category index files contain the titles, links, and descriptions used by the website’s card layouts. The local preview opens those indexes and also lists their guide pages in the sidebar.
