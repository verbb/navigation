# Upgrading from v1
While the [changelog](https://github.com/verbb/navigation/blob/craft-4/CHANGELOG.md) is the most comprehensive list of changes, this guide provides high-level overview and organizes changes by category.

## Plugin Settings
We've removed `disabledElements` and `propagateSiteElements` as they are no longer required.

## Manual vs. Custom
We've renamed references of "manual" nodes, to "custom" nodes.

Old | What to do instead
--- | ---
| `Node::isManual()` | `Node::isCustom()`

In addition, a manual node is now a Node Type, to better align with other custom node types, like Passive and Site.

## Nodes

Old | What to do instead
--- | ---
| `Node::nodeType` | Use `Node::type` or `Node::getType()` instead.
| `Node::nodeTypeLabel` | Use `Node::getTypeLabel()` instead.

## GraphQL

### Queries
We have changed the queries used for GraphQL so as not to conflict with other plugins.

Old | What to do instead
--- | ---
| `nodes(arguments)` | `navigationNodes(arguments)`
| `node(arguments)` | `navigationNode(arguments)`
