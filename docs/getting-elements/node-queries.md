# Node Queries
You can fetch nodes in your templates or PHP code using **node queries**.

:::code
```twig Twig
{# Create a new node query #}
{% set myQuery = craft.navigation.nodes() %}
```

```php PHP
// Create a new node query
$myQuery = \verbb\navigation\elements\Node::find();
```
:::

Once you’ve created a node query, you can set parameters on it to narrow down the results, and then execute it by calling `.all()`. An array of [Node](docs:developers/node) objects will be returned.

:::tip
See Introduction to [Element Queries](https://docs.craftcms.com/v3/dev/element-queries/) in the Craft docs to learn about how element queries work.
:::

## Example
We can display nodes for a given level by doing the following:

1. Create a node query with `craft.navigation.nodes()`.
2. Set the [level](#level), and [limit](#limit) parameters on it.
3. Fetch all nodes with `.all()` and output.
4. Loop through the nodes using a [for](https://twig.symfony.com/doc/2.x/tags/for.html) tag to output the contents.

```twig
{# Create a nodes query with the 'level', and 'limit' parameters #}
{% set nodesQuery = craft.navigation.nodes()
    .level(1)
    .limit(10)%}

{# Fetch the Comments #}
{% set nodes = nodesQuery.all() %}

{# Display their contents #}
{% for node in nodes %}
    <p>{{ node.node }}</p>
{% endfor %}
```

## Parameters

Node queries support the following parameters:


<!-- BEGIN PARAMS -->

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



### `ancestorOf`

Narrows the query results to only nodes that are ancestors of another node.

Possible values include:

| Value | Fetches nodes…
| - | -
| `1` | above the node with an ID of 1.
| a [Node](docs:developers/node) object | above the node represented by the object.

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



### `anyStatus`

Clears out the [status()](https://docs.craftcms.com/api/v3/craft-elements-db-elementquery.html#method-status) and [enabledForSite()](https://docs.craftcms.com/api/v3/craft-elements-db-elementquery.html#method-enabledforsite) parameters.

::: code
```twig Twig
{# Fetch all nodes, regardless of status #}
{% set nodes = craft.navigation.nodes()
    .anyStatus()
    .all() %}
```

```php PHP
// Fetch all nodes, regardless of status
$nodes = \verbb\navigation\elements\Node::find()
    ->anyStatus()
    ->all();
```
:::



### `asArray`

Causes the query to return matching nodes as arrays of data, rather than [Node](docs:developers/node) objects.

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



### `dateCreated`

Narrows the query results based on the nodes’ creation dates.

Possible values include:

| Value | Fetches nodes…
| - | -
| `'>= 2018-04-01'` | that were created on or after 2018-04-01.
| `'< 2018-05-01'` | that were created before 2018-05-01
| `['and', '>= 2018-04-04', '< 2018-05-01']` | that were created between 2018-04-01 and 2018-05-01.

::: code
```twig Twig
{# Fetch nodes created last month #}
{% set start = date('first day of last month') | atom %}
{% set end = date('first day of this month') | atom %}

{% set nodes = craft.navigation.nodes()
    .dateCreated(['and', ">= #{start}", "< #{end}"])
    .all() %}
```

```php PHP
// Fetch nodes created last month
$start = new \DateTime('first day of next month')->format(\DateTime::ATOM);
$end = new \DateTime('first day of this month')->format(\DateTime::ATOM);

$nodes = \verbb\navigation\elements\Node::find()
    ->dateCreated(['and', ">= {$start}", "< {$end}"])
    ->all();
```
:::



### `dateUpdated`

Narrows the query results based on the nodes’ last-updated dates.

Possible values include:

| Value | Fetches nodes…
| - | -
| `'>= 2018-04-01'` | that were updated on or after 2018-04-01.
| `'< 2018-05-01'` | that were updated before 2018-05-01
| `['and', '>= 2018-04-04', '< 2018-05-01']` | that were updated between 2018-04-01 and 2018-05-01.

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
$lastWeek = new \DateTime('1 week ago')->format(\DateTime::ATOM);

$nodes = \verbb\navigation\elements\Node::find()
    ->dateUpdated(">= {$lastWeek}")
    ->all();
```
:::



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



### `descendantOf`

Narrows the query results to only nodes that are descendants of another node.

Possible values include:

| Value | Fetches nodes…
| - | -
| `1` | below the node with an ID of 1.
| a [Node](docs:developers/node) object | below the node represented by the object.

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



### `enabledForSite`

Narrows the query results based on whether the nodes are enabled in the site they’re being queried in, per the [site](#site) parameter.

Possible values include:

| Value | Fetches nodes…
| - | -
| `true` _(default)_ | that are enabled in the site.
| `false` | whether they are enabled or not in the site.

::: code
```twig Twig
{# Fetch all nodes, including ones disabled for this site #}
{% set nodes = craft.navigation.nodes()
    .enabledForSite(false)
    .all() %}
```

```php PHP
// Fetch all nodes, including ones disabled for this site
$nodes = \verbb\navigation\elements\Node::find()
    ->enabledForSite(false)
    ->all();
```
:::



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



### `hasDescendants`

Narrows the query results based on whether the nodes have any descendants.

(This has the opposite effect of calling [leaves](#leaves).)

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



### `hasUrl`

Narrows the query results based on whether the nodes have a URL.

::: code
```twig Twig
{# Fetch nodes that have descendants #}
{% set nodes = craft.navigation.nodes()
    .hasUrl()
    .all() %}
```

```php PHP
// Fetch nodes that have descendants
$nodes = \verbb\navigation\elements\Node::find()
    ->hasUrl()
    ->all();
```
:::



### `id`

Narrows the query results based on the nodes’ IDs.

Possible values include:

| Value | Fetches nodes…
| - | -
| `1` | with an ID of 1.
| `'not 1'` | not with an ID of 1.
| `[1, 2]` | with an ID of 1 or 2.
| `['not', 1, 2]` | not with an ID of 1 or 2.

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



### `leaves`

Narrows the query results based on whether the nodes are “leaves” (nodes with no descendants).

(This has the opposite effect of calling [hasDescendants](#hasdescendants).)

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



### `level`

Narrows the query results based on the nodes’ level within the structure.

Possible values include:

| Value | Fetches nodes…
| - | -
| `1` | with a level of 1.
| `'not 1'` | not with a level of 1.
| `'>= 3'` | with a level greater than or equal to 3.
| `[1, 2]` | with a level of 1 or 2
| `['not', 1, 2]` | not with level of 1 or 2.

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



### `limit`

Determines the number of nodes that should be returned.

::: code
```twig Twig
{# Fetch up to 10 nodes  #}
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



### `nextSiblingOf`

Narrows the query results to only the node that comes immediately after another node.

Possible values include:

| Value | Fetches the node…
| - | -
| `1` | after the node with an ID of 1.
| a [Node](docs:developers/node) object | after the node represented by the object.

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



### `positionedAfter`

Narrows the query results to only nodes that are positioned after another node.

Possible values include:

| Value | Fetches nodes…
| - | -
| `1` | after the node with an ID of 1.
| a [Node](docs:developers/node) object | after the node represented by the object.

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



### `positionedBefore`

Narrows the query results to only nodes that are positioned before another node.

Possible values include:

| Value | Fetches nodes…
| - | -
| `1` | before the node with an ID of 1.
| a [Node](docs:developers/node) object | before the node represented by the object.

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



### `prevSiblingOf`

Narrows the query results to only the node that comes immediately before another node.

Possible values include:

| Value | Fetches the node…
| - | -
| `1` | before the node with an ID of 1.
| a [Node](docs:developers/node) object | before the node represented by the object.

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



### `siblingOf`

Narrows the query results to only nodes that are siblings of another node.

Possible values include:

| Value | Fetches nodes…
| - | -
| `1` | beside the node with an ID of 1.
| a [Node](docs:developers/node) object | beside the node represented by the object.

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



### `site`

Determines which site the nodes should be queried in.

The current site will be used by default.

Possible values include:

| Value | Fetches nodes…
| - | -
| `'foo'` | from the site with a handle of `foo`.
| a `\craft\elements\db\Site` object | from the site represented by the object.

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



### `status`

Narrows the query results based on the nodes’ statuses.

Possible values include:

| Value | Fetches nodes…
| - | -
| `'enabled'` _(default)_ | that are enabled.
| `'disabled'` | that are disabled.

::: code
```twig Twig
{# Fetch disabled nodes #}
{% set nodes = craft.navigation.nodes()
    .status('disabled')
    .all() %}
```

```php PHP
// Fetch disabled nodes
$nodes = \verbb\navigation\elements\Node::find()
    ->status('disabled')
    ->all();
```
:::



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


<!-- END PARAMS -->
