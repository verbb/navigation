# Feed Me

Use [Feed Me](https://github.com/craftcms/feed-me) when a feed supplies links you want to import into a saved Navigation menu. Install and enable both plugins, then create the target menu before configuring the feed.

## Setup

1. Create a Feed Me feed with element type **Navigation Node**.
2. Select the target **menu** in the feed group settings.
3. Map node fields in the Feed Me UI.

## Mapped Fields

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

Node custom fields on the menu’s Node Fields layout appear as additional mapping rows when configured.

## Nested Children

Map the **Children** field to import nested node structures. Feed Me runs sub-imports for each child row under the parent node.

::: warning Missing-Element Cleanup
When mapping **Children**, leave **Delete missing elements** and the **Disable missing elements** options off. Feed Me's missing-element cleanup tracks the top-level imported nodes; it can treat nested children as missing and delete or disable them even when they are present in the feed. Use add/update imports for nested trees and remove unwanted children separately.
:::

## Unique Identifiers

Use Feed Me's unique identifier checkboxes to match existing nodes by `title`, `id`, or custom fields before updating or skipping.
