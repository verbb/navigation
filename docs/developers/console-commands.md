# Console Commands

Navigation comes with a number of command line utilities that can be run on-demand, or on a set schedule.

## `navigation/menus/fix-sites`

Repairs missing menu site settings rows in the database.

```bash
php craft navigation/menus/fix-sites
```

## Resaving nodes

Use Craft's built-in resave command with the `--menuId` parameter:

```bash
php craft resave/navigation-nodes --menuId=1
```

## `navigation/import-export/*`

Portable menu JSON export and import (see [JSON import and export](/guides/configuration/json-import-export)).

```bash
php craft navigation/import-export/list
php craft navigation/import-export/export-json mainMenu
php craft navigation/import-export/export-json mainMenu --path=./mainMenu.json
php craft navigation/import-export/import-json ./mainMenu.json
php craft navigation/import-export/import-json ./mainMenu.json --update
```

## `navigation/migrate/*`

Copy menus from third-party navigation plugins (see [Migrations](/guides/migrations-upgrades/)).

```bash
php craft navigation/migrate/free-nav
php craft navigation/migrate/navigate
php craft navigation/migrate/navkit
php craft navigation/migrate/olivemenus
php craft navigation/migrate/tka-navigation

# Options
php craft navigation/migrate/free-nav --handle=mainMenu,footerMenu
php craft navigation/migrate/free-nav --skip-existing
```
