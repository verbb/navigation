# Rendering Nodes

You have two options for outputting your menu:

### craft.navigation.render()

The easy option - let Navigation output the list items for you. This will generate a nested `<ul>` list of navigation items. You can also pass in additional classes for each element.

```twig
{{ craft.navigation.render('navHandle', {
    ulClass: 'nav-items',
    liClass: 'nav-item',
    aClass: 'nav-link',
    activeClass: 'nav-active',
    ulAttributes: {
        'data-attr': 'Some value',
    },
    liAttributes: {
        'data-attr': 'Some value',
    },
    aAttributes: {
        'data-attr': 'Some value',
    },
}) }}
```

### craft.navigation.nodes()

For more fine-grained control over the navigation output, you can call nodes directly. As nodes are elements, output is a breeze using Craft's `{% nav %}` tag, so you don't have to deal with recursive macros.

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .all() %}

{# Or - alternatively #}
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

<ul>
    {% nav node in nodes %}
        <li>
            {{ node.link }}

            {% ifchildren %}
                <ul>
                    {% children %}
                </ul>
            {% endifchildren %}
        </li>
    {% endnav %}
</ul>
```

If you'd rather not use the `{% nav %}` functionality, you can create your own recursive macro to loop through nodes. In addition, you'll also only want to initially output the first level of nodes using the `level: 1` parameter.

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

        {% if node.hasDescendants %}
            <ul>
                {% for subnode in node.children %}
                    {{ macros.navigationNodes(subnode) }}
                {% endfor %}
            </ul>
        {% endif %}
    </li>
{% endmacro %}
```

### craft.navigation.getActiveNode()

You can get the active node of any navigation through this tag. Often useful if you want to output an additional navigation area on your site that's contextual to the current node you're on.

```twig
{# Represents a Node element #}
{% set activeNode = craft.navigation.getActiveNode() %}

<ul>
    {# Start looping through any nested nodes, starting a the currently active one #}
    {% set nodes = craft.navigation.nodes()
        .descendantOf(activeNode)
        .all() %}

    {% nav node in nodes %}
        <li>
            {{ node.title }}<br>
            {{ node.active }}<br>

            {% ifchildren %}
                <ul>
                    {% children %}
                </ul>
            {% endifchildren %}
        </li>
    {% endnav %}
</ul>
```
