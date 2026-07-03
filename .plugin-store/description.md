Navigation is a Craft CMS plugin for building and rendering menus — nested link trees backed by Craft elements, custom URLs, dynamic branches, and structural node types.

## What's new in Navigation 4

- **React menu builder** — a modern control panel experience with non-live edits you publish when ready, plus menu content editing in slide-outs.
- **Menu element & menu fields** — menu-level custom fields on a dedicated `Menu` element, separate from node fields.
- **Dynamic nodes** — pluggable dynamic sources that project entries, categories, assets, or Commerce products at read time, with entry conditions and sort order.
- **Context API** — `craft.navigation.context(handle)` with `siblings()`, `branch()`, `ancestors()`, and more for section sidebars and sub-nav.
- **Menu breadcrumbs** — `craft.navigation.menuBreadcrumbs(handle)` for trail output based on your menu structure, alongside URL-segment breadcrumbs.
- **Performance & caching** — tagged tree caching, smart hierarchy wiring, and opt-in linked-element hydration to cut down N+1 queries on large menus.
- **Smarter active state** — single-pass matching with `getActiveNodes()`, `getCurrentNodes()`, and improved `tree()` output (`current`, `hasActiveChild`).
- **GraphQL** — menu queries, context, menu breadcrumbs, and hydration flags for headless front ends.
- **Third-party migrations** — import menus from FreeNav, Navigate, Olivemenus, and tka navigation.

## Features

- Create multiple menus for your site.
- Link to entries, assets, categories, and Commerce products — or add custom URLs, site links, passive/group columns, and dynamic branches.
- Menu-level and node-level custom fields.
- Enable or disable nodes, open links in a new window, or apply additional CSS classes.
- Automatically updates nodes when linked elements' status or title changes.
- Navigation nodes are Craft elements for flexible querying in Twig and PHP.
- Support for third-party node types with hooks and custom node type classes.
- Multi-site menus with per-site link settings (`url`, `urlSuffix`, linked element site).
- Simple `render()` Twig function, or roll your own with `nodes()`, `tree()`, and the Context API.
- Generate breadcrumbs from your menu structure (`menuBreadcrumbs()`) or from URL segments (`urlBreadcrumbs()`).
- GraphQL support for menus, nodes, context, and breadcrumbs.
- Performance caching with tagged invalidation — works well with full-page cache plugins like Blitz.
- JSON import/export for moving menus between environments.
- Feed Me support for importing nodes.

### Node types

- **Entry** — link to a section entry.
- **Category** — link to a category.
- **Asset** — link to an asset.
- **Product** — link to a Commerce product.
- **Custom** — custom URL with optional per-site overrides.
- **Site** — link to another site in a multi-site install.
- **Passive** — structural label without a link (renders as a `<span>` by default).
- **Group/column** — structural grouping for mega menus and columns.
- **Dynamic** — read-time projection from a section, category group, asset volume, or product type — with entry conditions and sort order.

### Build & manage

- Drag-and-drop menu builder in the control panel with nested structure editing.
- Non-live editing — structure moves, node adds/deletes, and menu content changes are drafted before you publish or discard.
- Menu content editing via Craft CP slide-outs (field layout configured on the Menu Fields tab).
- Per-menu permissions for node types and user groups.
- Copy nodes between sites.

### Templates & front end

- `craft.navigation.render()` for quick, themeable output.
- `craft.navigation.nodes()` and `craft.navigation.tree()` for full control over markup.
- Context API for siblings, branch, ancestors, and related helpers — ideal for sidebars and section sub-nav.
- Active-state helpers: `getActiveNode()`, `getActiveNodes()`, `getCurrentNodes()`, plus `active`, `current`, and `hasActiveChild` on tree output.
- Opt-in query flags: `withLinkedElements()`, `withNodeHierarchy()`, `withMenu()`, and cache controls for large menus.

### Migrations

Import menus from other Craft navigation plugins. Source plugin data is never modified.

- [FreeNav](https://plugins.craftcms.com/free-nav)
- [Navigate](https://plugins.craftcms.com/navigate)
- [Olivemenus](https://plugins.craftcms.com/olivemenus)
- [tka navigation](https://plugins.craftcms.com/tka-navigation)

Run migrations from **Navigation → Settings**, or via console commands (`php craft navigation/migrate/free-nav`, etc.).

## Documentation

Visit the [Navigation Plugin page](https://verbb.io/craft-plugins/navigation) for all documentation, guides, pricing and developer resources.

## Support

Get in touch with us via the [Navigation Support page](https://verbb.io/craft-plugins/navigation/support) or by [creating a Github issue](https://github.com/verbb/navigation/issues)
