# Node

Whenever you're dealing with a node in your template, you're actually working with a `Node` object.

## Attributes

Attribute | Description
--- | ---
`id` | ID for the node.
`elementId` | The linked element ID (if not custom).
`element` | The linked element (if not custom).
`elementSlug` | The linked element slug (if not custom).
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
`nav` | The navigation model this node belongs to.
`status` | The current status of the node.
`children ` | A collection of child nodes (if any).
`level ` | The level this node resides in, if using nested nodes.
