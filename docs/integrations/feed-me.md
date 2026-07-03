# Feed Me

Navigation integrates with [Feed Me](https://github.com/craftcms/feed-me) for importing nodes into a menu.

## Setup

1. Create a Feed Me feed with element type **Navigation Node**.
2. Select the target **menu** in the feed group settings.
3. Map node fields in the Feed Me UI.

## Mapped fields

Standard node attributes include:

| Field | Notes |
| --- | --- |
| `title` | Node title |
| `type` | Node type class name |
| `elementId` | Linked Craft element ID |
| `linkedElementSiteId` | Site ID of the linked element locale (per-site link target) |
| `url` | Custom URL |
| `urlSuffix` | Per-site URL suffix |
| `newWindow`, `classes` | Link settings |
| `parentId` | Set automatically for nested imports via the **Children** mapping |
| `enabled` | Enabled state |

Menu custom fields on the node field layout appear as additional mapping rows when configured.

## Nested children

Map the **Children** field to import nested node structures. Feed Me runs sub-imports for each child row under the parent node.

## Unique identifiers

Use Feed Me's unique identifier checkboxes to match existing nodes by `title`, `id`, or custom fields before updating or skipping.
