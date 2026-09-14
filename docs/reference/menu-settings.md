# Menu Settings

A MenuSettings object represents **menu configuration** from project config — limits, propagation, permissions, and field layouts — rather than field values saved on the menu. Templates rarely need this object; use the [Menu](/reference/menu) element for custom field values.

## Access

```php
$menuSettings = \verbb\navigation\Navigation::$plugin->getMenus()->getMenuByHandle('mainMenu');
```

MenuSettings is a PHP-only API. In Twig, use `craft.navigation.menu('handle').one()` for menu field values.

## Properties

::: reference
### `id`

**Type:** `int|null`

Menu ID
:::

::: reference
### `name`

**Type:** `string|null`

Display name
:::

::: reference
### `handle`

**Type:** `string|null`

Handle (used in Twig/GraphQL)
:::

::: reference
### `instructions`

**Type:** `string|null`

CP instructions
:::

::: reference
### `maxLevels`

**Type:** `int|null`

Maximum nesting depth
:::

::: reference
### `maxNodes`

**Type:** `int|null`

Maximum total nodes
:::

::: reference
### `propagationMethod`

**Type:** `string`

Multisite propagation for nodes
:::

::: reference
### `titleTranslationMethod`

**Type:** `string`

How node titles propagate across sites (`none`, `site`, `siteGroup`, `language`, `custom`)
:::

::: reference
### `titleTranslationKeyFormat`

**Type:** `string|null`

Custom translation key template when `titleTranslationMethod` is `custom`
:::

::: reference
### `defaultEnabledForPropagatedSites`

**Type:** `bool`

Default **Enabled On Other Sites By Default** value for new nodes when propagation is not `none`
:::

::: reference
### `structureId`

**Type:** `int|null`

Craft structure ID for the node tree
:::

::: reference
### `fieldLayoutId`

**Type:** `int|null`

Node custom field layout
:::


For menu-level **field values**, use `craft.navigation.menu('handle').one()`.
