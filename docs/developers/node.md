# Node

Whenever you're dealing with a node in your template, you're actually working with a `Node` object.

## Attributes

Attribute | Description
--- | ---
`id` | ID for the node.
`elementId` | The linked element ID (if not custom).
`element` | The linked element (if not custom).
`navId` | The ID for the nav this node belongs to.
`url` | URL for this node. Either the linked element or custom.
`nodeUri` | URI for this node. Either the linked element or custom.
`title` | Title for this node. Either the linked element or custom.
`link` | Full HTML link (combined url and title).
`type` | The class name for the type of node. If custom, will be `null`.
`nodeType` | The class name for the type of node.
`nodeTypeLabel` | The shortened class name for the type of node.
`classes` | Any additional CSS classes added to the node.
`customAttributes` | A list of attributes as provided in the table. Use `attribute` and `value` for each row.
`urlSuffix` | If provided, a suffix (think anchor or query string) added on to the URL.
`target` | Returns either `_blank` or an empty string, should this node open in a new window.
`newWindow` | Whether this node should open in a new window.
`active` | Whether the URL matches the current URL.
`hasActiveChild` | Whether the node has an active child.
`nav` | The [Navigation](docs:developers/nav) model this node belongs to.
`status` | The current status of the node.
`children ` | A collection of child nodes (if any).
`level ` | The level this node resides in, if using nested nodes.
`isElement ` | Whether the node is an "Element" node type (it links to an Entry, Category, etc).
`isPassive ` | Whether the node is a "Passive" node type.
`isSite ` | Whether the node is a "Site" node type.

### `customAttributes`
As attributes are stored in a table for the node, you'll need to loop through them to output them. Each row has an `attribute` and `value` property, as defined in the table field for the node. These correspond with the column names.

```twig
<a {% for attribute in node.customAttributes %}{{ attribute.attribute }}="{{ attribute.value }}"{% endfor %}>
    {{- node.title -}}
</a>
```

### `linkAttributes`
A helper function to assist with generating attributes for a anchor tag.

```twig
<a {{ node.linkAttributes }}>

{# Would produce the following HTML #}
<a href="/some-url">

{# For a node that opens in a new window #}
<a href="/some-url" target="_blank" rel="noopener">

{# For a node with a custom class #}
<a href="/some-url" class="my-custom-class">

{# For a node with a custom attributes #}
<a href="/some-url" data-attribute="my-attribute-value">
```

You can also pass in any additional attributes you require at the template level:

```twig
<a {{ node.linkAttributes([{ attribute: 'data-target', value: 'some-value' }]) }}>

{# Would produce the following HTML #}
<a href="/some-url" data-target="some-value">
```


## Custom Fields

As you can have custom fields attached to each node, you can access their content via their field handles. For instance you might have added a Plain Text field to your navigation's field layout, with a handle `myPlainTextfield`, which you could access via:

```twig
{{ node.myPlainTextfield }}
```

## Element Custom Fields
As nodes can be linked to an element, you can also fetch those custom fields attached to that element. For example, you might have a Homepage entry, which you've added as a node to your navigation. On this entry, you have a Plain Text field with a handle of `myPlainTextfield`. You could access it via:

```twig
{{ node.element.myPlainTextfield }}
```

However, you'll want to be mindful that when looping through all the other nodes in your navigation that not all nodes are linked to entries, and not all linked entries contain this field. You'll likely receive errors that `myPlainTextfield` is not a valid attribute. So, you'll want to provide some conditional handling of this.

```twig
{# Check that this node links to an element, and it has the field we want #}
{% if node.element and node.element.myPlainTextfield %}
    {{ node.element.myPlainTextfield }}
{% endif %}

{# Check for a specific element, via its slug #}
{% if node.element and node.element.slug == 'homepage' %}
    {{ node.element.myPlainTextfield }}
{% endif %}
```