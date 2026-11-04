# MCP

Craft 5.12 and later include an [MCP server](https://craftcms.com/docs/5.x/system/mcp.html) that lets AI clients work with a site. When it is enabled, Navigation adds menu nodes to it, so a client can read and edit menus. Access follows Navigation’s menu and site permissions for the signed-in user.

## Nodes

Nodes use Craft’s `elements.*` tools with the type `navigation-nodes` (Craft’s `node` reference handle also works).

- `elements.list` takes the criteria `handle` (menu handle), `menuId`, `type`, `elementId` and `level`.
- `elements.create` needs `type` and either `menuHandle` or `menuId`. Element nodes take `linkedElementId`; custom nodes take `url`. `parentId` nests a node under another node in the same menu.
- `elements.update`, `elements.delete` and `elements.restore` work as they do for entries.

`type` is a node type class name, such as `verbb\navigation\nodetypes\Entry` or `verbb\navigation\nodetypes\Custom`. `elements.schema` with the type `navigation-nodes` lists the registered types, including custom ones.

Changes made over MCP save immediately. They do not go through the menu builder’s staged changes, so someone with unsaved builder changes to the same menu may need to reload it, as with Craft’s own structure moves.

## Tools

| Tool | Does |
| --- | --- |
| `navigation.menus.list` | Lists the menus the user can manage, with their IDs and handles. |
| `navigation.menus.tree` | Returns a menu as nested nodes in one call, the same tree `craft.navigation.tree()` gives templates. Includes disabled nodes and dynamic nodes’ projected children. Takes a menu handle or ID, an optional `siteId`, and `withLinkedElements`. |
| `navigation.nodes.move` | Moves a node `before` or `after` a sibling, or `prepend`s or `append`s it to a parent or the top level of its menu. Takes an optional `siteId`. The menu’s level and node limits apply. |

Craft’s own `structures.move-element` tool only moves entries, so use `navigation.nodes.move` to reorder nodes.
