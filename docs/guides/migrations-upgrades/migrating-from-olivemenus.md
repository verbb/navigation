# Migrating from Olivemenus

If your Craft site runs [Olivemenus](https://plugins.craftcms.com/olivemenus) (`olivestudio/craft-olivemenus`), Navigation can copy menus and node structure without modifying Olivemenus data.

## Prerequisites

1. Install **both** Olivemenus and Navigation on the same Craft site.
2. Back up your database and project config.
3. Run the migration on staging first.

## Control panel

1. Go to **Navigation → Settings**.
2. Under **Migrations**, choose **Olivemenus**.
3. Select the menus to migrate (or choose **All**).
4. Optionally enable **Skip existing handles** if you do not want to overwrite menus that already exist in Navigation.
5. Click **Migrate Menus** and review the per-menu output log. Click **Done** when finished.

## Console

```bash
# All Olivemenus menus
php craft navigation/migrate/olivemenus

# Specific handle(s)
php craft navigation/migrate/olivemenus --handle=mainMenu,footerMenu

# Skip menus whose handles already exist in Navigation
php craft navigation/migrate/olivemenus --skip-existing
```

## What is migrated

| Olivemenus | Navigation |
| --- | --- |
| Menus and handles | Menu name, handle, site settings |
| Entry-linked items | `Entry` node type |
| Custom URL items | `Custom` node type |
| Nested structure | Parent/child tree preserved |
| CSS classes, new window | `classes`, `newWindow`, `customAttributes` |
| Custom attributes JSON | Parsed into `customAttributes` |

If an entry-backed item no longer has a resolvable linked element, it is converted to a **Custom URL** node and a warning is logged.

## Manual follow-up

| Olivemenus feature | Notes |
| --- | --- |
| **`class_parent`** | Not migrated — rebuild with node classes or custom fields |
| **Single-site menus** | Olivemenus site binding is mapped to the menu's configured site |
| **Template output** | Replace Olivemenus Twig helpers with Navigation `render()` or custom templates |

## Handle collisions

When a Navigation menu with the same handle already exists, the migrator creates a new menu with a numeric suffix (for example `mainMenu1`).

## Related

- [Migrations](/guides/migrations-upgrades/)
- [Upgrading from v3](/get-started/upgrading-from-v3)
