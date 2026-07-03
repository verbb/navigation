# Migrations

Import menus from other Craft navigation plugins into Navigation.

Version upgrades (for example Navigation 3 → 4) are covered in [Upgrading from v3](/get-started/upgrading-from-v3).

## Third-party plugins

Run migrations from **Navigation → Settings**, under the **Migrations** sidebar heading (one page per source plugin when that plugin is installed), or use the console commands below. Source plugin data is never modified.

| Plugin | Console command | Guide |
| --- | --- | --- |
| [FreeNav](https://plugins.craftcms.com/free-nav) | `php craft navigation/migrate/free-nav` | [Migrating from FreeNav](/guides/migrations-upgrades/migrating-from-freenav) |
| [Navigate](https://plugins.craftcms.com/navigate) | `php craft navigation/migrate/navigate` | [Migrating from Navigate](/guides/migrations-upgrades/migrating-from-navigate) |
| [Olivemenus](https://plugins.craftcms.com/olivemenus) | `php craft navigation/migrate/olivemenus` | [Migrating from Olivemenus](/guides/migrations-upgrades/migrating-from-olivemenus) |
| [tka navigation](https://plugins.craftcms.com/tka-navigation) | `php craft navigation/migrate/tka-navigation` | [Migrating from tka navigation](/guides/migrations-upgrades/migrating-from-tka-navigation) |

Shared options:

- `--handle=mainMenu` — migrate specific handle(s), comma-separated
- `--skip-existing` — skip when a Navigation menu with the same handle already exists

## JSON import/export

For portable backups and cross-environment moves (not competitor DB tables), use [JSON import/export](/guides/configuration/json-import-export) instead.
