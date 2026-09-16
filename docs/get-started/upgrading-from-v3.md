# Upgrading from v3

Use this page when upgrading Navigation 3 to Navigation 4. The database migration updates stored menu data; templates and custom modules can also need changes. Navigation 4 requires Craft CMS 5.9.11 or greater and PHP 8.2 or later.

## Breaking Changes

Review custom SQL, node-type filters, and templates that depend on active-node or nullable child-state results. These require changes even when the original method call still executes.

### Custom URL References

Custom URLs no longer expand environment variables or aliases. Replace existing references with relative paths such as `/contact`, literal destinations, or a sandboxed site token such as `{site.baseUrl}contact`. Use a **Site** node when the link should point to a site's base URL. Review these destinations before publishing your upgraded menus.

### Database Tables

If you have custom SQL or reports that reference Navigation tables directly, update them to the new names:

| v3 | v4 |
| --- | --- |
| `navigation_navs` | `navigation_menus` |
| `navigation_navs_sites` | `navigation_menus_sites` |
| `navigation_nodes.navId` | `navigation_nodes.menuId` |
| `navigation.navs` (project config) | `navigation.menus` |

Per-site URLs and link settings now live in **`navigation_nodes_sites`**.

### Custom Node Type Class Names

If you compare `node.type` (or filter node queries by type), update any references to the new class names. The migration updates stored rows automatically.

| v3 | v4 |
| --- | --- |
| `craft\elements\Entry` | `verbb\navigation\nodetypes\Entry` |
| `craft\elements\Category` | `verbb\navigation\nodetypes\Category` |
| `craft\elements\Asset` | `verbb\navigation\nodetypes\Asset` |
| `craft\commerce\elements\Product` | `verbb\navigation\nodetypes\Product` |

Other built-in types use the same namespace (e.g. `Custom`, `Passive`, `Site`, `GroupColumn`, `Dynamic`).

Menu **Permissions** settings use the same node type classes as keys (not Craft element FQCNs or v3 `*Type` names). The migration rewrites stored keys automatically.

### Active Node Resolution

`craft.navigation.getActiveNode()` (default, without the second argument) now returns the **deepest node whose URL exactly matches the current page** — not the first branch-active ancestor encountered while walking the tree.

**Example menu:**

```
Products   → /products
  Widget   → /products/widget
```

**On `/products/widget`:**

| | v3 | v4 |
| --- | --- | --- |
| `getActiveNode()` | **Products** — first branch-active node in structure order (parent matches as active before the child is considered) | **Widget** — deepest exact URL match |
| `node.active` on Products | `true` | `true` (unchanged — branch highlighting still works) |
| `node.getCurrent()` on Widget | `true` | `true` |

Update templates that used the first active ancestor to choose a section. Passing `true` as the second argument permits a path fallback when there is no exact match; it does **not** force a parent to be returned when an exact match exists.

For a sidebar that lists the current page’s siblings, replace the ancestor-based query with the Context API. These snippets belong where the sidebar obtains its items:

::: code-group
```twig [Navigation 3]
{% set activeNode = craft.navigation.getActiveNode({ handle: 'mainMenu' }) %}
{% set items = activeNode ? craft.navigation.nodes().handle('mainMenu').descendantOf(activeNode).all() : [] %}
```

```twig [Navigation 4]
{% set items = craft.navigation.context('mainMenu').siblings() %}
```
:::

This example deliberately selects direct siblings. If your design needs all descendants, choose the intended section root explicitly instead. The [section sidebar guide](/user-guides/templating/build-a-section-sidebar-with-context-api) explains the available choices.

### `hasActiveChild()` Return Type

`hasActiveChild()` returns a `bool` in Navigation 4. If custom PHP checks strictly for `null` to detect the absence of an active child, replace that condition with `false`:

| Navigation 3 | Navigation 4 |
| --- | --- |
| `$node->hasActiveChild() === null` | `$node->hasActiveChild() === false` |

Ordinary truthy checks can remain unchanged.

## Deprecated Changes

These continue to work with deprecation notices. Work through **Utilities → Deprecation Warnings** at your own pace.

| v3 | v4 |
| --- | --- |
| `verbb\navigation\models\Nav` | `verbb\navigation\models\MenuSettings` |
| `verbb\navigation\services\Navs` | `verbb\navigation\services\Menus` |
| `verbb\navigation\events\NavEvent` | `verbb\navigation\events\MenuEvent` |
| `$node->navId` / `$node->deletedWithNav` | `$node->menuId` / `$node->deletedWithMenu` |
| GraphQL `navigationNavs.*` scopes | GraphQL `navigationMenus.*` scopes |

Deprecated method names (`getNavByHandle()`, `.navHandle()`, etc.) still work; use the menu equivalents when updating your project.

### Twig and PHP Naming (Nav → Menu)

| v3 | v4 |
| --- | --- |
| `craft.navigation.nav()` | `craft.navigation.menu()` |
| `craft.navigation.getNavByHandle()` | `craft.navigation.getMenuByHandle()` |
| `craft.navigation.getAllNavs()` | `craft.navigation.getAllMenus()` |
| `craft.navigation.breadcrumbs()` | `craft.navigation.urlBreadcrumbs()` |
| `Navigation::$plugin->getNavs()` | `Navigation::$plugin->getMenus()` |
| `verbb\navigation\services\Navs::getNavByHandle()` / `saveNav()` / etc. | `verbb\navigation\services\Menus::getMenuByHandle()` / `saveMenu()` / etc. |

**`craft.navigation.getMenuByHandle()`** returns a **`verbb\navigation\elements\Menu`**. Deprecated **`getNavByHandle()`** still returns **`verbb\navigation\models\MenuSettings`**. When you migrate call sites, use the return type that matches your needs (settings vs element field values).

### Node Queries and Properties

| v3 | v4 |
| --- | --- |
| `.navHandle()` / `.nav()` / `.navId()` on node queries | `.handle()` / `.menu()` / `.menuId()` |
| Criteria keys `navHandle`, `nav`, `navId` | `handle`, `menuId` |
| `$node->navId` | `$node->menuId` |
| `verbb\navigation\elements\Node::getNav()` | `verbb\navigation\elements\Node::getMenu()` |
| `verbb\navigation\elements\Node::getIsActive()` | `verbb\navigation\elements\Node::getActive()` / `getActiveState()` |

### PHP Classes

| v3 | v4 |
| --- | --- |
| `verbb\navigation\models\Nav` | `verbb\navigation\models\MenuSettings` |
| `verbb\navigation\services\Navs` | `verbb\navigation\services\Menus` |
| `verbb\navigation\events\NavEvent` | `verbb\navigation\events\MenuEvent` |
| `verbb\navigation\services\Navs::EVENT_*_NAV` | `verbb\navigation\services\Menus::EVENT_*_MENU` |
| `verbb\navigation\events\NavEvent::$nav` | `verbb\navigation\events\MenuEvent::$menu` |

### GraphQL

| v3 | v4 |
| --- | --- |
| Args `navHandle`, `nav`, `navId` | `menuHandle`, `menuId` |
| Fields `navId`, `navHandle`, `navName` | `menuId`, `menuHandle`, `menuName` |
| Schema scope `navigationNavs.*` | `navigationMenus.*` |

### Control Panel and Console

| v3 | v4 |
| --- | --- |
| CP URLs `navigation/navs/*` | `navigation/menus/*` |
| `./craft resave/navigation-nodes --navId=` | `--menuId=` |

### Imported Node Type Aliases

JSON imports accept legacy Navigation 3 node-type aliases and convert them to the registered Navigation 4 class names. Use the current class names when creating export data in custom code; see the mapping under Breaking Changes.

## Changes at a Glance

Not every v4 improvement requires migration work.

### Menu Element and Menu Fields

```twig
{% set menu = craft.navigation.menu('mainMenu').one() %}
{{ menu.myMenuField }}
```

**Settings** (`verbb\navigation\models\MenuSettings`, project config) and **field values** (`verbb\navigation\elements\Menu`) use separate save paths; both are editable from the menu settings screen.

### Context API and Menu Breadcrumbs

```twig
{% set ctx = craft.navigation.context('mainMenu') %}
{% set trail = craft.navigation.menuBreadcrumbs('mainMenu') %}
```

### Read Pipeline

v4 improves read performance without changing typical template output:

- **`withLinkedElements()`** — batch-loads linked Craft elements. **`node.element`** still works without it (lazy-loaded per node).
- **`withNodeHierarchy()`** — wires parent/child relationships in memory after the query. **`node.children`** and the `{% nav %}` tag still work without it (children load on demand). On menu-scoped front-end reads, hierarchy wiring is enabled automatically when you omit the flag; pass **`withNodeHierarchy(false)`** to opt out of that loading.
- **Tree caching** — defaults to **`auto`** on front-end menu reads. Active/current state is always resolved after a cache hit, so highlight behaviour stays correct. Set **`cacheMode`** to **`off`** in **Navigation → Settings → Performance** only when debugging or when you need every read to hit the database.
- **`craft.navigation.tree()`** — returns nested arrays; linked element hydration is **opt-in** via `{ withLinkedElements: true }`. Use **`nodes()`** when you want element objects with the same opt-in defaults as queries.
- **Passive / group nodes** — use **`node.getTag()`** in custom templates (`span` vs `a`); the default `render()` helper respects this for accessibility.

See [Node Queries](/getting-elements/node-queries) and [Performance & Caching](/frontend/performance-and-caching).
