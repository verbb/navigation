# Node

A Node object represents a single item in a navigation menu tree — a link, a linked Craft element, or a structural item such as a group or Dynamic node. When you work with nodes in Twig or PHP, you are usually dealing with a `verbb\navigation\elements\Node` object.

## Properties

::: reference
### `id`

**Type:** `int|null`

Node element ID
:::

::: reference
### `elementId`

**Type:** `int|null`

Linked Craft element ID (element-backed nodes)
:::

::: reference
### `element`

**Type:** `craft\base\ElementInterface|null`

Linked Craft element — lazy-loaded; use `withLinkedElements()` on the query to batch-load
:::

::: reference
### `menuId`

**Type:** `int|null`

Parent menu ID
:::

::: reference
### `menu`

**Type:** `verbb\navigation\elements\Menu|null`

Parent [Menu](/reference/menu) element — use `withMenu()` on the query to batch-load
:::

::: reference
### `url`

**Type:** `string|null`

Full URL (linked element or custom)
:::

::: reference
### `nodeUri`

**Type:** `string`

URI portion relative to the site
:::

::: reference
### `title`

**Type:** `string|null`

Node title
:::

::: reference
### `type`

**Type:** `string|null`

Node type class name (e.g. `verbb\navigation\nodetypes\Entry`)
:::

::: reference
### `classes`

**Type:** `string|null`

CSS classes string
:::

::: reference
### `customAttributes`

**Type:** `array`

Table of `{ attribute, value }` rows
:::

::: reference
### `urlSuffix`

**Type:** `string|null`

Per-site suffix (anchor path, query string)
:::

::: reference
### `newWindow`

**Type:** `bool`

Open in new window
:::

::: reference
### `target`

**Type:** `string`

`_blank` or empty string
:::

::: reference
### `status` / `enabled` / `enabledForSite`

Element status
:::

::: reference
### `children`

**Type:** `craft\elements\db\ElementQueryInterface|craft\elements\ElementCollection`

Child nodes — wired in memory when hierarchy is enabled
:::

::: reference
### `level`

**Type:** `int|null`

Structure level
:::

::: reference
### `siteId`

**Type:** `int|null`

Node site variant
:::


Node custom fields (on the menu’s node field layout) are available by handle like any Craft element.

## Active State

::: reference
### `getCurrent()`

**Returns:** `bool`

Exact URL match
:::

::: reference
### `getActive()`

**Returns:** `bool|null`

Current, path-section, or has active descendant
:::

::: reference
### `hasActiveChild()`

**Returns:** `bool`

Descendant nav node is current (`bool`)
:::

::: reference
### `getActiveState()`

**Returns:** `verbb\navigation\models\NodeActiveState`

Underlying state object
:::


See [Active State & Context](/template-guides/active-state-and-context).

## Methods

::: reference
### `getTag()`

**Returns:** `string`

HTML tag for this node type (`a`, `span`, …)
:::

::: reference
### `getTypeLabel()`

Display name for the node type
:::

::: reference
### `getLink()`

**Returns:** `Twig\Markup|null`

Full HTML anchor (or appropriate tag) with title
:::

::: reference
### `getLinkAttributes($extra)`

**Returns:** `Twig\Markup`

Rendered attribute string for custom markup
:::

::: reference
### `isElement()`

**Returns:** `bool`

Element-backed node
:::

::: reference
### `isCustom()`

**Returns:** `bool`

Custom URL node
:::

::: reference
### `isPassive()`

**Returns:** `bool`

Passive (non-link) node
:::

::: reference
### `isSite()`

**Returns:** `bool`

Site node
:::

::: reference
### `isGroupColumn()`

**Returns:** `bool`

Group/column structural node
:::


## `linkAttributes`

```twig
<a {{ node.linkAttributes({
    class: 'nav-link',
}) }}>{{ node.title }}</a>
```

Merges node classes, target, rel, and custom attributes with any extras you pass.

## `customAttributes`

This property contains the authored `{ attribute, value }` rows. Use [linkAttributes](#linkattributes) when rendering them into HTML so Navigation evaluates the values and applies its supported attribute rules.

## Linked Element Fields

```twig
{% if node.element %}
    {{ node.element.someFieldHandle }}
{% endif %}
```

Use `withLinkedElements()` on the parent query to avoid N+1 loads.
