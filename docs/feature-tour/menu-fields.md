# Menu Fields

Use menu fields for content that belongs to a whole menu, such as a footer tagline or a promotion beside a group of links. Use node fields when the content belongs to one item, such as its icon or badge.

For example, a footer menu can have a Plain Text field called **Tagline**. Editors can update its value alongside the menu without attaching the text to an arbitrary link.

## Configure the Field Layout

Create a Plain Text field named **Tagline** with handle `tagline` under **Settings → Fields** if it does not already exist. The handle is the name you’ll use in Twig.

Edit the menu in **Navigation → Menus**, open **Menu Fields**, and add Tagline to the layout. Save the menu settings. The layout is stored in project config; the values entered for the menu are content and are saved separately.

Open the menu builder’s **Menu Content** tab, enter a tagline, and save. In a multisite project, select the site you want to edit before entering its content. Values can also be edited on the menu settings screen.

## Display the Tagline

Put this code in the Twig partial that displays your menu. It assumes the menu handle is `footerMenu`; replace it with your saved handle:

```twig
{% set menu = craft.navigation.menu('footerMenu').one() %}

{% if menu and menu.tagline %}
    <p>{{ menu.tagline }}</p>
{% endif %}

{{ craft.navigation.render('footerMenu') }}
```

The menu query returns the content-bearing Menu element. Its fields are available by handle, such as `menu.tagline`. The check handles a missing menu or empty tagline. Load the page containing the partial and confirm the tagline appears above its links.

## Read Menu Content While Looping Nodes

Fetch the menu once when its content is only needed outside the node loop. For code that needs a menu attached to each node, add `withMenu()` to the query:

```twig
{% set nodes = craft.navigation.nodes('footerMenu').withMenu().all() %}
{% set menu = nodes[0].menu ?? null %}
```

This batches menu loading but bypasses the node tree cache. See [Performance & Caching](/frontend/performance-and-caching) for choosing between those approaches.

Menu configuration, such as limits and permissions, has a separate PHP object. The [Menu](/reference/menu) and [Menu Settings](/reference/menu-settings) references explain those APIs. For a complete promotional panel, follow [Build a Mega Menu](/user-guides/templating/build-a-mega-menu).
