# Node Types

Each node has a **type** that controls how it links, renders, and behaves in the menu builder. Types are PHP classes in `verbb\navigation\nodetypes\`.

## Element-backed types

Link to Craft elements. Title and URL come from the linked element unless overridden.

| Type | Links to |
| --- | --- |
| `Entry` | Entries |
| `Category` | Categories |
| `Asset` | Assets |
| `Product` | Commerce products (when Commerce is installed) |

## Structural and URL types

| Type | Purpose |
| --- | --- |
| `Custom` | Manual URL (internal or external) |
| `Passive` | Non-link label / dropdown trigger — renders as `<span>` by default |
| `Site` | Points at a site base URL; active for sub-pages on that site |
| `GroupColumn` | Mega-menu column wrapper — structural, no URL |
| `Dynamic` | Projects elements from a registered source at read time (see below) |

## Dynamic

A **Dynamic** node does not store child nodes for every matching element. At read time, Navigation **projects** matching elements as `ProjectedNode` children (stored manual children appear first, then projected items).

Choose a **source** when adding or editing the node. Built-in sources:

| Handle | Projects |
| --- | --- |
| `entrySection` | Entries from a channel or structure section |
| `categoryGroup` | Categories from a category group |
| `assetVolume` | Assets from a volume |
| `productType` | Commerce products from a product type (when Commerce is installed) |

Configure source-specific settings (section/group/volume, conditions, sort order, optional limit, and so on) in the node slide-out. Source picker labels use each element type’s plural display name (Entries, Categories, Assets, Products).

Third-party plugins can register additional sources via [`RegisterDynamicSourceEvent`](/developers/events#the-registerdynamicsources-event). See [Projected Node](/reference/projected-node) for template and GraphQL behaviour.

## Passive and group rendering

Passive and group nodes use **`getTag()`** (usually `span`) instead of an anchor. See [Custom Rendering](/templates/custom-rendering).

## Custom node types

Register your own types for specialised link behaviour. See [Node Types](/developers/node-types).
