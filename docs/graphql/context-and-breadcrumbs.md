# Context & Breadcrumbs

These queries return menu context for the URL Craft is handling. First configure [schema access](/graphql/query-nodes#configure-schema-access).

A request to a GraphQL endpoint usually has a different URL from the page your visitor is viewing. These helpers do not infer that page from the referring URL, so current-node results and breadcrumbs may be empty. For a separate frontend, query the menu tree and match its links to the browser URL as shown in [Expose a Menu as JSON](/user-guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend#highlight-the-current-page).

## `navigationContext`

Mirrors `craft.navigation.context(handle)`:

```graphql
{
  navigationContext(menuHandle: "mainMenu") {
    current { title url }
    parent { title url }
    siblings { title url }
    ancestors { title url }
    children { title url }
    branch { title url }
    currentNodes { title url }
    activeNodes { title url }
  }
}
```

Pass `menuHandle` to choose the menu. This query does not accept `site`, `siteId`, or a frontend URL argument. It uses Craft’s current request context.

## `navigationMenuBreadcrumbs`

Mirrors `craft.navigation.menuBreadcrumbs(handle)`:

```graphql
{
  navigationMenuBreadcrumbs(menuHandle: "mainMenu") {
    title
    url
    current
  }
}
```

Returns a trail from the menu root to the deepest current node, including projected Dynamic matches when applicable.

## Twig Reference

See [Context & Breadcrumbs](/template-guides/context-and-breadcrumbs) for URL-segment vs menu-tree semantics and when to use each resolver.
