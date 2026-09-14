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
    current
    active
    hasActiveChild
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
| `withNavigationCache` | Opt in when cache mode is **Manual** |
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

Node results implement `NodeInterface` with fields including `menuId`, `menuHandle`, `menuName`, `elementId`, `url`, `urlSuffix`, `nodeUri`, `type`, `classes`, `customAttributes`, `newWindow`, `children`, `parent`, and `element` (when hydrated).

For full Twig parity, see [Node](/reference/node) and [Node Queries](/getting-elements/node-queries).

## Current-Page Information

The `current`, `active`, and `hasActiveChild` fields use the URL Craft is handling. A GraphQL request does not automatically inherit the URL open in your frontend. For browser-side matching, follow [Expose a Menu as JSON](/user-guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend#highlight-the-current-page).

To identify the current menu branch or build a breadcrumb trail in your frontend, use the queries in [Context & Breadcrumbs](/graphql/context-and-breadcrumbs).
