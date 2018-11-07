# Templating

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
{% set nodes = craft.navigation.nodes({ handle: 'mainMenu' }).all() %}

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

<ul>
    {% for node in craft.navigation.nodes({ handle: 'mainMenu', level: 1 }).all() %}
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

### craft.navigation.breadcrumbs()

You can retrieve a list of elements to be used as breadcrumbs. They are not based on your navigation items, and instead use the current URL segments.

```twig
{% for crumb in craft.navigation.breadcrumbs() %}
    {{ crumb.link }}
{% endfor %}
```

### craft.navigation.getActiveNode()

You can get the active node of any navigation through this tag. Often useful if you want to output an additional navigation area on your site that's contextual to the current node you're on.

```twig
{# Represents a Node element #}
{% set activeNode = craft.navigation.getActiveNode() %}

<ul>
    {# Start looping through any nested nodes, starting a the currently active one #}
    {% nav node in craft.navigation.nodes({ descendantOf: activeNode }).all() %}
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

## Node attributes

You have access to the following attributes on navigation nodes. These are also available when querying node elements.

| Parameter | Explanation |
| --------- | ----------- |
| id | ID for the node. |
| elementId | The linked element ID (if not custom). |
| element | The linked element (if not custom). |
| navId | The ID for the nav this node belongs to. |
| url | URL for this node. Either the linked element or custom. |
| title | Title for this node. Either the linked element or custom. |
| link | Full HTML link (combined url and title). |
| type | The element type. If custom, will be `null`. |
| classes | Any additional CSS classes added to the node. |
| newWindow | Whether this node should open in a new window. |
| active | Whether the URL matches the current URL. |
| nav | The navigation model this node belongs to. |
| status | The current status of the node. |
| children | A collection of child nodes (if any). |


## Node queries

As nodes are just elements, you can query nodes as per your needs. See [Element Queries](https://docs.craftcms.com/v3/dev/element-queries/#creating-element-queries) and [Entry Queries](https://docs.craftcms.com/v3/dev/element-queries/entry-queries.html).

Some common query parameters:

| Parameter | Explanation |
| --------- | ----------- |
| hasDescendants | Whether the current node has any children |
| level | The level in the navigation structure this node belongs to. |
| status | The current status of the node. |