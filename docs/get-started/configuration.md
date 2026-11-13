# Configuration

You can customise Navigation’s settings using a PHP configuration file. This is optional: each setting has a default, so you only need to include the values you want to change.

To override a setting, create `navigation.php` in your Craft project’s `/config` directory and return an array of setting names and values. For example, the following will leave existing menus disabled for newly created sites:

```php
<?php

return [
    'autoEnableNewSites' => false,
];
```

All other settings keep their defaults. Add any further settings you want to change to the same array. The options below explain the available settings and their defaults. You can also manage many of these settings in the control panel under **Navigation → Settings**.

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

Controls plugin-managed tree caching for front-end navigation reads. Use `off`, `auto` (default), `static`, or `manual`. See [Performance & Caching](/frontend/performance-caching).
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
