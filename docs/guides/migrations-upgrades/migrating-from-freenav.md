# Migrating from FreeNav

If your Craft site runs [FreeNav](https://plugins.craftcms.com/free-nav) (`justinholtweb/craft-free-nav`), Navigation can copy menus and node structure without modifying FreeNav data — similar to [Formie's plugin migrations](https://github.com/verbb/formie/tree/beta/docs/guides/migrations-upgrades).

## Prerequisites

1. Install **both** FreeNav and Navigation on the same Craft site.
2. Back up your database and project config.
3. Run the migration on staging first.

## Control panel

1. Go to **Navigation → Settings**.
2. Under **Migrations**, choose **FreeNav**.
3. Select the menus to migrate (or choose **All**).
4. Optionally enable **Skip existing handles** if you do not want to overwrite menus that already exist in Navigation.
5. Click **Migrate Menus** and review the per-menu output log. Click **Done** when finished.

## Console

```bash
# All FreeNav menus
php craft navigation/migrate/free-nav

# Specific handle(s)
php craft navigation/migrate/free-nav --handle=mainMenu,footerMenu

# Skip menus whose handles already exist in Navigation
php craft navigation/migrate/free-nav --skip-existing
```

## What is migrated

| FreeNav | Navigation |
| --- | --- |
| Menus and handles | Menu name, handle, site settings |
| Entry, category, asset, product nodes | Matching element-backed node types |
| Custom URL nodes | `Custom` node type |
| Passive nodes | `Passive` node type |
| Site nodes | `Site` node type |
| Nested structure | Parent/child tree preserved |
| Node CSS classes, new window | `classes`, `newWindow`, `customAttributes` |
| Multi-site propagation | Per-site menu settings |

If an element-backed node no longer has a resolvable linked element, it is converted to a **Custom URL** node and a warning is logged.

## Manual follow-up

| FreeNav feature | Notes |
| --- | --- |
| **Conditional visibility** | Stored in node `data` as `migratedFreeNavVisibilityRules` but not enforced — rebuild with templates or custom logic |
| **Icon / badge fields** | Not migrated — map to node custom fields on the menu field layout |
| **Template render presets** | Replace `craft.freenav.render()` with Navigation templates |
| **REST / GraphQL clients** | Update to Navigation's GraphQL or headless APIs |

## Handle collisions

When a Navigation menu with the same handle already exists, the migrator creates a new menu with a numeric suffix (for example `mainMenu1`).

## Template mapping

```twig
{# FreeNav #}
{{ craft.freenav.render('mainMenu') }}

{# Navigation #}
{{ craft.navigation.render('mainMenu') }}
```

## Related

- [Migrations](/guides/migrations-upgrades/)
- [Upgrading from v3](/get-started/upgrading-from-v3)
