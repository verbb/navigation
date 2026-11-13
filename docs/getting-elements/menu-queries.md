# Menu Queries

You can fetch menus in your templates or PHP code using **menu queries**.

::: code
```twig Twig
{# Create a new menu query #}
{% set myQuery = craft.navigation.menu('mainMenu') %}
```

```php PHP
// Create a new menu query
$myQuery = \verbb\navigation\elements\Menu::find();
```
:::

Once you’ve created a menu query, you can set parameters on it to narrow down the results, and then execute it by calling `.all()`. An array of [Menu](/reference/menu) objects will be returned.

A Menu element holds the values of a menu's own custom fields, such as a heading or promo image shown in a mega menu. The links in the menu are nodes, which you fetch with [node queries](/getting-elements/node-queries). For a menu's settings, such as its maximum levels, see [Menu Settings](/reference/menu-settings).

`craft.navigation.menu()` takes a menu handle and returns a menu query with the [handle](#handle) parameter already set, so you'll usually finish it with `.one()`. Pass `null` instead of a handle to query every menu.

::: tip
See [Element Queries](https://craftcms.com/docs/5.x/development/element-queries.html) in the Craft docs to learn about how element queries work.
:::

## Example

We can display a mega menu's promo content by doing the following.

1. Create a menu query with `craft.navigation.menu()`, passing the menu's handle.
2. Fetch the menu with `.one()`.
3. Output its menu-level fields, here a `promoHeading` text field and a `promoImage` Assets field.

```twig
{# Create a menu query for the main menu, and fetch it #}
{% set menu = craft.navigation.menu('mainMenu').one() %}

{% if menu %}
    <h2>{{ menu.promoHeading }}</h2>

    {% set image = menu.promoImage.one() %}

    {% if image %}
        <img src="{{ image.url }}" alt="{{ image.alt }}">
    {% endif %}
{% endif %}
```

Menus don't have statuses, so there's no `status` parameter, and a menu query returns every menu that matches.

## Getting a Menu by Handle or ID

When you only need one menu and know which, `craft.navigation` has shortcuts that run the query for you. `craft.navigation.getMenuByHandle(handle)` returns the [Menu](/reference/menu) element with that handle, the same as `craft.navigation.menu(handle).one()`. `craft.navigation.getMenuById(id)` does the same for a menu ID, which is useful when a template only has an ID to go on, such as one saved in a Number field. Both return `null` when no menu matches. `craft.navigation.getAllMenus()` returns every menu as an array.

This example assumes the entry has a Number field with the handle `promoMenuId`, holding the ID of the menu whose promo heading the page shows:

```twig
{% set menu = entry.promoMenuId ? craft.navigation.getMenuById(entry.promoMenuId) : null %}

{% if menu %}
    <h2>{{ menu.promoHeading }}</h2>
{% endif %}
```

All three return Menu elements, which hold field values. For a menu's settings, such as its maximum levels, use [Menu Settings](/reference/menu-settings) in PHP instead.

When you already have a node, you don't need either shortcut, because `node.menu` returns the menu the node belongs to.

## Parameters

Menu queries support the following parameters.

::: reference
### `asArray`

Causes the query to return matching menus as arrays of data, rather than [Menu](/reference/menu) objects.

::: code
```twig Twig
{# Fetch menus as arrays #}
{% set menus = craft.navigation.menu(null)
    .asArray()
    .all() %}
```

```php PHP
// Fetch menus as arrays
$menus = \verbb\navigation\elements\Menu::find()
    ->asArray()
    ->all();
```
:::
:::

::: reference
### `dateCreated`

Narrows the query results based on the menus’ creation dates.

| Value | Fetches menus… |
| --- | --- |
| `'>= 2026-04-01'` | that were created on or after 2026-04-01. |
| `'< 2026-05-01'` | that were created before 2026-05-01. |
| `['and', '>= 2026-04-01', '< 2026-05-01']` | that were created between 2026-04-01 and 2026-05-01. |

::: code
```twig Twig
{# Fetch menus created this year #}
{% set menus = craft.navigation.menu(null)
    .dateCreated('>= 2026-01-01')
    .all() %}
```

```php PHP
// Fetch menus created this year
$menus = \verbb\navigation\elements\Menu::find()
    ->dateCreated('>= 2026-01-01')
    ->all();
```
:::
:::

::: reference
### `dateUpdated`

Narrows the query results based on the menus’ last-updated dates, which change when menu-level field values are saved.

| Value | Fetches menus… |
| --- | --- |
| `'>= 2026-04-01'` | that were updated on or after 2026-04-01. |
| `'< 2026-05-01'` | that were updated before 2026-05-01. |

::: code
```twig Twig
{# Fetch menus updated in the last week #}
{% set menus = craft.navigation.menu(null)
    .dateUpdated('>= ' ~ now|date_modify('-7 days')|atom)
    .all() %}
```

```php PHP
// Fetch menus updated in the last week
$menus = \verbb\navigation\elements\Menu::find()
    ->dateUpdated('>= ' . (new \DateTime('-7 days'))->format(\DateTime::ATOM))
    ->all();
```
:::
:::

::: reference
### `fixedOrder`

Causes the query results to be returned in the order specified by [id](#id).

::: code
```twig Twig
{# Fetch menus in a specific order #}
{% set menus = craft.navigation.menu(null)
    .id([3, 1, 2])
    .fixedOrder()
    .all() %}
```

```php PHP
// Fetch menus in a specific order
$menus = \verbb\navigation\elements\Menu::find()
    ->id([3, 1, 2])
    ->fixedOrder()
    ->all();
```
:::
:::

::: reference
### `handle`

Narrows the query results based on the menus’ handles. `craft.navigation.menu('mainMenu')` sets this parameter for you.

| Value | Fetches menus… |
| --- | --- |
| `'mainMenu'` | with a handle of `mainMenu`. |
| `['mainMenu', 'footerMenu']` | with a handle of `mainMenu` or `footerMenu`. |
| `'not mainMenu'` | not with a handle of `mainMenu`. |

::: code
```twig Twig
{# Fetch the main and footer menus #}
{% set menus = craft.navigation.menu(null)
    .handle(['mainMenu', 'footerMenu'])
    .all() %}
```

```php PHP
// Fetch the main and footer menus
$menus = \verbb\navigation\elements\Menu::find()
    ->handle(['mainMenu', 'footerMenu'])
    ->all();
```
:::
:::

::: reference
### `id`

Narrows the query results based on the menus’ element IDs.

| Value | Fetches menus… |
| --- | --- |
| `1` | with an ID of 1. |
| `'not 1'` | not with an ID of 1. |
| `[1, 2]` | with an ID of 1 or 2. |

::: code
```twig Twig
{# Fetch the menu by its ID #}
{% set menu = craft.navigation.menu(null)
    .id(1)
    .one() %}
```

```php PHP
// Fetch the menu by its ID
$menu = \verbb\navigation\elements\Menu::find()
    ->id(1)
    ->one();
```
:::
:::

::: reference
### `inReverse`

Causes the query results to be returned in reverse order.

::: code
```twig Twig
{# Fetch menus in reverse #}
{% set menus = craft.navigation.menu(null)
    .inReverse()
    .all() %}
```

```php PHP
// Fetch menus in reverse
$menus = \verbb\navigation\elements\Menu::find()
    ->inReverse()
    ->all();
```
:::
:::

::: reference
### `limit`

Determines the number of menus that should be returned.

::: code
```twig Twig
{# Fetch up to 5 menus #}
{% set menus = craft.navigation.menu(null)
    .limit(5)
    .all() %}
```

```php PHP
// Fetch up to 5 menus
$menus = \verbb\navigation\elements\Menu::find()
    ->limit(5)
    ->all();
```
:::
:::

::: reference
### `menuHandle`

Narrows the query results based on the menus’ handles. It's an alias of [handle](#handle).

::: code
```twig Twig
{# Fetch the footer menu #}
{% set menu = craft.navigation.menu(null)
    .menuHandle('footerMenu')
    .one() %}
```

```php PHP
// Fetch the footer menu
$menu = \verbb\navigation\elements\Menu::find()
    ->menuHandle('footerMenu')
    ->one();
```
:::
:::

::: reference
### `offset`

Determines how many menus should be skipped in the results.

::: code
```twig Twig
{# Fetch all menus except the first #}
{% set menus = craft.navigation.menu(null)
    .offset(1)
    .all() %}
```

```php PHP
// Fetch all menus except the first
$menus = \verbb\navigation\elements\Menu::find()
    ->offset(1)
    ->all();
```
:::
:::

::: reference
### `orderBy`

Determines the order that the menus should be returned in. Menus are returned newest first unless you set an order.

::: code
```twig Twig
{# Fetch menus by their sort order #}
{% set menus = craft.navigation.menu(null)
    .orderBy('navigation_menus.sortOrder asc')
    .all() %}
```

```php PHP
// Fetch menus in alphabetical order
$menus = \verbb\navigation\elements\Menu::find()
    ->orderBy('navigation_menus.sortOrder asc')
    ->all();
```
:::
:::

::: reference
### `site`

Determines which site the menus should be queried in. Menu-level field values can differ between sites, so this controls which site’s values you get. The current site is used by default.

| Value | Fetches menus… |
| --- | --- |
| `'foo'` | from the site with a handle of `foo`. |
| a `craft\models\Site` object | from the site represented by the object. |

::: code
```twig Twig
{# Fetch the main menu from the Foo site #}
{% set menu = craft.navigation.menu('mainMenu')
    .site('foo')
    .one() %}
```

```php PHP
// Fetch the main menu from the Foo site
$menu = \verbb\navigation\elements\Menu::find()
    ->handle('mainMenu')
    ->site('foo')
    ->one();
```
:::
:::

::: reference
### `siteId`

Determines which site the menus should be queried in, by the site’s ID. The current site is used by default.

::: code
```twig Twig
{# Fetch the main menu from the site with an ID of 1 #}
{% set menu = craft.navigation.menu('mainMenu')
    .siteId(1)
    .one() %}
```

```php PHP
// Fetch the main menu from the site with an ID of 1
$menu = \verbb\navigation\elements\Menu::find()
    ->handle('mainMenu')
    ->siteId(1)
    ->one();
```
:::
:::

::: reference
### `uid`

Narrows the query results based on the menus’ element UIDs.

::: code
```twig Twig
{# Fetch the menu by its UID #}
{% set menu = craft.navigation.menu(null)
    .uid('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
    .one() %}
```

```php PHP
// Fetch the menu by its UID
$menu = \verbb\navigation\elements\Menu::find()
    ->uid('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
    ->one();
```
:::
:::

::: reference
### `with`

Causes the query to return matching menus eager-loaded with related elements from their menu-level fields, such as an Assets field on the menu.

::: code
```twig Twig
{# Fetch the main menu with its promo image eager-loaded #}
{% set menu = craft.navigation.menu('mainMenu')
    .with(['promoImage'])
    .one() %}
```

```php PHP
// Fetch the main menu with its promo image eager-loaded
$menu = \verbb\navigation\elements\Menu::find()
    ->handle('mainMenu')
    ->with(['promoImage'])
    ->one();
```
:::
:::
