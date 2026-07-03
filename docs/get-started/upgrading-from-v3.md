# Upgrading From v3

Navigation 4 standardises **menu** terminology, adds menu-level custom fields, improves read performance and active-state handling, and stores per-site link settings in a dedicated table.

Despite being a major release, we've kept breaking changes to a minimum.

## Breaking Changes

### Database tables

If you have custom SQL or reports that reference Navigation tables directly, update them to the new names:

| v3 | v4 |
| --- | --- |
| `navigation_navs` | `navigation_menus` |
| `navigation_navs_sites` | `navigation_menus_sites` |
| `navigation_nodes.navId` | `navigation_nodes.menuId` |
| `navigation.navs` (project config) | `navigation.menus` |

Per-site URLs and link settings now live in **`navigation_nodes_sites`**.

### Custom node type class names

If you compare `node.type` (or filter node queries by type), update any references to the new class names. The migration updates stored rows automatically.

| v3 | v4 |
| --- | --- |
| `craft\elements\Entry` | `verbb\navigation\nodetypes\Entry` |
| `craft\elements\Category` | `verbb\navigation\nodetypes\Category` |
| `craft\elements\Asset` | `verbb\navigation\nodetypes\Asset` |
| `craft\commerce\elements\Product` | `verbb\navigation\nodetypes\Product` |

Other built-in types use the same namespace (e.g. `Custom`, `Passive`, `Site`, `GroupColumn`, `Dynamic`).

Menu **Permissions** settings use the same node type classes as keys (not Craft element FQCNs or v3 `*Type` names). The migration rewrites stored keys automatically.

## Behaviour Changes

These are intentional improvements. They are not breaking in the sense that old call sites still run, but output or return values may differ from v3. Review templates and modules that depend on the previous behaviour.

### Active node resolution

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

Most sites only notice this if they used `getActiveNode()` to drive contextual sub-nav (e.g. `.descendantOf(activeNode)`). If you relied on the parent being returned, pass `true` as the second argument for branch/path matching, or use `craft.navigation.getActiveNodes()` / `getCurrentNodes()` when you need multiple matches.

```twig
{# v4: deepest exact match (current page node) #}
{% set activeNode = craft.navigation.getActiveNode({ handle: 'mainMenu' }) %}

{# Branch/path fallback when there is no exact node for the URL #}
{% set sectionNode = craft.navigation.getActiveNode({ handle: 'mainMenu' }, true) %}
```

### `hasActiveChild()` return type

`hasActiveChild()` now always returns a `bool`. Remove strict `=== null` checks if you had code branching on a nullable return.

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

### Twig and PHP naming (nav → menu)

| v3 | v4 |
| --- | --- |
| `craft.navigation.nav()` | `craft.navigation.menu()` |
| `craft.navigation.getNavByHandle()` | `craft.navigation.getMenuByHandle()` |
| `craft.navigation.getAllNavs()` | `craft.navigation.getAllMenus()` |
| `craft.navigation.breadcrumbs()` | `craft.navigation.urlBreadcrumbs()` |
| `Navigation::$plugin->getNavs()` | `Navigation::$plugin->getMenus()` |
| `verbb\navigation\services\Navs::getNavByHandle()` / `saveNav()` / etc. | `verbb\navigation\services\Menus::getMenuByHandle()` / `saveMenu()` / etc. |

Canonical **`craft.navigation.getMenuByHandle()`** returns a **`verbb\navigation\elements\Menu`**. Deprecated **`getNavByHandle()`** still returns **`verbb\navigation\models\MenuSettings`**. When you migrate call sites, use the return type that matches your needs (settings vs element field values).

### Node queries and properties

| v3 | v4 |
| --- | --- |
| `.navHandle()` / `.nav()` / `.navId()` on node queries | `.handle()` / `.menu()` / `.menuId()` |
| Criteria keys `navHandle`, `nav`, `navId` | `menuHandle`, `menuId` |
| `$node->navId` | `$node->menuId` |
| `verbb\navigation\elements\Node::getNav()` | `verbb\navigation\elements\Node::getMenu()` |
| `verbb\navigation\elements\Node::getIsActive()` | `verbb\navigation\elements\Node::getActive()` / `getActiveState()` |

### PHP classes

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

### Control panel and console

| v3 | v4 |
| --- | --- |
| CP URLs `navigation/navs/*` | `navigation/menus/*` |
| `./craft resave/navigation-nodes --navId=` | `--menuId=` |

## Changes at a Glance

Not every v4 improvement requires migration work.

### Menu element and menu fields

```twig
{% set menu = craft.navigation.menu('mainMenu').one() %}
{{ menu.myMenuField }}
```

**Settings** (`verbb\navigation\models\MenuSettings`, project config) and **field values** (`verbb\navigation\elements\Menu`) use separate save paths; both are editable from the menu settings screen.

### Context API and menu breadcrumbs

```twig
{% set ctx = craft.navigation.context('mainMenu') %}
{% set trail = craft.navigation.menuBreadcrumbs('mainMenu') %}
```

### Read pipeline

v4 improves read performance without changing typical template output:

- **`withLinkedElements()`** — batch-loads linked Craft elements. **`node.element`** still works without it (lazy-loaded per node).
- **`withNodeHierarchy()`** — wires parent/child relationships in memory after the query. **`node.children`** and the `{% nav %}` tag still work without it (children load on demand). On menu-scoped front-end reads, hierarchy wiring is enabled automatically when you omit the flag; pass **`withNodeHierarchy(false)`** only if you want the old per-child query pattern.
- **Tree caching** — defaults to **`auto`** on front-end menu reads. Active/current state is always resolved after a cache hit, so highlight behaviour stays correct. Set **`cacheMode`** to **`off`** in **Settings → Navigation → Performance** only when debugging or when you need every read to hit the database.
- **`craft.navigation.tree()`** — returns nested arrays; linked element hydration is **opt-in** via `{ withLinkedElements: true }`. Use **`nodes()`** when you want element objects with the same opt-in defaults as queries.
- **Passive / group nodes** — use **`node.getTag()`** in custom templates (`span` vs `a`); the default `render()` helper respects this for accessibility.

See [Node Queries](/getting-elements/node-queries) and [Performance & Caching](/frontend/performance-and-caching).