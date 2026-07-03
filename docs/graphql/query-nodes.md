# Query Nodes

Navigation exposes nodes via GraphQL for headless front-ends. Be sure to read [Craft's GraphQL support](https://craftcms.com/docs/5.x/graphql.html) for general schema and token setup.

## Example

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

## Navigation-specific arguments

In addition to [standard Craft element query arguments](https://craftcms.com/docs/5.x/development/graphql.html#element-query-arguments), node queries support:

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

## Site and language

- **`site` / `siteId`** select which **node variant** to return (per-site titles, URLs, enabled state).
- For element-backed nodes, **`linkedElementSiteId`** (per node-site row) selects which locale of the linked element supplies the URL — independent of the node's `siteId`.

In multisite setups, pass explicit **`site`** / **`siteId`** when the requested site is not the primary site. The **`language`** field on each node reflects the site's language, not a separate filter dimension.

Headless consumers should use the same site context as entry queries so URLs and enabled state match the front-end.

## Menu element type

Each menu with a field layout can expose a `{handle}_Menu` GraphQL type for menu-level custom fields.

## Projected nodes

Dynamic children return as `ProjectedNavigationNode` with `isProjected: true`. See [Projected Node](/reference/projected-node).

## Node interface

Node results implement `NodeInterface` with fields including `menuId`, `menuHandle`, `menuName`, `elementId`, `url`, `urlSuffix`, `nodeUri`, `type`, `classes`, `customAttributes`, `newWindow`, `children`, `parent`, and `element` (when hydrated).

For full Twig parity, see [Node](/reference/node) and [Node Queries](/getting-elements/node-queries).

## Related

- [Context & Breadcrumbs](/graphql/context-and-breadcrumbs)
