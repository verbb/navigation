# Custom Rendering

Not every node is a plain anchor. Passive labels, mega-menu columns, and Dynamic projections need different markup.

## `getTag()`

Each node type exposes **`getTag()`** — the HTML element name for the node wrapper or link:

| Type | Default tag |
| --- | --- |
| Entry, Category, Custom, Site, … | `a` |
| Passive, GroupColumn | `span` |

`craft.navigation.render()` respects `getTag()` for accessibility (no empty `href` on passive items).

### Example

```twig
{% for node in craft.navigation.nodes('mainMenu').all() %}
    {% set tag = node.getTag() %}
    <{{ tag }}
        {% if node.url and tag == 'a' %}href="{{ node.url }}"{% endif %}
        class="{{ node.getActive() ? 'is-active' }}"
    >
        {{ node.title }}
    </{{ tag }}>
{% endfor %}
```

## Linked element fields

`node.element` is lazy-loaded by default. Use **`withLinkedElements()`** when reading linked entry/category fields in the loop:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withLinkedElements()
    .all() %}

{% for node in nodes %}
    {% if node.element and node.element.myFieldHandle is defined %}
        {{ node.element.myFieldHandle }}
    {% endif %}
{% endfor %}
```

## Node custom fields

Node custom fields (configured on the menu’s node field layout) are available on the node element like any Craft element:

```twig
{{ node.myPlainTextField }}
```

## Projected children

Dynamic children are [ProjectedNode](/reference/projected-node) objects — not full `Node` elements. They support `title`, `url`, active flags, and `element`, but not node custom fields.

```twig
{% for child in node.children %}
    {% if child.isProjected ?? false %}
        {# projected entry #}
    {% else %}
        {# stored Node #}
    {% endif %}
{% endfor %}
```

## Mega menus

Combine [Menu Fields](/menus/menu-fields) for panel content, **GroupColumn** nodes for columns, and custom Twig partials. Navigation does not ship render presets — build layout in your templates using node queries and field values.
