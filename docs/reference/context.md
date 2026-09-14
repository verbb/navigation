# Context

A `NavigationContext` describes the current page’s position in a menu. Obtain it in Twig with `craft.navigation.context('mainMenu')`, or pass node criteria as a second argument: `craft.navigation.context('mainMenu', { siteId: currentSite.id })`.

The current node is the deepest exact URL match and can be a stored Node or a ProjectedNode. See [Build a Section Sidebar](/user-guides/templating/build-a-section-sidebar-with-context-api) for a complete use case.

## Methods

::: reference
### `current()`

**Returns:** Deepest exact-match node

Deepest exact-match node
:::

::: reference
### `currentNodes()`

**Returns:** All exact matches, deepest first

All exact matches, deepest first
:::

::: reference
### `activeNodes()`

**Returns:** All branch-active nodes

All branch-active nodes
:::

::: reference
### `parent()`

**Returns:** Parent of deepest current node

Parent of deepest current node
:::

::: reference
### `ancestors()`

**Returns:** Root → parent of current

Root → parent of current
:::

::: reference
### `siblings()`

**Returns:** Same-level nodes under shared parent

Same-level nodes under shared parent
:::

::: reference
### `children()`

**Returns:** Direct children of deepest current node

Direct children of deepest current node
:::

::: reference
### `branch()`

**Returns:** Ancestors + current + siblings (common sidebar set)

Ancestors + current + siblings (common sidebar set)
:::


When no node exactly matches the request URL, `current()` and `parent()` return `null`; methods based on that current node return empty arrays. `activeNodes()` can still contain path-active nodes. Supplying criteria does not replace the request URL used for matching.
