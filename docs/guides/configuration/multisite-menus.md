# Multisite menus

Craft multisite projects need menus enabled on the right sites, with URLs and linked elements scoped per site. Navigation stores per-site link data separately from the node tree.

## Enable menus per site

Each menu has **site settings** in the control panel:

1. Edit the menu under **Navigation → Menus**.
2. On the settings screen, enable or disable the menu for each site.
3. Save.

When **`autoEnableNewSites`** is `true` in `config/navigation.php` (the default), new Craft sites automatically enable all existing menus.

```php
// config/navigation.php
return [
    '*' => [
        'autoEnableNewSites' => true,
    ],
];
```

See [Configuration](/get-started/configuration).

## Edit per-site links in the builder

Node titles can propagate from linked elements, but **URLs**, **urlSuffix**, and **linked element site** are stored per site in `navigation_nodes_sites`.

1. Open the **menu builder**.
2. Switch site using Craft's site menu in the CP header.
3. Edit nodes — link targets and overrides apply to the selected site only.
4. **Save menu**.

The same tree structure is shared; per-site rows control what each site outputs.

## Render on the current site

By default, node queries use the **current site** when Craft renders a front-end request:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}
```

Explicit site scoping when needed:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .site(currentSite)
    .all() %}
```

### All sites in one query

Admin tools or cross-site exports occasionally need every site's variant:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .site('*')
    .unique()
    .all() %}
```

See [Node Queries](/getting-elements/node-queries) for `site` parameter behaviour.

## Site nodes

The **Site** node type links to another Craft site's base URL and marks itself active for pages on that site. Useful for multi-brand headers that jump between sites.

See [Node Types](/menus/node-types).

## Multisite and caching

Navigation cache tags include the site id (`navigation:menu:{uid}:site:{id}`). Saving a menu on one site invalidates that site's cached reads without affecting other sites.

## Multisite and headless

Pass site criteria to `tree()` and GraphQL the same way as node queries:

```twig
{% set tree = craft.navigation.tree({ handle: 'mainMenu', siteId: currentSite.id }) %}
```

## Propagation on the menu

Menu-level **propagation** (in project config / menu settings) controls how new nodes propagate across sites — aligned with Craft element propagation methods (`all`, `none`, site group, language, etc.). Set this when creating the menu if editors should manage one shared tree or site-specific branches.

### Default status on propagated sites

When propagation is not **none**, each menu has an **Enabled On Other Sites By Default** setting. New nodes inherit this in the builder quick-add form (editors can override per node with the **Enabled on other sites** lightswitch). This is useful for site-specific branches in large multisite installs — add the node on one site without enabling it everywhere, then enable it on the sites that need it.

## Title translation

Each menu has a **Title Translation Method** (same options as entry types: per site, site group, language, custom, or not translatable). When you override a node title on one site, Craft copies it to other sites that share the same translation key — for example, all English locales when set to **Translate for each language**.

This is separate from **Propagation Method**, which controls which sites receive the node in the first place.

## Related

- [Overview — Multisite](/menus/overview#multisite)
- [Build a section sidebar with Context API](/guides/templating/build-a-section-sidebar-with-context)
- [Menu Settings](/reference/menu-settings)
