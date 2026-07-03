# Migrating from tka navigation

If your Craft site runs [tka navigation](https://plugins.craftcms.com/tka-navigation) (`thekitchen-agency/craft-tka-navigation`), Navigation can copy menus and node structure without modifying tka data.

## Prerequisites

1. Install **both** tka navigation and Navigation on the same Craft site.
2. Run tka navigation's own migrations so its tables exist.
3. Back up your database and project config.
4. Run the migration on staging first.

## Control panel

1. Go to **Navigation → Settings**.
2. Under **Migrations**, choose **tka navigation**.
3. Select the menus to migrate (or choose **All**).
4. Optionally enable **Skip existing handles** if you do not want to overwrite menus that already exist in Navigation.
5. Click **Migrate Menus** and review the per-menu output log. Click **Done** when finished.

## Console

```bash
# All tka navigation menus
php craft navigation/migrate/tka-navigation

# Specific handle(s)
php craft navigation/migrate/tka-navigation --handle=mainMenu,footerMenu

# Skip menus whose handles already exist in Navigation
php craft navigation/migrate/tka-navigation --skip-existing
```

## What is migrated

| tka navigation | Navigation |
| --- | --- |
| Navigations and handles | Menu name, handle, site settings |
| Entry, category, asset, product nodes | Matching element-backed node types |
| Custom URL nodes | `Custom` node type |
| Passive nodes | `Passive` node type |
| Nested JSON tree | Parent/child tree preserved |
| CSS classes, new window | `classes`, `newWindow`, `customAttributes` |

If an element-backed node no longer has a resolvable linked element, it is converted to a **Custom URL** node and a warning is logged.

## Manual follow-up

| tka navigation feature | Notes |
| --- | --- |
| **Per-site trees** | Only the first site tree is migrated — review other sites manually |
| **Bulk paste / anchor UX** | Not migrated — re-add in the Navigation builder if needed |
| **Template output** | Replace tka Twig helpers with Navigation `render()` or custom templates |

## Handle collisions

When a Navigation menu with the same handle already exists, the migrator creates a new menu with a numeric suffix (for example `mainMenu1`).

## Related

- [Migrations](/guides/migrations-upgrades/)
- [Upgrading from v3](/get-started/upgrading-from-v3)
