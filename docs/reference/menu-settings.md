# Menu Settings

A MenuSettings object holds a menu’s **configuration** from project config, such as its limits, propagation, permissions and field layouts, rather than the field values saved on the menu. Templates rarely need this object. Use the [Menu](/reference/menu) element for custom field values.

## Access

```php
$menuSettings = \verbb\navigation\Navigation::$plugin->getMenus()->getMenuByHandle('mainMenu');
```

MenuSettings is a PHP-only API. In Twig, use `craft.navigation.menu('handle').one()` for menu field values.

## Properties

::: reference
### `id`

**Type:** `int|null`

The ID of the menu.
:::

::: reference
### `name`

**Type:** `string|null`

The menu’s name, as shown in the control panel.
:::

::: reference
### `handle`

**Type:** `string|null`

The menu’s handle, such as `mainMenu`. This is the name you use for the menu in Twig, PHP and GraphQL.
:::

::: reference
### `instructions`

**Type:** `string|null`

The instructions shown to editors for this menu in the control panel. It’s `null` when the menu has none.
:::

::: reference
### `maxLevels`

**Type:** `int|null`

The deepest level editors can nest nodes to, where top-level nodes are at level 1. It’s `null` when nesting isn’t limited, and `1` keeps the menu flat.
:::

::: reference
### `maxNodes`

**Type:** `int|null`

The most nodes editors can add to the menu on each site. It’s `null` when there’s no limit.
:::

::: reference
### `propagationMethod`

**Type:** `string`

The way new nodes are copied to the menu’s other sites, which is `none`, `siteGroup`, `language` or `all`.
:::

::: reference
### `titleTranslationMethod`

**Type:** `string`

The way node titles are translated across sites, which is `none`, `site`, `siteGroup`, `language` or `custom`. With `none`, a title edited on one site changes on every site.
:::

::: reference
### `titleTranslationKeyFormat`

**Type:** `string|null`

The Twig template that builds the translation key for node titles when [titleTranslationMethod](#titletranslationmethod) is `custom`. It’s `null` for the other methods.
:::

::: reference
### `defaultEnabledForPropagatedSites`

**Type:** `bool`

Whether new nodes are enabled on the menu’s other sites when they’re copied there. Editors see this as the default for **Enabled On Other Sites By Default**, and it only applies when [propagationMethod](#propagationmethod) isn’t `none`.
:::

::: reference
### `structureId`

**Type:** `int|null`

The ID of the Craft structure that holds the menu’s node tree.
:::

::: reference
### `fieldLayoutId`

**Type:** `int|null`

The ID of the field layout used for the menu’s nodes, which sets the custom fields editors fill in on each node.
:::


For menu-level **field values**, use `craft.navigation.menu('handle').one()`.
