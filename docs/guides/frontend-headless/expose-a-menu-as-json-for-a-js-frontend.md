# Expose a menu as JSON for a JS frontend

Decoupled front ends need menu data as JSON — titles, URLs, nested children, and active flags for the current request. Navigation's `craft.navigation.tree()` returns a nested array ready to encode.

For GraphQL clients, see [Query Nodes](/graphql/query-nodes) instead.

## Basic JSON endpoint

Create a Craft template routed at `/api/navigation/main-menu` (or use an action/controller in a module):

```twig
{# templates/api/navigation-main-menu.twig #}
{% set tree = craft.navigation.tree('mainMenu') %}
{% header 'Content-Type: application/json' %}
{{ tree|json_encode|raw }}
```

`tree()` runs the same read pipeline as `nodes().all()` — hierarchy wiring and caching apply. Active flags (`current`, `active`, `hasActiveChild`) reflect **the request that hits this URL**, not an arbitrary page.

## Shape of the response

See [Headless Trees — Example output](/frontend/headless-trees#example-output) for a sample payload. Each node includes link data, active flags, optional `children`, and `element: null` unless you opt in.

## Include linked element data

When the JS app needs entry fields (thumbnail URL, summary):

```twig
{% set tree = craft.navigation.tree('mainMenu', {
    withLinkedElements: true,
}) %}
{{ tree|json_encode|raw }}
```

Linked element hydration is opt-in for `tree()` and disables tree caching for that read. See [Performance & Caching](/frontend/performance-and-caching).

## Top-level items only

Limit depth when the client builds sub-menus lazily:

```twig
{% set tree = craft.navigation.tree({ handle: 'mainMenu', level: 1 }) %}
```

Children of returned nodes are still nested under each root unless you trim in JS.

## Dynamic projected children

Projected entries from **Dynamic** nodes appear in `children` with `"isProjected": true`. Skip them when needed:

```twig
{% set tree = craft.navigation.tree({ handle: 'mainMenu', withProjectedChildren: false }) %}
```

## Active state from the client

Two common patterns:

| Pattern | Approach |
| --- | --- |
| **Server-rendered shell** | The JSON endpoint is requested from the same Craft site; active flags match the current page automatically. |
| **Separate JS app** | Pass the current path as a query param and resolve active state in the client, **or** fetch menu JSON from a Craft route that receives the front-end URL as context. |

Navigation resolves active flags server-side during the request. A static JSON file exported at build time will not update highlights per route unless the client computes them.

## CORS and caching

- Set appropriate `Access-Control-*` headers if the menu URL is called from another origin.
- HTTP cache headers on the JSON route are independent of Navigation's tree cache — tune both deliberately.
- When using full-page cache plugins, see [Navigation with Blitz](/guides/frontend-headless/navigation-with-blitz-and-full-page-cache).

## Related

- [Headless Trees](/frontend/headless-trees)
- [The complete guide to Navigation performance](/guides/performance/the-complete-guide-to-navigation-performance)
- [Query Nodes](/graphql/query-nodes)
