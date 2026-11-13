# Node

A Node object represents a single item in a navigation menu tree, such as a link, a linked Craft element, or a structural item such as a group or Dynamic node. When you work with nodes in Twig or PHP, you are usually dealing with a `verbb\navigation\elements\Node` object.

## Properties

::: reference
### `id`

**Type:** `int|null`

The ID of this node.
:::

::: reference
### `title`

**Type:** `string|null`

The node’s title, as entered in the menu builder. For a node linked to an element, this is the node’s own title, which can differ from the element’s title.
:::

::: reference
### `url`

**Type:** `string|null`

The node’s full URL, including any [urlSuffix](#urlsuffix). For a linked element this is the element’s URL on the node’s linked site, and for a custom node it’s the URL entered in the menu builder. It’s `null` for nodes without a destination, such as passive nodes and groups.
:::

::: reference
### `nodeUri`

**Type:** `string`

The node’s URL with the current site’s base URL removed, such as `news/my-article`. It’s an empty string when the node has no URL. A URL on another domain is returned in full.
:::

::: reference
### `urlSuffix`

**Type:** `string|null`

The text added to the end of the node’s URL, such as `#contact` or `?ref=nav`. Editors set it per site in the menu builder.
:::

::: reference
### `type`

**Type:** `string|null`

The class name of the node’s type, such as `verbb\navigation\nodetypes\Entry`. Use [isElement()](#iselement), [isCustom()](#iscustom) and the other type checks below rather than comparing class names in templates.
:::

::: reference
### `elementId`

**Type:** `int|null`

The ID of the Craft element this node links to. It’s `null` for nodes that don’t link to an element, such as custom URLs and passive nodes.
:::

::: reference
### `element`

**Type:** `craft\base\ElementInterface|null`

The Craft element this node links to, such as an entry or category. Linked elements from the same query result load together the first time you read one, and `withLinkedElements()` on the query loads them before it returns. It’s `null` for nodes that don’t link to an element, or when the element no longer exists or isn’t available on the node’s site.
:::

::: reference
### `menuId`

**Type:** `int|null`

The ID of the menu this node belongs to.
:::

::: reference
### `menu`

**Type:** [Menu](/reference/menu)`|null`

The [Menu](/reference/menu) element this node belongs to, which holds the menu’s own custom field values. Use `withMenu()` on the query to load menus for all nodes at once when you read this in a loop.
:::

::: reference
### `classes`

**Type:** `string|null`

The CSS classes editors entered for this node, as one space-separated string. [linkAttributes](#linkattributes) adds them to the rendered link for you.
:::

::: reference
### `customAttributes`

**Type:** `array`

The extra HTML attributes editors entered for this node, as a list of rows with `attribute` and `value` keys. Render them with [linkAttributes](#linkattributes), as described below.
:::

::: reference
### `newWindow`

**Type:** `bool`

Whether this node’s link opens in a new window or tab.
:::

::: reference
### `target`

**Type:** `string`

The value for the link’s `target` attribute, which is `_blank` when [newWindow](#newwindow) is on and an empty string otherwise.
:::

::: reference
### `status`

**Type:** `string|null`

The node’s status, which is `enabled` or `disabled` on the front end. In the control panel’s menu builder, a node with unsaved builder changes can also be `pending-add`, `pending-edit` or `pending-delete`.
:::

::: reference
### `enabled`

**Type:** `bool`

Whether this node is enabled globally. Node queries only return enabled nodes unless you change the [status](/getting-elements/node-queries#status) parameter.
:::

::: reference
### `enabledForSite`

**Type:** `bool`

Whether this node is enabled for the site it was fetched for. A node can be enabled globally but turned off for one site.
:::

::: reference
### `children`

**Type:** `craft\elements\db\ElementQueryInterface|craft\elements\ElementCollection`

The node’s direct children. When the query has already loaded the menu’s hierarchy, this is a collection of the loaded nodes, including any [Projected Node](/reference/projected-node) items from a Dynamic node. Otherwise, it’s a new query for the children.
:::

::: reference
### `level`

**Type:** `int|null`

The node’s level in the menu structure. Top-level nodes are at level 1.
:::

::: reference
### `siteId`

**Type:** `int|null`

The ID of the site this copy of the node belongs to. Each site has its own copy of a node, with its own title, URL and status.
:::


Node custom fields (on the menu’s node field layout) are available by handle like any Craft element.

## Active State

Front-end node queries work out these values for every node in the result at once, comparing each node’s URL to the current request URL.

::: reference
### `getCurrent()`

**Returns:** `bool`

Returns whether this node’s URL exactly matches the current page’s URL. Read it as `node.current` in Twig.
:::

::: reference
### `getActive()`

**Returns:** `bool|null`

**Arguments:** `includeChildren` (`bool`, default `true`)

Returns whether this node should be highlighted for the current page. A node is active when it’s the current page, when the current URL is inside the node’s URL path, or when one of its descendants is the current page. Pass `false` to check for an exact match only. Read it as `node.active` in Twig. Listeners to the [modifyNodeActive](/developers/events#the-modifynodeactive-event) event can change the result.
:::

::: reference
### `hasActiveChild()`

**Returns:** `bool`

Returns whether one of this node’s descendants is the current page. It’s only worked out for nodes returned by a front-end node query, and returns `false` otherwise.
:::

::: reference
### `getActiveState()`

**Returns:** `verbb\navigation\models\NodeActiveState`

Returns an object holding this node’s `isCurrent`, `isActive` and `hasActiveChild` values together. Most templates use `node.current`, `node.active` and `node.hasActiveChild()` instead.
:::


See [Active State & Context](/template-guides/active-state-context).

## Methods

::: reference
### `getTag()`

**Returns:** `string`

Returns the HTML tag this node renders with, such as `a` for links or `span` for passive nodes and groups.
:::

::: reference
### `getTypeLabel()`

**Returns:** `string`

Returns the display name of the node’s type, such as “Entry”, “Custom URL” or “Passive”.
:::

::: reference
### `getLink()`

**Returns:** `Twig\Markup|null`

**Arguments:** `attributes` (`array|null`, default `null`), extra HTML attributes to add to the tag

Returns the complete HTML element for this node, using [getTag()](#gettag), [linkAttributes](#linkattributes) and the escaped title. You don’t need Twig’s `raw` filter.

```twig
{{ node.getLink({class: 'nav-link'}) }}
```
:::

::: reference
### `getLinkAttributes()`

**Returns:** `Twig\Markup`

**Arguments:** `extraAttributes` (`array|null`, default `null`), extra HTML attributes to merge in

Returns the node’s HTML attributes as a ready-to-render string, for when you write the tag yourself. It includes `href`, `target`, `rel`, the node’s classes and its custom attributes. Read it as `node.linkAttributes` in Twig, as shown in [linkAttributes](#linkattributes).
:::

::: reference
### `isElement()`

**Returns:** `bool`

Returns whether this node links to a Craft element, such as an entry, category, asset or product.
:::

::: reference
### `isCustom()`

**Returns:** `bool`

Returns whether this node links to a custom URL entered by an editor.
:::

::: reference
### `isPassive()`

**Returns:** `bool`

Returns whether this is a passive node, which shows a title without a link.
:::

::: reference
### `isSite()`

**Returns:** `bool`

Returns whether this node links to the home page of one of your Craft sites.
:::

::: reference
### `isGroupColumn()`

**Returns:** `bool`

Returns whether this is a Group / Column node, which shows a title without a link and groups its children, for example into a column of a mega menu.
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

Linked elements from the query result load together on first access. Use `withLinkedElements()` on the parent query to load them immediately. Relational fields on those elements may still need their own eager-loading strategy.
