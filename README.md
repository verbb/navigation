> [!IMPORTANT]
> **Navigation 4** for **Craft 5** has some breaking changes. Consult our [Upgrading from v3](./docs/get-started/upgrading-from-v3.md) docs for the details.

<p align="center"><img src="https://assets.verbb.io/plugins/navigation/navigation-icon.svg" width="100" height="100" alt="Navigation icon"></p>
<h1 align="center">Navigation for Craft CMS</h1>

Navigation is a Craft CMS plugin for building and rendering menus — nested link trees backed by Craft elements, custom URLs, dynamic branches, and structural node types.

## What's new in Navigation 4

- **New menu builder** — a modern control panel experience with non-live edits you publish when ready, plus menu content editing in slide-outs.
- **Menu element & menu fields** — menu-level custom fields on a dedicated `Menu` element, separate from node fields.
- **Dynamic nodes** — pluggable dynamic sources that project entries, categories, assets, or Commerce products at read time, with entry conditions and sort order.
- **Context API** — `craft.navigation.context(handle)` with `siblings()`, `branch()`, `ancestors()`, and more for section sidebars and sub-nav.
- **Menu breadcrumbs** — `craft.navigation.menuBreadcrumbs(handle)` for trail output based on your menu structure, alongside URL-segment breadcrumbs.
- **Performance & caching** — tagged tree caching, smart hierarchy wiring, and opt-in linked-element hydration to cut down N+1 queries on large menus.
- **Smarter active state** — single-pass matching with `getActiveNodes()`, `getCurrentNodes()`, and improved `tree()` output (`current`, `hasActiveChild`).
- **GraphQL** — menu queries, context, menu breadcrumbs, and hydration flags for headless front ends.
- **Third-party migrations** — import menus from [FreeNav](https://plugins.craftcms.com/free-nav), [Navigate](https://plugins.craftcms.com/navigate), [Olivemenus](https://plugins.craftcms.com/olivemenus), and [tka navigation](https://plugins.craftcms.com/tka-navigation).

## Features

- Create multiple menus for your site
- Link to entries, assets, categories, and Commerce products — or add custom URLs, site links, passive/group columns, and dynamic branches
- Menu-level and node-level custom fields
- Enable or disable nodes, open links in a new window, or apply additional CSS classes
- Automatically updates nodes when linked elements' status or title changes
- Navigation nodes are elements for flexible querying
- Support for third-party node types with hooks
- Multi-site menus with per-site link settings
- Simple `render()` Twig function, or roll your own with `nodes()`, `tree()`, and the Context API
- Generate breadcrumbs from your menu structure or from URL segments
- GraphQL support for menus, nodes, context, and breadcrumbs
- Performance caching with tagged invalidation ([Blitz](https://plugins.craftcms.com/blitz)-friendly)
- Migrate from [FreeNav](https://plugins.craftcms.com/free-nav), [Navigate](https://plugins.craftcms.com/navigate), [Olivemenus](https://plugins.craftcms.com/olivemenus), or [tka navigation](https://plugins.craftcms.com/tka-navigation)
- [Feed Me](https://plugins.craftcms.com/feed-me) support for importing nodes

## Documentation

Visit the [Navigation Plugin page](https://verbb.io/craft-plugins/navigation) for all documentation, guides, pricing and developer resources.

## Support

Get in touch with us via the [Navigation Support page](https://verbb.io/craft-plugins/navigation/support) or by [creating a Github issue](https://github.com/verbb/navigation/issues)

<h2></h2>

<a href="https://verbb.io" target="_blank">
    <img width="101" height="33" src="https://verbb.io/assets/img/verbb-pill.svg" alt="Verbb">
</a>
