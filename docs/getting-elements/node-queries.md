# Node Queries

You can fetch nodes in your templates or PHP code using **node queries**.

::: code
```twig Twig
{# Create a new node query #}
{% set myQuery = craft.navigation.nodes() %}
```

```php PHP
// Create a new node query
$myQuery = \verbb\navigation\elements\Node::find();
```
:::

Once you’ve created a node query, you can set parameters on it to narrow down the results, and then execute it by calling `.all()`. An array of [Node](/reference/node) objects will be returned.

Most node queries are scoped to one menu with [handle](#handle). You can also pass a menu handle straight to `craft.navigation.nodes('mainMenu')` as a shortcut for `craft.navigation.nodes().handle('mainMenu')`.

::: tip
See [Element Queries](https://craftcms.com/docs/5.x/development/element-queries.html) in the Craft docs to learn about how element queries work. For caching and loading options, see [Performance & Caching](/frontend/performance-caching).
:::

## Example

We can display the top-level links of a menu by doing the following.

1. Create a node query with `craft.navigation.nodes()`.
2. Set the [handle](#handle), [level](#level) and [limit](#limit) parameters on it.
3. Fetch the nodes with `.all()`.
4. Loop through the nodes using a [for](https://twig.symfony.com/doc/3.x/tags/for.html) tag to output their links.

```twig
{# Create a node query with the 'handle', 'level' and 'limit' parameters #}
{% set nodesQuery = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .limit(10) %}

{# Fetch the nodes #}
{% set nodes = nodesQuery.all() %}

{# Display their links #}
{% for node in nodes %}
    {{ node.link }}
{% endfor %}
```

`node.link` renders each node's title inside a link to its URL, with the node's classes, attributes and new-window setting applied. To build the markup yourself, or to render a full nested menu, see [Rendering Nodes](/template-guides/rendering-nodes).

## Parameters

Node queries support the following parameters.

::: reference
### `ancestorDist`

Narrows the query results to only nodes that are up to a certain distance away from the node specified by [ancestorOf](#ancestorof).

::: code
```twig Twig
{# Fetch nodes above this one #}
{% set nodes = craft.navigation.nodes()
    .ancestorOf(node)
    .ancestorDist(3)
    .all() %}
```

```php PHP
// Fetch nodes above this one
$nodes = \verbb\navigation\elements\Node::find()
    ->ancestorOf($node)
    ->ancestorDist(3)
    ->all();
```
:::
:::

::: reference
### `ancestorOf`

Narrows the query results to only nodes that are ancestors of another node.

| Value | Fetches nodes… |
| --- | --- |
| `1` | above the node with an ID of 1. |
| a [Node](/reference/node) object | above the node represented by the object. |

::: code
```twig Twig
{# Fetch nodes above this one #}
{% set nodes = craft.navigation.nodes()
    .ancestorOf(node)
    .all() %}
```

```php PHP
// Fetch nodes above this one
$nodes = \verbb\navigation\elements\Node::find()
    ->ancestorOf($node)
    ->all();
```
:::

::: tip
This can be combined with [ancestorDist](#ancestordist) if you want to limit how far away the ancestor nodes can be.
:::
:::

::: reference
### `asArray`

Causes the query to return matching nodes as arrays of data, rather than [Node](/reference/node) objects.

::: code
```twig Twig
{# Fetch nodes as arrays #}
{% set nodes = craft.navigation.nodes()
    .asArray()
    .all() %}
```

```php PHP
// Fetch nodes as arrays
$nodes = \verbb\navigation\elements\Node::find()
    ->asArray()
    ->all();
```
:::
:::

::: reference
### `dateCreated`

Narrows the query results based on the nodes’ creation dates.

| Value | Fetches nodes… |
| --- | --- |
| `'>= 2018-04-01'` | that were created on or after 2018-04-01. |
| `'< 2018-05-01'` | that were created before 2018-05-01 |
| `['and', '>= 2018-04-01', '< 2018-05-01']` | that were created between 2018-04-01 and 2018-05-01. |

::: code
```twig Twig
{# Fetch nodes created last month #}
{% set start = date('first day of last month midnight') | atom %}
{% set end = date('first day of this month midnight') | atom %}

{% set nodes = craft.navigation.nodes()
    .dateCreated(['and', ">= #{start}", "< #{end}"])
    .all() %}
```

```php PHP
// Fetch nodes created last month
$start = (new \DateTime('first day of last month midnight'))->format(\DateTime::ATOM);
$end = (new \DateTime('first day of this month midnight'))->format(\DateTime::ATOM);

$nodes = \verbb\navigation\elements\Node::find()
    ->dateCreated(['and', ">= {$start}", "< {$end}"])
    ->all();
```
:::
:::

::: reference
### `dateUpdated`

Narrows the query results based on the nodes’ last-updated dates.

| Value | Fetches nodes… |
| --- | --- |
| `'>= 2018-04-01'` | that were updated on or after 2018-04-01. |
| `'< 2018-05-01'` | that were updated before 2018-05-01 |
| `['and', '>= 2018-04-01', '< 2018-05-01']` | that were updated between 2018-04-01 and 2018-05-01. |

::: code
```twig Twig
{# Fetch nodes updated in the last week #}
{% set lastWeek = date('1 week ago')|atom %}

{% set nodes = craft.navigation.nodes()
    .dateUpdated(">= #{lastWeek}")
    .all() %}
```

```php PHP
// Fetch nodes updated in the last week
$lastWeek = (new \DateTime('1 week ago'))->format(\DateTime::ATOM);

$nodes = \verbb\navigation\elements\Node::find()
    ->dateUpdated(">= {$lastWeek}")
    ->all();
```
:::
:::

::: reference
### `descendantDist`

Narrows the query results to only nodes that are up to a certain distance away from the node specified by [descendantOf](#descendantof).

::: code
```twig Twig
{# Fetch nodes below this one #}
{% set nodes = craft.navigation.nodes()
    .descendantOf(node)
    .descendantDist(3)
    .all() %}
```

```php PHP
// Fetch nodes below this one
$nodes = \verbb\navigation\elements\Node::find()
    ->descendantOf($node)
    ->descendantDist(3)
    ->all();
```
:::
:::

::: reference
### `descendantOf`

Narrows the query results to only nodes that are descendants of another node.

| Value | Fetches nodes… |
| --- | --- |
| `1` | below the node with an ID of 1. |
| a [Node](/reference/node) object | below the node represented by the object. |

::: code
```twig Twig
{# Fetch nodes below this one #}
{% set nodes = craft.navigation.nodes()
    .descendantOf(node)
    .all() %}
```

```php PHP
// Fetch nodes below this one
$nodes = \verbb\navigation\elements\Node::find()
    ->descendantOf($node)
    ->all();
```
:::

::: tip
This can be combined with [descendantDist](#descendantdist) if you want to limit how far away the descendant nodes can be.
:::
:::

::: reference
### `elementId`

Narrows the query results to nodes linked to the element with the given ID, such as every node that links to a particular entry. It's useful for finding where an entry appears in your menus.

| Value | Fetches nodes… |
| --- | --- |
| `1` | linked to the element with an ID of 1. |
| `[1, 2]` | linked to the element with an ID of 1 or 2. |

::: code
```twig Twig
{# Fetch the nodes that link to this entry #}
{% set nodes = craft.navigation.nodes()
    .elementId(entry.id)
    .all() %}
```

```php PHP
// Fetch the nodes that link to this entry
$nodes = \verbb\navigation\elements\Node::find()
    ->elementId($entry->id)
    ->all();
```
:::
:::

::: reference
### `fixedOrder`

Causes the query results to be returned in the order specified by [id](#id).

::: code
```twig Twig
{# Fetch nodes in a specific order #}
{% set nodes = craft.navigation.nodes()
    .id([1, 2, 3, 4, 5])
    .fixedOrder()
    .all() %}
```

```php PHP
// Fetch nodes in a specific order
$nodes = \verbb\navigation\elements\Node::find()
    ->id([1, 2, 3, 4, 5])
    ->fixedOrder()
    ->all();
```
:::
:::

::: reference
### `handle`

Narrows the query results to nodes in the menu with the given handle. This is the usual way to scope a node query to one menu. `menuHandle()` does the same thing.

::: code
```twig Twig
{# Fetch nodes for the main menu #}
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .all() %}
```

```php PHP
// Fetch nodes for the main menu
$nodes = \verbb\navigation\elements\Node::find()
    ->handle('mainMenu')
    ->all();
```
:::
:::

::: reference
### `hasDescendants`

Narrows the query results based on whether the nodes have any descendants.

This has the opposite effect of [leaves](#leaves).

::: code
```twig Twig
{# Fetch nodes that have descendants #}
{% set nodes = craft.navigation.nodes()
    .hasDescendants()
    .all() %}
```

```php PHP
// Fetch nodes that have descendants
$nodes = \verbb\navigation\elements\Node::find()
    ->hasDescendants()
    ->all();
```
:::
:::

::: reference
### `hasUrl`

Narrows the query results to nodes with a linked element or a non-empty stored URL. Call `hasUrl(false)` to remove this filter. URLs computed by node types, such as Site nodes, are not part of this stored-value filter.

::: code
```twig Twig
{# Fetch nodes with a stored link destination #}
{% set nodes = craft.navigation.nodes()
    .hasUrl()
    .all() %}
```

```php PHP
// Fetch nodes with a stored link destination
$nodes = \verbb\navigation\elements\Node::find()
    ->hasUrl()
    ->all();
```
:::
:::

::: reference
### `id`

Narrows the query results based on the nodes’ IDs.

| Value | Fetches nodes… |
| --- | --- |
| `1` | with an ID of 1. |
| `'not 1'` | not with an ID of 1. |
| `[1, 2]` | with an ID of 1 or 2. |
| `['not', 1, 2]` | not with an ID of 1 or 2. |

::: code
```twig Twig
{# Fetch the node by its ID #}
{% set node = craft.navigation.nodes()
    .id(1)
    .one() %}
```

```php PHP
// Fetch the node by its ID
$node = \verbb\navigation\elements\Node::find()
    ->id(1)
    ->one();
```
:::

::: tip
This can be combined with [fixedOrder](#fixedorder) if you want the results to be returned in a specific order.
:::
:::

::: reference
### `includePendingProjections`

Includes Dynamic node items from disabled or pending sources in the projected children, for previews where editors need to see content before it goes live. It bypasses the plugin tree cache, so don't use it for public front-end menus.

::: code
```twig Twig
{# Fetch the main menu including pending projected items #}
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .includePendingProjections()
    .all() %}
```

```php PHP
// Fetch the main menu including pending projected items
$nodes = \verbb\navigation\elements\Node::find()
    ->handle('mainMenu')
    ->includePendingProjections()
    ->all();
```
:::
:::

::: reference
### `inReverse`

Causes the query results to be returned in reverse order.

::: code
```twig Twig
{# Fetch nodes in reverse #}
{% set nodes = craft.navigation.nodes()
    .inReverse()
    .all() %}
```

```php PHP
// Fetch nodes in reverse
$nodes = \verbb\navigation\elements\Node::find()
    ->inReverse()
    ->all();
```
:::
:::

::: reference
### `leaves`

Narrows the query results based on whether the nodes are “leaves” (nodes with no descendants).

This has the opposite effect of [hasDescendants](#hasdescendants).

::: code
```twig Twig
{# Fetch nodes that have no descendants #}
{% set nodes = craft.navigation.nodes()
    .leaves()
    .all() %}
```

```php PHP
// Fetch nodes that have no descendants
$nodes = \verbb\navigation\elements\Node::find()
    ->leaves()
    ->all();
```
:::
:::

::: reference
### `level`

Narrows the query results based on the nodes’ level within the structure.

| Value | Fetches nodes… |
| --- | --- |
| `1` | with a level of 1. |
| `'not 1'` | not with a level of 1. |
| `'>= 3'` | with a level greater than or equal to 3. |
| `[1, 2]` | with a level of 1 or 2. |
| `['not', 1, 2]` | not with a level of 1 or 2. |

::: code
```twig Twig
{# Fetch nodes positioned at level 3 or above #}
{% set nodes = craft.navigation.nodes()
    .level('>= 3')
    .all() %}
```

```php PHP
// Fetch nodes positioned at level 3 or above
$nodes = \verbb\navigation\elements\Node::find()
    ->level('>= 3')
    ->all();
```
:::
:::

::: reference
### `limit`

Determines the number of nodes that should be returned.

::: code
```twig Twig
{# Fetch up to 10 nodes #}
{% set nodes = craft.navigation.nodes()
    .limit(10)
    .all() %}
```

```php PHP
// Fetch up to 10 nodes
$nodes = \verbb\navigation\elements\Node::find()
    ->limit(10)
    ->all();
```
:::
:::

::: reference
### `menu`

Narrows the query results to nodes in a menu. Pass a menu handle, a [Menu](/reference/menu) element or a [Menu Settings](/reference/menu-settings) model. Passing an object also scopes the query to that menu's structure.

| Value | Fetches nodes… |
| --- | --- |
| `'mainMenu'` | in the menu with a handle of `mainMenu`. |
| a [Menu](/reference/menu) element | in the menu represented by the element. |
| a [Menu Settings](/reference/menu-settings) model | in the menu represented by the model. |
| `null` | in any menu, clearing an earlier `menu`, `menuId` or `handle` value. |

::: code
```twig Twig
{# Fetch nodes for a menu element #}
{% set nodes = craft.navigation.nodes()
    .menu(menu)
    .all() %}
```

```php PHP
// Fetch nodes for a menu element
$nodes = \verbb\navigation\elements\Node::find()
    ->menu($menu)
    ->all();
```
:::
:::

::: reference
### `menuHandle`

Narrows the query results to nodes in the menu with the given handle. It's an alias of [handle](#handle).

::: code
```twig Twig
{# Fetch nodes for the footer menu #}
{% set nodes = craft.navigation.nodes()
    .menuHandle('footerMenu')
    .all() %}
```

```php PHP
// Fetch nodes for the footer menu
$nodes = \verbb\navigation\elements\Node::find()
    ->menuHandle('footerMenu')
    ->all();
```
:::
:::

::: reference
### `menuId`

Narrows the query results to nodes in the menu with the given ID.

| Value | Fetches nodes… |
| --- | --- |
| `1` | in the menu with an ID of 1. |
| `[1, 2]` | in the menus with an ID of 1 or 2. |
| `'not 1'` | not in the menu with an ID of 1. |

::: code
```twig Twig
{# Fetch nodes for the menu with an ID of 1 #}
{% set nodes = craft.navigation.nodes()
    .menuId(1)
    .all() %}
```

```php PHP
// Fetch nodes for the menu with an ID of 1
$nodes = \verbb\navigation\elements\Node::find()
    ->menuId(1)
    ->all();
```
:::
:::

::: reference
### `nextSiblingOf`

Narrows the query results to only the node that comes immediately after another node.

| Value | Fetches the node… |
| --- | --- |
| `1` | after the node with an ID of 1. |
| a [Node](/reference/node) object | after the node represented by the object. |

::: code
```twig Twig
{# Fetch the next node #}
{% set node = craft.navigation.nodes()
    .nextSiblingOf(node)
    .one() %}
```

```php PHP
// Fetch the next node
$node = \verbb\navigation\elements\Node::find()
    ->nextSiblingOf($node)
    ->one();
```
:::
:::

::: reference
### `offset`

Determines how many nodes should be skipped in the results.

::: code
```twig Twig
{# Fetch all nodes except for the first 3 #}
{% set nodes = craft.navigation.nodes()
    .offset(3)
    .all() %}
```

```php PHP
// Fetch all nodes except for the first 3
$nodes = \verbb\navigation\elements\Node::find()
    ->offset(3)
    ->all();
```
:::
:::

::: reference
### `orderBy`

Determines the order that the nodes should be returned in.

::: code
```twig Twig
{# Fetch all nodes in order of date created #}
{% set nodes = craft.navigation.nodes()
    .orderBy('elements.dateCreated asc')
    .all() %}
```

```php PHP
// Fetch all nodes in order of date created
$nodes = \verbb\navigation\elements\Node::find()
    ->orderBy('elements.dateCreated asc')
    ->all();
```
:::
:::

::: reference
### `positionedAfter`

Narrows the query results to only nodes that are positioned after another node.

| Value | Fetches nodes… |
| --- | --- |
| `1` | after the node with an ID of 1. |
| a [Node](/reference/node) object | after the node represented by the object. |

::: code
```twig Twig
{# Fetch nodes after this one #}
{% set nodes = craft.navigation.nodes()
    .positionedAfter(node)
    .all() %}
```

```php PHP
// Fetch nodes after this one
$nodes = \verbb\navigation\elements\Node::find()
    ->positionedAfter($node)
    ->all();
```
:::
:::

::: reference
### `positionedBefore`

Narrows the query results to only nodes that are positioned before another node.

| Value | Fetches nodes… |
| --- | --- |
| `1` | before the node with an ID of 1. |
| a [Node](/reference/node) object | before the node represented by the object. |

::: code
```twig Twig
{# Fetch nodes before this one #}
{% set nodes = craft.navigation.nodes()
    .positionedBefore(node)
    .all() %}
```

```php PHP
// Fetch nodes before this one
$nodes = \verbb\navigation\elements\Node::find()
    ->positionedBefore($node)
    ->all();
```
:::
:::

::: reference
### `prevSiblingOf`

Narrows the query results to only the node that comes immediately before another node.

| Value | Fetches the node… |
| --- | --- |
| `1` | before the node with an ID of 1. |
| a [Node](/reference/node) object | before the node represented by the object. |

::: code
```twig Twig
{# Fetch the previous node #}
{% set node = craft.navigation.nodes()
    .prevSiblingOf(node)
    .one() %}
```

```php PHP
// Fetch the previous node
$node = \verbb\navigation\elements\Node::find()
    ->prevSiblingOf($node)
    ->one();
```
:::
:::

::: reference
### `siblingOf`

Narrows the query results to only nodes that are siblings of another node.

| Value | Fetches nodes… |
| --- | --- |
| `1` | beside the node with an ID of 1. |
| a [Node](/reference/node) object | beside the node represented by the object. |

::: code
```twig Twig
{# Fetch nodes beside this one #}
{% set nodes = craft.navigation.nodes()
    .siblingOf(node)
    .all() %}
```

```php PHP
// Fetch nodes beside this one
$nodes = \verbb\navigation\elements\Node::find()
    ->siblingOf($node)
    ->all();
```
:::
:::

::: reference
### `site`

Determines which site the nodes should be queried in.

The current site will be used by default.

| Value | Fetches nodes… |
| --- | --- |
| `'foo'` | from the site with a handle of `foo`. |
| a `\craft\models\Site` object | from the site represented by the object. |

::: code
```twig Twig
{# Fetch nodes from the Foo site #}
{% set nodes = craft.navigation.nodes()
    .site('foo')
    .all() %}
```

```php PHP
// Fetch nodes from the Foo site
$nodes = \verbb\navigation\elements\Node::find()
    ->site('foo')
    ->all();
```
:::
:::

::: reference
### `siteId`

Determines which site the nodes should be queried in, per the site’s ID.

The current site will be used by default.

::: code
```twig Twig
{# Fetch nodes from the site with an ID of 1 #}
{% set nodes = craft.navigation.nodes()
    .siteId(1)
    .all() %}
```

```php PHP
// Fetch nodes from the site with an ID of 1
$nodes = \verbb\navigation\elements\Node::find()
    ->siteId(1)
    ->all();
```
:::
:::

::: reference
### `status`

Narrows the query results based on the nodes’ statuses. An enabled node is only returned when its menu is also enabled for the queried site.

| Value | Fetches nodes… |
| --- | --- |
| `'enabled'` _(default)_ | that are enabled. |
| `'disabled'` | that are disabled. |
| `['enabled', 'disabled']` | that are enabled or disabled. |
| `null` | regardless of status. |

::: code
```twig Twig
{# Fetch all nodes, regardless of status #}
{% set nodes = craft.navigation.nodes()
    .status(null)
    .all() %}
```

```php PHP
// Fetch all nodes, regardless of status
$nodes = \verbb\navigation\elements\Node::find()
    ->status(null)
    ->all();
```
:::
:::

::: reference
### `type`

Narrows the query results based on the nodes’ type, using the node type's class name. In Twig, escape each backslash in the class name.

::: code
```twig Twig
{# Fetch entry nodes #}
{% set nodes = craft.navigation.nodes()
    .type('verbb\\navigation\\nodetypes\\Entry')
    .all() %}
```

```php PHP
// Fetch entry nodes
$nodes = \verbb\navigation\elements\Node::find()
    ->type('verbb\\navigation\\nodetypes\\Entry')
    ->all();
```
:::

The built-in node types are listed below. A [custom node type](/developers/custom-node-types) uses its own class name.

- `verbb\navigation\nodetypes\Custom`
- `verbb\navigation\nodetypes\Passive`
- `verbb\navigation\nodetypes\Site`
- `verbb\navigation\nodetypes\Entry`
- `verbb\navigation\nodetypes\Category`
- `verbb\navigation\nodetypes\Asset`
- `verbb\navigation\nodetypes\Product` (requires Commerce)
- `verbb\navigation\nodetypes\GroupColumn`
- `verbb\navigation\nodetypes\Dynamic`
:::

::: reference
### `uid`

Narrows the query results based on the nodes’ UIDs.

::: code
```twig Twig
{# Fetch the node by its UID #}
{% set node = craft.navigation.nodes()
    .uid('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
    .one() %}
```

```php PHP
// Fetch the node by its UID
$node = \verbb\navigation\elements\Node::find()
    ->uid('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
    ->one();
```
:::
:::

::: reference
### `withLinkedElements`

Batch-loads linked Craft elements before the query returns. Without this flag, the first access to a linked node's `url` or `element` loads the pending linked elements from the query result together. Both approaches preserve Craft URL events and custom node types.

The plugin tree cache isn't used for a query with this parameter.

::: code
```twig Twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withLinkedElements()
    .all() %}
```

```php PHP
$nodes = \verbb\navigation\elements\Node::find()
    ->handle('mainMenu')
    ->withLinkedElements()
    ->all();
```
:::
:::

::: reference
### `withMenu`

Batch-loads the parent Menu element (including menu-level custom fields) for each node.

The plugin tree cache isn't used for a query with this parameter.

::: code
```twig Twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withMenu()
    .all() %}
```

```php PHP
$nodes = \verbb\navigation\elements\Node::find()
    ->handle('mainMenu')
    ->withMenu()
    ->all();
```
:::
:::

::: reference
### `withNavigationCache`

Opts the query in to the plugin tree cache when the [cacheMode](/get-started/configuration#cachemode) setting is `manual`. In `auto` and `static` modes, eligible queries are cached anyway, so the parameter has no effect.

::: code
```twig Twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withNavigationCache()
    .all() %}
```

```php PHP
$nodes = \verbb\navigation\elements\Node::find()
    ->handle('mainMenu')
    ->withNavigationCache()
    ->all();
```
:::
:::

::: reference
### `withNodeHierarchy`

Wires parent/child relationships in memory after the query executes.

| Value | Behaviour |
| --- | --- |
| `null` (default) | Wires the hierarchy automatically on front-end queries scoped to a menu, but not in the control panel or console. |
| `true` | Always wires the hierarchy. |
| `false` | Never wires the hierarchy, so children load when you access them. |

::: code
```twig Twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withNodeHierarchy(true)
    .all() %}
```

```php PHP
$nodes = \verbb\navigation\elements\Node::find()
    ->handle('mainMenu')
    ->withNodeHierarchy(true)
    ->all();
```
:::
:::

::: reference
### `withProjectedChildren`

Controls whether Dynamic nodes append read-time projected children. Projected children are included by default, so calling `withProjectedChildren()` without an argument is unnecessary. Pass `false` when you need only the stored menu nodes.

A Dynamic node remains in the hierarchy and receives the projected items as its children, so projections don't replace the Dynamic node. On a normal front-end menu-scoped query, Navigation also wires the hierarchy automatically. Calling `withNodeHierarchy(false)` disables that assembly and is not suitable for recursive rendering that needs projected children.

When the hierarchy has been assembled, `node.children` is an `ElementCollection`, not another `NodeQuery`. Apply `withProjectedChildren()` to the original query before calling `all()` rather than calling it on `node.children`.

| Value | Behaviour |
| --- | --- |
| `null` (default) | Includes projected children. |
| `true` | Includes projected children, the same as the default. |
| `false` | Skips projection, so only stored children are returned. |

::: code
```twig Twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withProjectedChildren(false)
    .all() %}
```

```php PHP
$nodes = \verbb\navigation\elements\Node::find()
    ->handle('mainMenu')
    ->withProjectedChildren(false)
    ->all();
```
:::
:::
