# Node

A Node object represents a single item in a navigation menu tree — a link, a linked Craft element, or a structural item such as a group or Dynamic node. When you work with nodes in Twig or PHP, you are usually dealing with a `verbb\navigation\elements\Node` object.

## Properties

| Property | Description |
| --- | --- |
| `id` | Node element ID |
| `elementId` | Linked Craft element ID (element-backed nodes) |
| `element` | Linked Craft element — lazy-loaded; use `withLinkedElements()` on the query to batch-load |
| `menuId` | Parent menu ID |
| `menu` | Parent [Menu](/reference/menu) element — use `withMenu()` on the query to batch-load |
| `url` | Full URL (linked element or custom) |
| `nodeUri` | URI portion relative to the site |
| `title` | Node title |
| `type` | Node type class name (e.g. `verbb\navigation\nodetypes\Entry`) |
| `classes` | CSS classes string |
| `customAttributes` | Table of `{ attribute, value }` rows |
| `urlSuffix` | Per-site suffix (anchor path, query string) |
| `newWindow` | Open in new window |
| `target` | `_blank` or empty string |
| `status` / `enabled` / `enabledForSite` | Element status |
| `children` | Child nodes — wired in memory when hierarchy is enabled |
| `level` | Structure level |
| `siteId` | Node site variant |

Node custom fields (on the menu’s node field layout) are available by handle like any Craft element.

## Active state

| Method | Description |
| --- | --- |
| `getCurrent()` | Exact URL match |
| `getActive()` | Current, path-section, or has active descendant |
| `hasActiveChild()` | Descendant nav node is current (`bool`) |
| `getActiveState()` | Underlying state object |

See [Active State & Context](/templates/active-state-and-context).

## Methods

| Method | Description |
| --- | --- |
| `getTag()` | HTML tag for this node type (`a`, `span`, …) |
| `getTypeLabel()` | Display name for the node type |
| `getLink()` | Full HTML anchor (or appropriate tag) with title |
| `getLinkAttributes($extra)` | Rendered attribute string for custom markup |
| `isElement()` | Element-backed node |
| `isCustom()` | Custom URL node |
| `isPassive()` | Passive (non-link) node |
| `isSite()` | Site node |
| `isGroupColumn()` | Group/column structural node |

## `linkAttributes`

```twig
<a {{ node.linkAttributes({
    class: 'nav-link',
}) }}>{{ node.title }}</a>
```

Merges node classes, target, rel, and custom attributes with any extras you pass.

## `customAttributes`

```twig
<a {% for attribute in node.customAttributes %}
    {{ attribute.attribute }}="{{ attribute.value }}"
{% endfor %}>{{ node.title }}</a>
```

## Linked element fields

```twig
{% if node.element %}
    {{ node.element.someFieldHandle }}
{% endif %}
```

Use `withLinkedElements()` on the parent query to avoid N+1 loads.
