# Active State & Context

Navigation resolves **current**, **active**, and **hasActiveChild** states for each node after the menu loads. Use these for highlighting, open dropdowns, and contextual sub-navigation.

Active state is always computed **after** cache hits and is **skipped** in the CP, console, and preview requests.

## States

| State | Method | Typical use |
| --- | --- | --- |
| Current | `node.getCurrent()` | `aria-current="page"`, primary highlight |
| Active | `node.getActive()` | Open branch / `.active` on parent |
| Has active child | `node.hasActiveChild()` | Open submenu, chevron icon |

**Current** is an exact URL match (including `urlSuffix`). **Active** is true when the node is current, when any descendant nav node is current, or when path-section matching applies. **Has active child** is true when a descendant **nav node** — not just a URL under the parent path — is current.

**Path-section matching** covers URLs that sit under a node’s path when no child nav item exists for that page. The `/news` node is active on `/news/article-slug` even if the article is not in the menu.

```twig
<li class="{{ node.getActive() ? 'is-active' }}{{ node.getCurrent() ? ' is-current' }}">
```

## Query helpers

```twig
{# Deepest exact-match (current page) node #}
{% set currentNode = craft.navigation.getActiveNode({ handle: 'mainMenu' }) %}

{# Branch fallback when no exact node exists for the URL #}
{% set sectionNode = craft.navigation.getActiveNode({ handle: 'mainMenu' }, true) %}

{# All exact matches, deepest first #}
{% set currentNodes = craft.navigation.getCurrentNodes({ handle: 'mainMenu' }) %}

{# All branch-active nodes #}
{% set activeNodes = craft.navigation.getActiveNodes({ handle: 'mainMenu' }) %}
```

### Default behaviour

`getActiveNode()` (default) returns the **deepest node whose URL exactly matches** the current page — not the first branch-active ancestor.

On `/products/widget` with nodes for `/products` and `/products/widget`, `getActiveNode()` returns **Widget**. Both **Products** (`getActive()`) and **Widget** (`getCurrent()`) are highlighted appropriately.

Pass **`true`** as the second argument when you need branch/path fallback for contextual sub-nav.

## Context API

For sidebars and section navigation, use **`craft.navigation.context()`** instead of hand-rolling parent/sibling queries:

```twig
{% set ctx = craft.navigation.context('mainMenu') %}

<ul>
    {% for node in ctx.siblings() %}
        <li class="{{ node.getCurrent() ? 'is-current' }}">
            <a href="{{ node.url }}">{{ node.title }}</a>
        </li>
    {% endfor %}
</ul>
```

| Method | Returns |
| --- | --- |
| `current()` | Deepest exact-match node |
| `currentNodes()` | All exact matches, deepest first |
| `activeNodes()` | All branch-active nodes |
| `parent()` | Parent of deepest current node |
| `ancestors()` | Root → parent of current |
| `siblings()` | Same-level nodes under shared parent |
| `children()` | Direct children of deepest current node |
| `branch()` | Ancestors + current + siblings (common sidebar set) |

See [Context & Breadcrumbs](/templates/context-and-breadcrumbs) for breadcrumb trails.

## Dynamic children

Projected entries from Dynamic nodes participate in active resolution. `getActiveNode()` and `getCurrentNodes()` can return a [ProjectedNode](/reference/projected-node) when the current URL matches a projected entry.

## Override active state

Branch active state can be modified via `Node::EVENT_NODE_ACTIVE` when `getActive()` runs. See [Events](/developers/events).
