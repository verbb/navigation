# Query Nodes

Query a menu’s links with GraphQL when your frontend needs to select the fields it receives. Start with a saved menu called **Main Menu**, handle `mainMenu`, enabled for the site you want to query.

## Configure Schema Access

In Craft’s **GraphQL → Schemas**, edit the schema your client will use. Under **Navigation**, enable **View menu - Main Menu**, then save. Use **View all menus** only when that schema should read every menu. Menu access is separate from access to linked entry or asset data.

Follow [Craft’s GraphQL setup](https://craftcms.com/docs/5.x/development/graphql.html) to create an endpoint and choose a public schema or a token for a private schema. Test using that same schema in GraphiQL; the administrator’s full schema can hide missing permissions. Keep private tokens in server-side code.

If you request a node’s linked `element`, the schema must also permit its specific section, category group, volume, or product type. A menu grant alone does not grant access to that content.

Run the example below in GraphiQL with your intended schema. You should receive Main Menu’s root links and their direct children. If `navigationNodes` is unavailable, check the schema’s Navigation grants. If results are empty, check the saved handle, site, and enabled state.

## Query a Menu

```graphql
{
  navigationNodes(menuHandle: "mainMenu", level: 1) {
    title
    url
    children {
      title
      url
    }
  }
}
```

Use the singular `navigationNode` query to fetch one node by ID or UID.

## Refine the Query

Use `menuHandle` to select the menu and `level: 1` to start at its roots. For a secondary site, add its site handle, such as `site: "french"`, to the query arguments. In addition to [standard Craft element query arguments](https://craftcms.com/docs/5.x/development/graphql.html#element-query-arguments), node queries support:

| Argument | Description |
| --- | --- |
| `menuHandle` | Menu handle |
| `menuId` | Menu ID |
| `type` | Node type class name(s) |
| `withLinkedElements` | Batch-load linked Craft elements (disables tree cache) |
| `withNodeHierarchy` | Wire parent/child in memory (`null` = auto on front-end) |
| `withMenu` | Batch-load parent Menu elements (disables tree cache) |
| `withProjectedChildren` | Include Dynamic projections; `false` to skip |

## Site and Language

- **`site` / `siteId`** select which **node variant** to return (per-site titles, URLs, enabled state).
- For element-backed nodes, **`linkedElementSiteId`** (per node-site row) selects which locale of the linked element supplies the URL — independent of the node's `siteId`.

In multisite setups, pass explicit **`site`** / **`siteId`** when the requested site is not the primary site. The **`language`** field on each node reflects the site's language, not a separate filter dimension.

Headless consumers should use the same site context as entry queries so URLs and enabled state match the front-end.

## Menu Element Type

Each menu with a field layout can expose a `{handle}_Menu` GraphQL type for menu-level custom fields.

## Projected Nodes

Dynamic children return as `ProjectedNavigationNode` with `isProjected: true`. See [Projected Node](/reference/projected-node).

## Returned Nodes

### The `NodeInterface` Interface

Stored and projected nodes implement `NodeInterface`. Alongside inherited Craft element and structure fields such as `id`, `title`, `siteId`, and `level`, the interface exposes the following Navigation fields. A `!` marks a non-null value.

| Field | Type | Description |
| --- | --- | --- |
| `isProjected` | `Boolean!` | Whether the node is generated at read time rather than stored in the menu structure. |
| `elementId` | `Int` | The linked element’s ID. |
| `menuId` | `Int` | The owning menu’s ID. |
| `menuHandle` | `String` | The owning menu’s handle. |
| `menuName` | `String` | The owning menu’s title. |
| `type` | `String` | The node type’s PHP class name. |
| `typeLabel` | `String` | The display name of the node type. |
| `classes` | `String` | Additional CSS classes. |
| `urlSuffix` | `String` | The configured URL suffix. |
| `customAttributes` | `[NodeCustomAttribute]` | Additional attributes, each with `attribute: String` and `value: String`. |
| `data` | `String` | Additional stored node data encoded as JSON; `null` for projected nodes. |
| `newWindow` | `String` | The open-in-new-window value, serialised as a GraphQL string. |
| `url` | `String` | The node’s full URL. |
| `nodeUri` | `String` | The node’s URI. |
| `children` | `[NodeInterface]` | The node’s children, including Dynamic projections when enabled. |
| `parent` | `NodeInterface` | The parent node, or null for a root node. |
| `element` | `ElementInterface` | The linked element, subject to the active schema’s content permissions. |

Projected nodes return null or empty values for settings they do not store, such as `classes`, `urlSuffix`, and `customAttributes`. Use `__typename` to distinguish their `ProjectedNavigationNode` type. Custom node fields belong on the concrete type generated for the menu; inspect the active schema in GraphiQL for its exact fields.

The [Node reference](/reference/node) describes the PHP and Twig object. Its methods and properties are not automatically exposed as GraphQL fields.

## Current-Page Information

Current-page helpers are available through `navigationContext`; `NodeInterface` does not expose `current`, `active`, or `hasActiveChild` fields. A GraphQL request does not automatically inherit the URL open in your frontend. For browser-side matching, follow [Expose a Menu as JSON](/user-guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend#highlight-the-current-page).

To identify the current menu branch or build a breadcrumb trail in your frontend, use the queries in [Context & Breadcrumbs](/graphql/context-and-breadcrumbs).
