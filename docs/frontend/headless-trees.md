# Headless Trees

`craft.navigation.tree()` returns a nested **array** tree — useful for JSON APIs, JavaScript menus, and static site generators.

## Basic usage

```twig
{% set tree = craft.navigation.tree('mainMenu') %}
{{ tree|json_encode }}
```

You can pass criteria like a node query:

```twig
{% set tree = craft.navigation.tree({ handle: 'mainMenu', level: 1 }) %}
```

## Array shape

Each node array includes keys such as:

| Key | Description |
| --- | --- |
| `title`, `url`, `nodeUri` | Link data |
| `current`, `active`, `hasActiveChild` | Active flags (resolved for the current request) |
| `target` | Link target |
| `children` | Nested arrays |
| `element` | Linked element array when present — `null` by default; populated when `withLinkedElements` is enabled |

## Example output

A small `mainMenu` on `/products/widget` might serialise like this:

```json
[
  {
    "id": 101,
    "title": "Products",
    "url": "https://example.com/products",
    "nodeUri": "products",
    "level": 1,
    "active": true,
    "current": false,
    "hasActiveChild": true,
    "target": "",
    "element": null,
    "children": [
      {
        "id": 102,
        "title": "Widget",
        "url": "https://example.com/products/widget",
        "nodeUri": "products/widget",
        "level": 2,
        "active": true,
        "current": true,
        "hasActiveChild": false,
        "target": "",
        "element": null
      }
    ]
  },
  {
    "id": 103,
    "title": "About",
    "url": "https://example.com/about",
    "nodeUri": "about",
    "level": 1,
    "active": false,
    "current": false,
    "hasActiveChild": false,
    "target": "",
    "element": null
  }
]
```

Each node also includes Craft element keys from `toArray()` — `type`, `menuId`, custom fields, and so on. Omitted here for brevity.

With `withLinkedElements: true`, `element` holds the linked entry (or category, etc.) as an array instead of `null`. Projected Dynamic children include `"isProjected": true` and a slimmer key set — see [Projected Node](/reference/projected-node).

## Options

```twig
{% set tree = craft.navigation.tree('mainMenu', {
    withLinkedElements: true,
}) %}
```

Unlike `nodes()`, linked element hydration is **opt-in** for `tree()`. Without `withLinkedElements`, `element` is `null`.

`tree()` runs through the same read pipeline as `nodes().all()` — caching and active-state rules apply to the underlying fetch. See [Performance & Caching](/frontend/performance-and-caching).

## Dynamic projection

Projected Dynamic children appear in `children` with `isProjected: true` unless you opt out with `withProjectedChildren(false)` on the underlying query criteria.

For GraphQL headless access, see [Query Nodes](/graphql/query-nodes).
