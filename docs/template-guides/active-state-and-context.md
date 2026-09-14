# Active State & Context

Highlight the page a visitor is viewing and its menu branch using each node’s current and active states. For example, on a product page you can mark the product link as current while keeping the Products section highlighted. The examples use a saved `mainMenu` and belong in the Twig partial that displays it.

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

## Find the Current Page

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

### Default Behaviour

`getActiveNode()` (default) returns the **deepest node whose URL exactly matches** the current page.

On `/products/widget` with nodes for `/products` and `/products/widget`, `getActiveNode()` returns **Widget**. Both **Products** (`getActive()`) and **Widget** (`getCurrent()`) are highlighted appropriately.

Pass **`true`** as the second argument when you need branch/path fallback for contextual sub-nav.

## Context API

For sidebars and section navigation, use **`craft.navigation.context()`** to find related pages without separate parent and sibling queries:

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

Use `siblings()` for pages beside the current page, or `children()` when the current page is a section root. `branch()` combines the current node, its ancestors, and siblings. The [Context reference](/reference/context) lists all methods and empty-match behaviour.

See [Context & Breadcrumbs](/template-guides/context-and-breadcrumbs) for breadcrumb trails.

## Dynamic Children

Projected entries from Dynamic nodes participate in active resolution. `getActiveNode()` and `getCurrentNodes()` can return a [ProjectedNode](/reference/projected-node) when the current URL matches a projected entry.

## Override Active State

Branch active state can be modified via `Node::EVENT_NODE_ACTIVE` when `getActive()` runs. See [Events](/developers/events).
