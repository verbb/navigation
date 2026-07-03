# Context & Breadcrumbs

GraphQL equivalents for the Twig context and breadcrumb helpers.

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

Pass the same `menuHandle` / site arguments you would use for `navigationNodes`.

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

## Twig reference

See [Context & Breadcrumbs](/templates/context-and-breadcrumbs) for URL-segment vs menu-tree semantics and when to use each resolver.
