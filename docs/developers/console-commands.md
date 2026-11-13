# Console Commands

Navigation includes console commands you can run from your Craft project’s directory.

## Import & Export

These commands export a menu to a portable JSON file and import it again, such as to copy a menu between environments. [JSON Import and Export](/user-guides/configuration/json-import-and-export) explains what the file contains and how imports behave.

::: reference
### `navigation/import-export/list`

Lists every menu with its ID, handle and name, then the JSON files in the export folder. The export folder is `storage/navigation-exports` unless you pass another folder as the argument.

```shell
php craft navigation/import-export/list
php craft navigation/import-export/list ./menu-exports
```
:::

::: reference
### `navigation/import-export/export-json`

Exports a menu, with all of its nodes, to a JSON file. Pass the menu’s handle as the argument or with `--handle`.

| Option | Description | Default |
| --- | --- | --- |
| `--handle` | The handle of the menu to export, when it isn’t passed as the argument. | |
| `--path` | The file to write the export to. | `storage/navigation-exports/navigation-[handle].json` |

```shell
php craft navigation/import-export/export-json mainMenu
php craft navigation/import-export/export-json mainMenu --path=./mainMenu.json
```
:::

::: reference
### `navigation/import-export/import-json`

Imports a menu from a JSON file. Pass the file’s path as the argument or with `--path`. A path that doesn’t exist is also looked for in the export folder, so a file name from `list` is enough. By default the import creates a new menu, and gives it a new handle when the file’s handle is already taken.

| Option | Description | Default |
| --- | --- | --- |
| `--path` | The JSON file to import, when it isn’t passed as the argument. | |
| `--update` | Updates the existing menu with the same handle instead of creating a new one, replacing its whole node tree. The import fails when no menu has that handle. | `false` |
| `--create` | Creates a new menu, which is already the default. | `false` |

```shell
php craft navigation/import-export/import-json ./mainMenu.json
php craft navigation/import-export/import-json navigation-mainMenu.json --update
```
:::

## Migrating From Other Plugins

These commands copy menus from another navigation plugin into Navigation. The other plugin’s tables must still be in the database, and the commands leave its data in place. Each command reports what it created, skipped and couldn’t copy. The [migration guides](/user-guides/migrations-upgrades/migrating-from-freenav) explain what each plugin’s menus become.

Every migration command takes the same options.

| Option | Description | Default |
| --- | --- | --- |
| `--handle` | A comma-separated list of the other plugin’s menu handles to migrate. Leave it out to migrate every menu. | |
| `--skip-existing` | Skips a menu whose handle is already used by a Navigation menu. Without it, the migrated menu is saved with a new handle. | `false` |

::: reference
### `navigation/migrate/free-nav`

Migrates menus from FreeNav.

```shell
php craft navigation/migrate/free-nav
php craft navigation/migrate/free-nav --handle=mainMenu,footerMenu
php craft navigation/migrate/free-nav --skip-existing
```
:::

::: reference
### `navigation/migrate/menu`

Migrates menus from the Menu plugin.

```shell
php craft navigation/migrate/menu
```
:::

::: reference
### `navigation/migrate/menu-builder`

Migrates menus from Menu Builder.

```shell
php craft navigation/migrate/menu-builder
```
:::

::: reference
### `navigation/migrate/navigate`

Migrates menus from Navigate.

```shell
php craft navigation/migrate/navigate
```
:::

::: reference
### `navigation/migrate/navkit`

Migrates menus from Navkit.

```shell
php craft navigation/migrate/navkit
```
:::

::: reference
### `navigation/migrate/olivemenus`

Migrates menus from Olivemenus.

```shell
php craft navigation/migrate/olivemenus
```
:::

::: reference
### `navigation/migrate/tka-navigation`

Migrates menus from TKA Navigation.

```shell
php craft navigation/migrate/tka-navigation
```
:::

## Resaving Nodes

::: reference
### `resave/navigation-nodes`

Resaves Navigation nodes through Craft’s `resave` command, such as after changing a menu’s field layout. It also takes Craft’s usual resave options, such as `--queue` and `--site`.

| Option | Description | Default |
| --- | --- | --- |
| `--menuId` | A comma-separated list of menu IDs whose nodes to resave. Leave it out to resave nodes in every menu. | |

```shell
php craft resave/navigation-nodes --menuId=1
php craft resave/navigation-nodes --menuId=1,2 --queue
```
:::

## Repairs

::: reference
### `navigation/menus/fix-sites`

Repairs menus that have no site settings saved. It restores the sites from project config, from the menu’s element records or, on a single-site install, the primary site. A multi-site menu it can’t work out is skipped and reported.

```shell
php craft navigation/menus/fix-sites
```
:::

::: reference
### `navigation/menus/fix-menu-element-collisions`

Repairs a database where menu elements took over the IDs of other elements. It restores the affected elements, moves the colliding menus to their own elements, recreates missing menu elements and restores empty titles on linked entry nodes. Only sites the [upgrade guide](/get-started/upgrading-from-v3#sites-that-ran-a-beta) describes need it.

```shell
php craft navigation/menus/fix-menu-element-collisions
```
:::
