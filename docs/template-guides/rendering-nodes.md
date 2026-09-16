# Rendering Nodes

Once you have [created a menu](/feature-tour/overview), put it on your site by adding Twig to the template where its links belong. The examples use a saved menu with handle `mainMenu`; replace that handle with your own. You can let Navigation render a nested list or query nodes and build your own markup.

## `craft.navigation.render()`

The quickest option — Navigation outputs a nested `<ul>` with active classes applied.

```twig
{{ craft.navigation.render('mainMenu', {
    ulClass: 'nav-items',
    liClass: 'nav-item',
    aClass: 'nav-link',
    activeClass: 'nav-active',
    currentClass: 'nav-current',
    hasChildrenClass: 'nav-has-children',
}) }}
```

`render()` outputs a fixed nested list from stored node titles and URLs. Parent/child relationships are wired in memory on front-end requests; no additional loading flags are needed for typical use.

## `craft.navigation.nodes()`

Use a node query when you need full control over HTML, mega-menu layouts, node custom fields, or linked element data (`withLinkedElements()`).

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .all() %}

<ul>
    {% nav node in nodes %}
        <li>
            {{ node.link }}

            {% ifchildren %}
                <ul>{% children %}</ul>
            {% endifchildren %}
        </li>
    {% endnav %}
</ul>
```

You can pass the menu handle as a shorthand:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}
```

On frontend menu-scoped reads, parent/child relationships are loaded together automatically — you do not need Craft eager-loading for hierarchy in most cases. See [Performance & Caching](/frontend/performance-and-caching).

For query parameters, see [Node Queries](/getting-elements/node-queries).

### Recursive Macro (Without `{% nav %}`)

If you prefer macros, fetch only top-level nodes and recurse manually:

```twig
{% import _self as macros %}

{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .all() %}

<ul>
    {% for node in nodes %}
        {{ macros.navigationNodes(node) }}
    {% endfor %}
</ul>

{% macro navigationNodes(node) %}
    {% import _self as macros %}

    <li>
        {{ node.link }}

        {% if node.children %}
            <ul>
                {% for subnode in node.children %}
                    {{ macros.navigationNodes(subnode) }}
                {% endfor %}
            </ul>
        {% endif %}
    </li>
{% endmacro %}
```

## Custom Markup

When building links yourself, use [Node](/reference/node) helpers and active-state methods:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

<ul>
    {% nav node in nodes %}
        <li class="{{ node.getActive() ? 'is-active' }}{{ node.getCurrent() ? ' is-current' }}">
            <a {{ node.linkAttributes({
                class: (node.isProjected ?? false) ? null : node.classes,
            }) }}>
                {{- node.title -}}
            </a>

            {% ifchildren %}
                <ul>{% children %}</ul>
            {% endifchildren %}
        </li>
    {% endnav %}
</ul>
```

Passive and group nodes may not use `<a>` tags — see [Custom Rendering](/template-guides/custom-rendering).
