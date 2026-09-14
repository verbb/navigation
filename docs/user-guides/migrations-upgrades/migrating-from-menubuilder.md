# Migrating from MenuBuilder

If your Craft site runs [MenuBuilder](https://plugins.craftcms.com/menubuilder?craft5) (`tahadudhiya/craft-menu-builder`), Navigation can copy its menus and item trees without changing the original MenuBuilder data.

## Prerequisites

1. Install **both** MenuBuilder and Navigation on the same Craft site.
2. Back up your database and project config.
3. Run the migration on staging first.

## Control Panel

1. Go to **Navigation → Settings**.
2. Under **Migrations**, choose **MenuBuilder**.
3. Select the menus to migrate (or choose **All**).
4. Optionally enable **Skip existing handles** to leave matching handles out of the import instead of creating additional menus with suffixed handles.
5. Click **Migrate Menus** and review the per-menu output log. Click **Done** when finished.

## Console

Run these commands from your Craft project root:

```bash
# All MenuBuilder menus
php craft navigation/migrate/menu-builder

# Specific handle(s)
php craft navigation/migrate/menu-builder --handle=mainMenu,footerMenu

# Skip menus whose handles already exist in Navigation
php craft navigation/migrate/menu-builder --skip-existing
```

## What Is Migrated

| MenuBuilder | Navigation |
| --- | --- |
| Menu name, handle, and description | Name, handle, and instructions |
| Enabled state and site restrictions | Per-site menu availability |
| Maximum depth | Maximum levels |
| URL and anchor items | `Custom` nodes |
| Entry, category, and asset items | Matching element-backed node types |
| Non-clickable and separator items | `Passive` nodes |
| Entry section, category group, and asset volume dynamic items | Matching `Dynamic` sources, including limit and order |
| Parent IDs and sort order | Nested node structure |
| CSS classes and new-window behaviour | `classes` and `newWindow` |
| `rel`, HTML ID, ARIA label, title attribute, and custom HTML attributes | `customAttributes` |

When an element link no longer resolves, its MenuBuilder fallback behaviour is applied. A fallback URL becomes a `Custom` node, **Disable link** becomes a `Passive` node, and **Hide** becomes a disabled `Custom` node. An unavailable dynamic source becomes a `Passive` node so the rest of the menu can still migrate.

## Manual Follow-Up

MenuBuilder and Navigation have different presentation and visibility systems. The migrator keeps unsupported item settings under `node.data.migratedMenuBuilder` and reports warnings, but Navigation does not apply those settings automatically.

Review these features after the migration:

| MenuBuilder feature | What to Do in Navigation |
| --- | --- |
| Visibility rules | Recreate the conditions in your templates or project logic |
| Mega-menu and mobile settings | Rebuild the presentation in your Navigation templates |
| Icons, badges, descriptions, images, and featured state | Add suitable node custom fields or read the retained values in custom template logic |
| Separator markup | Render migrated separator nodes appropriately, or replace them with your preferred structure |
| Item field layouts and custom field values | Configure and populate matching Navigation node fields manually |
| Menu CSS classes, HTML attributes, and other presentation settings | Apply them in the template that renders the menu |
| Twig, GraphQL, and REST consumers | Replace MenuBuilder calls and queries with Navigation's APIs |

After migrating, compare each menu in the control panel and on the front end. Check restricted sites, disabled items, linked elements, dynamic children, and any template logic rebuilt from the warning output before removing MenuBuilder.

## Handle Collisions

When a Navigation menu already uses the same handle, the migrator creates a new menu with a numeric suffix (for example `mainMenu1`). Enable **Skip existing handles** if you would rather leave those menus untouched.

## Template Mapping

Replace MenuBuilder's Twig call in the template that renders the menu:

```twig
{# MenuBuilder #}
{% set menu = craft.menuBuilder.get('mainMenu') %}

{# Navigation #}
{{ craft.navigation.render('mainMenu') }}
```

The Navigation call renders the migrated menu. If your MenuBuilder template used its node metadata or custom fields, carry that presentation logic over as part of the manual follow-up above.
