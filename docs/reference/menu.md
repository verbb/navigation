# Menu

A Menu object represents menu-level custom field **values** for a navigation menu — for example mega-menu content, footer copy, or other data attached to the menu rather than individual nodes. In templates, this is the `verbb\navigation\elements\Menu` element.

## Query

```twig
{% set menu = craft.navigation.menu('mainMenu').one() %}

{% if menu %}
    {{ menu.promoHeading }}
{% endif %}
```

Use `craft.navigation.menu()` to query menu elements. For batch-loading menus while looping nodes, use **`withMenu()`** on [node queries](/getting-elements/node-queries).

## Properties

Menu elements expose standard Craft element properties (`id`, `title`, `siteId`, etc.) plus any custom fields on the menu field layout.

## Methods

| Method | Description |
| --- | --- |
| `getMenuHandle()` | Menu handle string |

## Settings vs element

| API | Returns | Use for |
| --- | --- | --- |
| `craft.navigation.menu('handle').one()` | **Menu** element | Field values in templates |
| `craft.navigation.getMenuByHandle('handle')` | **Menu** element | Same as `menu().one()` — a shortcut when you already know the handle |
| `Navigation::$plugin->getMenus()->getMenuByHandle('handle')` | **MenuSettings** model | CP metadata in PHP — see [Menu Settings](/reference/menu-settings) |

## GraphQL

Per-menu GraphQL types: `{handle}_Menu`. See [Query Nodes](/graphql/query-nodes).
