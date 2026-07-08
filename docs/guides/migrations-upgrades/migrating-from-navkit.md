# Migrating from Navkit

If your Craft site runs [Navkit](https://plugins.craftcms.com/navkit?craft5) (`jainilnagar/navkit`), Navigation can copy menus and node structure without modifying Navkit data — similar to [Formie's plugin migrations](https://github.com/verbb/formie/tree/beta/docs/guides/migrations-upgrades).

## Prerequisites

1. Install **both** Navkit and Navigation on the same Craft site.
2. Back up your database and project config.
3. Run the migration on staging first.

## Control panel

1. Go to **Navigation → Settings**.
2. Under **Migrations**, choose **Navkit**.
3. Select the menus to migrate (or choose **All**).
4. Optionally enable **Skip existing handles** if you do not want to overwrite menus that already exist in Navigation.
5. Click **Migrate Menus** and review the per-menu output log. Click **Done** when finished.

## Console

```bash
# All Navkit menus
php craft navigation/migrate/navkit

# Specific handle(s)
php craft navigation/migrate/navkit --handle=mainMenu,footerMenu

# Skip menus whose handles already exist in Navigation
php craft navigation/migrate/navkit --skip-existing
```

## What is migrated

| Navkit | Navigation |
| --- | --- |
| Menus and handles | Menu name, handle, site settings |
| Entry, category, asset, product links | Matching element-backed node types |
| URL links | `Custom` node type |
| Passive links | `Passive` node type |
| Nested structure | Parent/child tree preserved |
| Node CSS classes, `target="_blank"` | `classes`, `newWindow` |
| `rel` attribute | `customAttributes` |
| Per-menu node custom fields | Node field values on the primary site |
| Multi-site propagation | Per-site menu settings |

If an element-backed node no longer has a resolvable linked element, it is converted to a **Custom URL** node and a warning is logged.

## Manual follow-up

| Navkit feature | Notes |
| --- | --- |
| **Per-menu node field layouts** | Field values are copied when the same custom fields exist on the Navigation menu layout; add matching fields to the Navigation menu before migrating if needed |
| **Menu field layouts** | Navkit has no menu-level fields — not applicable |
| **GraphQL / template clients** | Update to Navigation's `craft.navigation.render()` or `craft.navigation.nodes()` |

## Handle collisions

When a Navigation menu with the same handle already exists, the migrator creates a new menu with a numeric suffix (for example `mainMenu1`).

## Template mapping

```twig
{# Navkit #}
{{ craft.navkit.render('mainMenu') }}

{# Navigation #}
{{ craft.navigation.render('mainMenu') }}
```

## Related

- [Migrations](/guides/migrations-upgrades/)
- [Upgrading from v3](/get-started/upgrading-from-v3)
