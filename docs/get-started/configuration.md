# Configuration

You can customise Navigation’s settings using a PHP configuration file. This is optional: each setting has a default, so you only need to include the values you want to change.

To override a setting, create `navigation.php` in your Craft project’s `/config` directory and return an array of setting names and values. For example, the following will leave existing menus disabled for newly created sites:

```php
<?php

return [
    'autoEnableNewSites' => false,
];
```

All other settings keep their defaults. Add any further settings you want to change to the same array. The options below explain the available settings and their defaults.

## Configuration Options


::: reference
### `pluginName`

**Type:** `string` · **Default:** `'Navigation'`

Sets a custom name for the plugin in the control panel.
:::


::: reference
### `bypassProjectConfig`

**Type:** `bool` · **Default:** `false`

Allows menu definitions to change directly in the database when Craft’s `allowAdminChanges` setting is `false`. When `allowAdminChanges` is `true`, menu changes still write to project config. Use with care: changes made while bypassing project config are local to that database.
:::


### General


::: reference
#### `autoEnableNewSites`

**Type:** `bool` · **Default:** `true`

Automatically enables all menus when a new site is created.
:::


::: reference
#### `builderLiveStructure`

**Type:** `bool` · **Default:** `false`

Controls whether menu structure changes save immediately on drag-and-drop. When `false` (default), the menu builder keeps structure changes unsaved until you click **Save menu**.
:::


### Performance


::: reference
#### `cacheMode`

**Type:** `string` · **Default:** `NavigationCache::MODE_AUTO`

Controls plugin-managed tree caching for front-end navigation reads. Use `off`, `auto` (default), `static`, or `manual`. See [Performance & Caching](/frontend/performance-and-caching).
:::


::: reference
#### `cacheProfile`

**Type:** `string` · **Default:** `NavigationCache::PROFILE_STANDARD`

Controls how much data is stored in the cache. Use `lite`, `standard` (default), or `full`.
:::


::: reference
#### `cacheDuration`

**Type:** `int|null` · **Default:** `86400`

Sets the TTL in seconds when cache mode is `static`.
:::


### Menus (Project Config)

Menu definitions are stored in Craft’s project config unless `bypassProjectConfig` is `true` and `allowAdminChanges` is `false`. This lets your development workflow carry menu settings, permissions, and field layouts between environments. Node trees and the values entered in menu fields are content, stored separately as Craft elements.

Manage these settings in **Navigation → Menus**. You do not need to edit the generated YAML to create a menu. See [Overview](/feature-tour/overview) for the builder workflow.

## Control Panel

You can also manage many configuration settings through the control panel by visiting **Navigation → Settings**.

### Permissions

Navigation registers Craft user permissions under **Settings → Users → {user group} → Navigation**. Assign:

| Permission | Purpose |
| --- | --- |
| **Create menus** | Create new menus in the control panel |
| **Manage “{menu}”** | Open the menu builder and manage nodes |
| **Edit menu settings** | Edit menu settings for that menu (nested under **Manage**) |
| **Delete menu** | Delete that menu (nested under **Manage**) |

Per-menu permissions are created automatically when a menu is saved. Restrict which node types appear in the menu builder using each menu’s **Permissions** tab.

Menu-specific limits (max levels, max nodes, propagation, field layouts) are configured per menu in **Navigation → Menus**, not in `navigation.php`.
