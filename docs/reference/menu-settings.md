# Menu Settings

A MenuSettings object represents **menu configuration** from project config — limits, propagation, permissions, and field layouts — rather than field values saved on the menu. Templates rarely need this object; use the [Menu](/reference/menu) element for custom field values.

## Access

```php
$menuSettings = \verbb\navigation\Navigation::$plugin->getMenus()->getMenuByHandle('mainMenu');
```

MenuSettings is a PHP-only API. In Twig, use `craft.navigation.menu('handle').one()` for menu field values.

## Properties

| Property | Description |
| --- | --- |
| `id` | Menu ID |
| `name` | Display name |
| `handle` | Handle (used in Twig/GraphQL) |
| `instructions` | CP instructions |
| `maxLevels` | Maximum nesting depth |
| `maxNodes` | Maximum total nodes |
| `propagationMethod` | Multisite propagation for nodes |
| `titleTranslationMethod` | How node titles propagate across sites (`none`, `site`, `siteGroup`, `language`, `custom`) |
| `titleTranslationKeyFormat` | Custom translation key template when `titleTranslationMethod` is `custom` |
| `defaultEnabledForPropagatedSites` | Default **Enabled On Other Sites By Default** value for new nodes when propagation is not `none` |
| `structureId` | Craft structure ID for the node tree |
| `fieldLayoutId` | Node custom field layout |

For menu-level **field values**, use `craft.navigation.menu('handle').one()`.
