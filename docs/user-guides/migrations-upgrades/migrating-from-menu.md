# Migrating from Menu

Navigation can copy menus from [Menu by Rewish](https://plugins.craftcms.com/menu) (`rewi.sh/menu`) on the same Craft site. The migration creates Navigation menus and leaves the source menus intact, so you can compare the results before changing your templates.

## Before You Begin

Install both plugins and run Menu’s database migrations. Back up your database and try the migration on staging before using it on your production site. The importer supports Menu 1.0’s stored menus and nodes; save any pending builder changes in Menu first.

## Copy Your Menus

1. Open **Navigation → Settings**.
2. Under **Migrations**, choose **Menu**.
3. Select the menus to copy, or choose **All**.
4. Enable **Skip existing handles** if you want to leave matching Navigation handles alone.
5. Run the migration and read its results, including warnings about settings that need review.

You can also run the migration from a terminal in your Craft project root:

```bash
php craft navigation/migrate/menu

# Copy selected menus by handle.
php craft navigation/migrate/menu --handle=mainMenu,footerMenu

# Skip handles that already exist in Navigation.
php craft navigation/migrate/menu --skip-existing
```

Without `--skip-existing`, a matching handle creates a separate menu with a numeric suffix, such as `mainMenu1`. Existing Navigation menus are preserved.

## What Is Copied

| Menu content | Navigation result |
| --- | --- |
| Menu title, handle, instructions and nesting limit | Menu settings |
| Enabled sites | Menu site settings |
| Nested nodes and sibling order | Node hierarchy |
| Translated titles and per-site enabled states | Localised node content |
| Disabled menus | Their imported nodes remain disabled |
| Custom URLs, entries, categories, assets and Commerce products | Corresponding node types and linked content |
| Passive and site nodes | Passive and Site nodes |
| Classes, URL suffixes, new-window settings and attributes | Node settings |
| Node field layouts and values | Copied layouts using the same global fields |
| Dynamic source, limit and title sorting | Dynamic node settings |

Missing linked content falls back to the stored URL, or `#` when no URL is available. Missing sites and unavailable dynamic sources become Passive nodes, retaining any manual children. Warnings identify these cases. Original node type, follow-element preference and data are retained under `node.data.migratedMenu` for reference.

## Review the Result

Open each imported menu and compare its hierarchy, links, translations and custom fields with the source. Check every enabled site, including links to content that only exists on another site. Custom field types must remain installed; fields that depend on their original element owner may need manual adjustment.

Dynamic nodes use Navigation’s published-content rules and section/category hierarchy. Menu projects a flat list that includes all content statuses, so dynamic output can differ even with the same source and limit. Review the rendered result before switching templates.

Menu permissions and its follow-element preference do not have a direct migration mapping. Configure Navigation’s menu permissions and check linked titles and enabled states after later changes to the linked content.

Replace Menu’s Twig calls with Navigation’s template API. For example, to render the imported `mainMenu` in your site template:

```twig
{{ craft.navigation.render('mainMenu') }}
```

For your own markup, follow [Custom Rendering](/template-guides/custom-rendering). Keep Menu installed until you have checked the imported content and updated all templates that use it.
