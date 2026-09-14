# Projected Node

A projected node is a menu item created from a Craft element when Navigation reads a Dynamic node. For example, a Dynamic node connected to a News section can supply menu items for its entries without an editor adding each entry to the menu manually.

The returned `ProjectedNode` object gives your template a title, destination and access to the original element. It is not a saved [Node](/reference/node) element, so it has no node custom fields or control-panel edit URL. To read an entry's custom fields, use the projected node's [element](#element) instead.

## When You See Them

Projected nodes appear among a Dynamic node's children, after any manually stored children. You can encounter them through `node.children`, `{% children %}` and `tree()` output. Active-state helpers can also return a projected node when it matches the current URL.

## Template Example

For a menu with the handle `mainMenu`, put this example in the Twig template where you want its projected child links to appear. It reads the top-level nodes, then displays their immediate projected children. Configure a top-level Dynamic node with a source containing eligible elements first.

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .all() %}

{% for node in nodes %}
    {% for child in node.children %}
        {% if child.isProjected ?? false %}
            {% set url = child.getUrl() %}

            {% if url %}
                <a href="{{ url }}"{% if child.current %} aria-current="page"{% endif %}>
                    {{ child.title }}
                </a>
            {% else %}
                <span>{{ child.title }}</span>
            {% endif %}
        {% endif %}
    {% endfor %}
{% endfor %}
```

Each `child` that passes the `isProjected` check is the object described below. The example displays plain text when there is no destination and marks an exact current-page link with `aria-current`. It deliberately shows only projected children one level down; [Rendering Nodes](/template-guides/rendering-nodes) explains rendering a complete menu.

## Properties

These entries explain the values useful when working with a projected node in Twig. Use `child` as in the example above, or replace it with your own variable name.

::: reference
### `title`

**Type:** `string`

The label derived from the source element's title, with the element's string representation used when its title is empty. Read it with `child.title`, as shown in the opening example.
:::

::: reference
### `url`

**Type:** `string|null`

The URL copied from the source element. It can be `null` when the element has no URL. For rendering, use [getUrl()](#geturl), which can also resolve a stored URI, and handle an absent destination as the opening example does.
:::

::: reference
### `element`

**Type:** `craft\base\ElementInterface|null`

The original Craft element, such as the entry supplied by an entry-section source. This is where you access that element's own fields. Check that it is available before reading them.

For example, if the Dynamic source supplies entries with a plain-text field whose handle is `summary`, add this inside the projected-child branch of the loop:

```twig
{% set entry = child.element %}

{% if entry %}
    <p>{{ entry.summary }}</p>
{% endif %}
```

Use fields that belong to the configured source's elements. A projected asset and a projected entry do not necessarily expose the same custom fields.
:::

::: reference
### `elementId`

**Type:** `int|null`

The source element's ID. This identifies the original entry, asset or other element, rather than a saved Navigation node. Use `child.element` when you need the object itself.
:::

::: reference
### `level`

**Type:** `int`

The item's depth in the menu. Top-level nodes have level `1`; their immediate projected children have level `2`. This describes menu placement, which can differ from the source element's original structure.
:::

::: reference
### `siteId`

**Type:** `int`

The site ID of the source element used for this projection. It provides the site context when Navigation resolves the item's URI.
:::

::: reference
### `isProjected`

**Type:** `bool`

Always `true` for a projected node. Use `child.isProjected ?? false` when looping over children that may also contain saved nodes, as the opening example does.
:::

::: reference
### `parent`

**Type:** `verbb\navigation\elements\Node|null`

The Dynamic node supplied as the projection's parent. It gives you access to the owning saved node; it is not the source entry's parent in a Craft structure. Guard against `null` before accessing it outside the normal menu output.
:::

::: reference
### `current`

**Type:** `bool`

Whether the item is an exact match for the current request according to Navigation's active matcher. Twig's `child.current` reads `getCurrent()`. Use it for the current-page indicator shown in the example.
:::

::: reference
### `active`

**Type:** `bool`

Whether Navigation's active matcher finds a match for this item, including a broader match than an exact current page. Twig's `child.active` reads `getActive()`. Use this for menu highlighting; use `current` when indicating the current page specifically. [Active State & Context](/template-guides/active-state-and-context) explains matching in more detail.
:::

## Methods

::: reference
### `getUrl()`

**Returns:** `string|null` · **Arguments:** None

Returns the item's URL when one is available. Otherwise, it tries to build a site URL from its URI and site ID. It returns `null` when neither a URL nor a URI is available. The opening example uses this method before deciding whether to render a link.
:::

::: reference
### `getLink(attributes)`

**Returns:** `Twig\Markup|null` · **Arguments:** Optional attribute array, default `null`

Renders an escaped title inside a link when there is a destination, or escaped plain text when there is not. The implementation returns markup for both cases; the declared return type also permits `null`. This is useful when you do not need to write the anchor yourself:

```twig
{{ child.getLink({class: 'menu-link'}) }}
```

The returned markup is ready to render; you do not need to add Twig's `raw` filter.
:::

## Opt Out

To read saved nodes without adding projected children, use `withProjectedChildren(false)` on the query. See [Node Queries](/getting-elements/node-queries) for query options.

## GraphQL

Projected items use the `ProjectedNavigationNode` type with an `isProjected` field. See [GraphQL](/graphql/query-nodes) for querying Navigation through a schema.
