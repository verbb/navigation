# Menu

A Menu object holds the custom field **values** saved on a navigation menu, such as mega-menu content, footer copy or other data that belongs to the menu rather than to individual nodes. In templates, this is the `verbb\navigation\elements\Menu` element.

## Query

```twig
{% set menu = craft.navigation.menu('mainMenu').one() %}

{% if menu %}
    {{ menu.promoHeading }}
{% endif %}
```

Use `craft.navigation.menu()` to query menu elements. For batch-loading menus while looping nodes, use **`withMenu()`** on [Node Queries](/getting-elements/node-queries).

## Properties

Menu elements expose standard Craft element properties (`id`, `title`, `siteId`, etc.) plus any custom fields on the menu field layout.

## Methods

::: reference
### `getMenuHandle()`

**Returns:** `string`

Returns the menu’s handle, such as `mainMenu`. It’s an empty string if the menu has no handle.
:::


## Settings vs Element

| API | Returns | Use for |
| --- | --- | --- |
| `craft.navigation.menu('handle').one()` | **Menu** element | Field values in templates |
| `craft.navigation.getMenuByHandle('handle')` | **Menu** element | Same as `menu().one()`, as a shortcut when you already know the handle |
| `Navigation::$plugin->getMenus()->getMenuByHandle('handle')` | **MenuSettings** model | Menu configuration in PHP. See [Menu Settings](/reference/menu-settings) |

## GraphQL

Each menu has its own GraphQL type, named after its handle, such as `mainMenu_Menu`. [The `[handle]_Menu` Type](/graphql/interfaces-types#the-handle-menu-type) lists its fields.
