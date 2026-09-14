# JSON Import and Export

Export a menu to JSON when you want a backup or need to copy its settings and links to another environment. Importing the file can create a separate menu or replace an existing menu you choose.

For CSV or XML content, use [Feed Me](/integrations/feed-me). If the menus already belong to another plugin, choose its guide under [Migrations & Upgrades](/user-guides/migrations-upgrades/) before exporting anything.

## Control Panel

Go to **Navigation → Settings → Import/Export** (`/admin/navigation/settings/import-export`).

1. **Import Menu** — upload a JSON file, review the console-style summary on the next screen, then import
2. **Export Menu** — pick a menu from the dropdown and download JSON

Import is a **settings-level workflow**, not something you do while editing a menu. The file describes a whole menu; Navigation either creates a new one or — if you explicitly choose to — updates an existing menu with the same handle.

When a handle already exists, you must choose:

- **Create a new menu** — generates a unique handle (`mainMenu1`, etc.). Safest default for staging → production.
- **Update existing menu** — overwrites settings and **replaces the entire node tree**. Destructive; use when syncing a known menu in place.

After import you land on an **Import Completed** screen with a link to the menu builder.

## Console

```bash
# List menus and JSON files in the export folder
php craft navigation/import-export/list

# Export
php craft navigation/import-export/export-json mainMenu
php craft navigation/import-export/export-json mainMenu --path=./mainMenu.json

# Import (creates a new menu when the handle already exists)
php craft navigation/import-export/import-json ./mainMenu.json

# Import and overwrite an existing menu by handle
php craft navigation/import-export/import-json ./mainMenu.json --update
```

Default export folder: `@storage/navigation-exports`.

## Export Format

```json
{
  "exportVersion": "1.0.0",
  "exportedAt": "2026-07-03T00:00:00+00:00",
  "sourceSiteHandle": "default",
  "menu": {
    "name": "Main Menu",
    "handle": "mainMenu",
    "propagationMethod": "all",
    "siteSettings": { "default": { "enabled": true } }
  },
  "menuFieldValues": {
    "default": { "promoHeading": "Shop now" }
  },
  "nodes": [
    {
      "title": "About",
      "type": "verbb\\navigation\\nodetypes\\Custom",
      "url": "/about",
      "newWindow": false,
      "children": []
    }
  ]
}
```

### Portability Rules

| Data | Export strategy |
| --- | --- |
| Linked Craft elements | `linkedElementUid` + `linkedElementType` |
| Per-site URLs / suffixes | `siteOverrides` keyed by site handle |
| Menu field values | `menuFieldValues` keyed by site handle |
| Node custom fields | `fieldValues` keyed by field handle |
| Node types | Registered node type class name |

Unresolved element UIDs produce warnings; the node is still created without a link.

## Developer API

```php
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\Navigation;

$menu = Navigation::$plugin->getMenus()->getMenuByHandle('mainMenu');
$export = ImportExportHelper::generateMenuExport($menu);

$result = ImportExportHelper::importMenuFromJson($json, 'create'); // or 'update'
```

See `ImportExportHelper` for the format details and import report (`MenuImportResult`).
