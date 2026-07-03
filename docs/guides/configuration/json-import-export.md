# JSON import and export

Portable menu JSON for staging → production moves, backups, and git-tracked menu definitions — modeled after [Formie](https://github.com/verbb/formie) form export/import.

## Control panel

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

## Export format

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

### Portability rules

| Data | Export strategy |
| --- | --- |
| Linked Craft elements | `linkedElementUid` + `linkedElementType` |
| Per-site URLs / suffixes | `siteOverrides` keyed by site handle |
| Menu field values | `menuFieldValues` keyed by site handle |
| Node custom fields | `fieldValues` keyed by field handle |
| Node types | 4.x node type FQCN; legacy v3 aliases accepted on import |

Unresolved element UIDs produce warnings; the node is still created without a link.

## Developer API

```php
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\Navigation;

$menu = Navigation::$plugin->getMenus()->getMenuByHandle('mainMenu');
$export = ImportExportHelper::generateMenuExport($menu);

$result = ImportExportHelper::importMenuFromJson($json, 'create'); // or 'update'
```

See `ImportExportHelper` for the canonical format and import report (`MenuImportResult`).

## Related

- [Feed Me](/integrations/feed-me) — CSV/XML bulk imports
- [Migrating from FreeNav](/guides/migrations-upgrades/migrating-from-freenav) — database migrator (Phase 8)
