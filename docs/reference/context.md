# Context

A `NavigationContext` describes the current page’s position in a menu. Obtain it in Twig with `craft.navigation.context('mainMenu')`, or pass node criteria as a second argument: `craft.navigation.context('mainMenu', { siteId: currentSite.id })`.

The current node is the deepest exact URL match and can be a stored Node or a ProjectedNode. See [Build a Section Sidebar](/user-guides/templating/build-a-section-sidebar-with-context-api) for a complete use case.

## Methods

::: reference
### `current()`

**Returns:** [Node](/reference/node)`|`[Projected Node](/reference/projected-node)`|null`

Returns the node for the current page, meaning the deepest node whose URL exactly matches the request URL. It’s `null` when no node matches.
:::

::: reference
### `currentNodes()`

**Returns:** `array`

Returns every node whose URL exactly matches the request URL, deepest first. A page can appear in a menu more than once, so this can hold several nodes.
:::

::: reference
### `activeNodes()`

**Returns:** `array`

Returns every node that is active for the current page, meaning nodes that match it exactly, contain it in their URL path or have a descendant that matches it.
:::

::: reference
### `parent()`

**Returns:** [Node](/reference/node)`|`[Projected Node](/reference/projected-node)`|null`

Returns the parent of the [current()](#current) node. It’s `null` when there’s no current node or the current node is at the top level.
:::

::: reference
### `ancestors()`

**Returns:** `array`

Returns the ancestors of the [current()](#current) node, from the top level down to its parent. Use it for breadcrumbs.
:::

::: reference
### `siblings()`

**Returns:** `array`

Returns the nodes that share the [current()](#current) node’s parent, including the current node itself. For a top-level node, this is every top-level node in the menu.
:::

::: reference
### `children()`

**Returns:** `array`

Returns the direct children of the [current()](#current) node.
:::

::: reference
### `branch()`

**Returns:** `array`

Returns the [ancestors()](#ancestors) followed by the [siblings()](#siblings), which is the usual set of links for a section sidebar.
:::


When no node exactly matches the request URL, `current()` and `parent()` return `null`, and the methods based on that current node return empty arrays. `activeNodes()` can still contain path-active nodes. Supplying criteria does not replace the request URL used for matching.
