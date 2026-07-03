# Migrating from Navigate

If your Craft site runs [Navigate](https://plugins.craftcms.com/navigate) (`studioespresso/craft-navigate`), Navigation can copy menus and node structure without modifying Navigate data.

## Prerequisites

1. Install **both** Navigate and Navigation on the same Craft site.
2. Back up your database and project config.
3. Run the migration on staging first.

## Control panel

1. Go to **Navigation → Settings**.
2. Under **Migrations**, choose **Navigate**.
3. Select the menus to migrate (or choose **All**).
4. Optionally enable **Skip existing handles** if you do not want to overwrite menus that already exist in Navigation.
5. Click **Migrate Menus** and review the per-menu output log. Click **Done** when finished.

## Console

```bash
# All Navigate menus
php craft navigation/migrate/navigate

# Specific handle(s)
php craft navigation/migrate/navigate --handle=mainMenu,footerMenu

# Skip menus whose handles already exist in Navigation
php craft navigation/migrate/navigate --skip-existing
```

## What is migrated

| Navigate | Navigation |
| --- | --- |
| Navigations and handles | Menu name, handle, site settings |
| Entry, category, asset nodes | Matching element-backed node types |
| Custom URL nodes | `Custom` node type |
| Nested structure | Parent/child tree preserved |
| Node CSS classes, new window | `classes`, `newWindow`, `customAttributes` |
| Max levels | Menu `maxLevels` |

If an element-backed node no longer has a resolvable linked element, it is converted to a **Custom URL** node and a warning is logged.

## Manual follow-up

| Navigate feature | Notes |
| --- | --- |
| **Multi-site trees** | Only the primary site tree is migrated — review other sites manually |
| **Class preset dropdown** | Not migrated — use node custom fields or template logic |
| **Template output** | Replace Navigate Twig helpers with Navigation `render()` or custom templates |

## Handle collisions

When a Navigation menu with the same handle already exists, the migrator creates a new menu with a numeric suffix (for example `mainMenu1`).

## Related

- [Migrations](/guides/migrations-upgrades/)
- [Upgrading from v3](/get-started/upgrading-from-v3)
