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
`newWindow` | Whether this node should open in a new window.
`active` | Whether the URL matches the current URL.
`hasActiveChild` | Whether the node has an active child.
`nav` | The navigation model this node belongs to.
`status` | The current status of the node.
`children ` | A collection of child nodes (if any).
