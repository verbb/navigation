# Custom Rendering

Not every node is a plain anchor. Passive labels, mega-menu columns, and Dynamic projections need different markup.

## `getTag()`

Each node type exposes **`getTag()`** — the HTML element name for the node wrapper or link:

| Type | Default tag |
| --- | --- |
| Entry, Category, Custom, Site, … | `a` |
| Passive, GroupColumn, Dynamic | `span` |

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

## Linked Element Fields

`node.element` loads on first access, together with pending linked elements from the same query result. You can use **`withLinkedElements()`** to load them before the query returns:

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

## Node Custom Fields

Node custom fields (configured on the menu’s node field layout) are available on the node element like any Craft element:

```twig
{{ node.myPlainTextField }}
```

## Projected Children

Dynamic nodes remain in the saved menu hierarchy. Navigation attaches each matching entry, category, asset, or product beneath its Dynamic node as a [ProjectedNode](/reference/projected-node) object; the projected items do not replace their parent.

For example, suppose the first top-level node in `mainMenu` is a Passive node named **News**, and its first child is a Dynamic node that projects entries from a News section. The resulting hierarchy has three levels:

```text
News (Passive)
└── News entries (Dynamic)
    ├── First article (ProjectedNode)
    └── Second article (ProjectedNode)
```

Put the following code in a Twig template to reach the Dynamic node and inspect its projected children:

```twig
{% set roots = craft.navigation.nodes('mainMenu')
    .level(1)
    .all() %}

{% set newsGroup = roots|first %}
{% set dynamicNode = newsGroup.children|first %}

{% for child in dynamicNode.children %}
    {% if child.isProjected ?? false %}
        {# projected entry #}
    {% else %}
        {# stored Node #}
    {% endif %}
{% endfor %}
```

Projected children are included by default. When Navigation has assembled the hierarchy, `node.children` is an `ElementCollection`, so apply query options such as `withProjectedChildren(false)` to the original `craft.navigation.nodes()` query before calling `all()`. Do not disable hierarchy assembly for recursive menu rendering. See [Rendering Nodes](/template-guides/rendering-nodes) for a complete recursive macro and [Auto-List a Section with Dynamic Nodes](/user-guides/templating/auto-list-a-section-with-dynamic-nodes) for a full walkthrough.

Projected nodes support `title`, `url`, active flags, and `element`, but not node custom fields.

## Mega Menus

Combine [Menu Fields](/feature-tour/menu-fields) for panel content, **GroupColumn** nodes for columns, and custom Twig partials. Navigation does not ship render presets — build layout in your templates using node queries and field values.
