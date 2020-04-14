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
`title` | Title for this node. Either the linked element or custom.
`link` | Full HTML link (combined url and title).
`type` | The element type. If custom, will be `null`.
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

### `customAttributes`
As attributes are stored in a table for the node, you'll need to loop through them to output them. Each row has an `attribute` and `value` property, as defined in the table field for the node. These correspond with the column names.

```twig
<a {% for attribute in node.customAttributes %}{{ attribute.attribute }}="{{ attribute.value }}"{% endfor %}>
    {{- node.title -}}
</a>
```

## Custom Fields

As you can have custom fields attached to each node, you can access their content via their field handles. For instance:

```twig
{{ node.myPlainTextfield }}
```
