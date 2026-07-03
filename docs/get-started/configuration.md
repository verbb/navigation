# Configuration

Create a `navigation.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these settings per environment.

The example below shows the defaults already used by Navigation, so you only need to add the settings you want to change.

```php
<?php

return [
    '*' => [
        'pluginName' => 'Navigation',
        'bypassProjectConfig' => false,

        // General
        'autoEnableNewSites' => true,
        'builderLiveStructure' => false,

        // Performance
        'cacheMode' => 'auto', // `off`, `auto`, `static`, or `manual`
        'cacheProfile' => 'standard', // `lite`, `standard`, or `full`
        'cacheDuration' => 86400,
    ],
];
```

## Configuration options

- `pluginName` sets a custom name for the plugin in the control panel.
- `bypassProjectConfig` stores menus in the database instead of project config. Use with care — this bypasses Craft’s normal project-config workflow for menu definitions.

### General

- `autoEnableNewSites` automatically enables all menus when a new site is created.
- `builderLiveStructure` controls whether menu structure changes save immediately on drag-and-drop. When `false` (default), the menu builder keeps structure changes unsaved until you click **Save menu**.

### Performance

- `cacheMode` controls plugin-managed tree caching for front-end navigation reads. Use `off`, `auto` (default), `static`, or `manual`. See [Performance & Caching](/frontend/performance-and-caching).
- `cacheProfile` controls how much data is stored in the cache. Use `lite`, `standard` (default), or `full`.
- `cacheDuration` sets the TTL in seconds when cache mode is `static`.

### Menus (project config)

Menus are managed in the control panel under **Navigation → Menus**, but their definitions are stored in project config under `navigation.menus.{uid}` when `bypassProjectConfig` is `false`:

```yaml
navigation:
  menus:
    7f3e2a1b-0000-4000-8000-000000000001:
      name: Main Menu
      handle: mainMenu
      sortOrder: 1
      propagationMethod: all
      maxLevels: null
      maxNodes: null
      permissions: []
      siteSettings:
        1:
          enabled: true
      # field layouts, max node limits, and more…
```

Each entry stores menu metadata, site settings, permissions, and field layout configuration. Node trees and menu field values are stored separately as Craft elements.

See [Overview](/menus/overview) for control panel behaviour.

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
