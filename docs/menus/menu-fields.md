# Menu Fields

Navigation provides **menu-level custom fields** — values stored on the `Menu` element, separate from individual node fields.

Use menu fields when you need content attached to the menu itself: mega-menu panels, promo blocks, footer taglines, or global CTA copy that is not tied to a single link.

## Configure the field layout

1. Edit a menu in **Navigation → Menus**.
2. Open the **Menu Fields** tab and add fields to the layout (same as any Craft field layout).
3. Save the menu settings.

The layout is stored in project config. Values are edited per site on the **Menu Content** tab in the menu builder or on the menu settings screen.

## Read menu fields in templates

Fetch the menu element and read field handles directly:

```twig
{% set menu = craft.navigation.menu('mainMenu').one() %}

{% if menu %}
    {{ menu.promoHeading }}
{% endif %}
```

When looping nodes, batch-load menus with **`withMenu()`** on the node query so each node has its parent menu hydrated:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withMenu()
    .all() %}

{% set menu = nodes[0].menu ?? null %}
```

See [Performance & Caching](/frontend/performance-and-caching) for when `withMenu()` affects caching.

## Menu element vs menu settings

| API | Returns | Use for |
| --- | --- | --- |
| `craft.navigation.menu('handle').one()` | [Menu](/reference/menu) element | Field **values** |
| `craft.navigation.getMenuByHandle('handle')` | [Menu](/reference/menu) element | Same as `menu().one()` |
| `Navigation::$plugin->getMenus()->getMenuByHandle('handle')` (PHP) | [MenuSettings](/reference/menu-settings) | CP metadata (limits, propagation, permissions) |

Use `craft.navigation.menu('handle').one()` for field values in templates.
