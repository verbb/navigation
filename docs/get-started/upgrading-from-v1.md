# Upgrading from v1
While the [changelog](https://github.com/verbb/navigation/blob/craft-4/CHANGELOG.md) is the most comprehensive list of changes, this guide provides high-level overview and organizes changes by category.

## GraphQL

### Queries
We have changed the queries used for GraphQL so as not to conflict with other plugins.

Old | What to do instead
--- | ---
| `nodes(arguments)` | `navigationNodes(arguments)`
| `node(arguments)` | `navigationNode(arguments)`
