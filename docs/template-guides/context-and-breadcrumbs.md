# Context & Breadcrumbs

Breadcrumbs show a page’s place in a hierarchy. Choose whether that hierarchy follows your URL path or the menu you built in Craft. A News article can have a URL trail even when it has never been added to a menu. The examples belong in the Twig template displaying your page.

## When to Use Which

| Need | Use |
| --- | --- |
| SEO trail matching URL segments / entries not in the menu | `urlBreadcrumbs()` |
| “You are here” through the menu tree, mega-menu context, headless menu trail | `menuBreadcrumbs()` |
| Sidebar siblings or branch for the current page | `context()` |

## Context API

```twig
{% set ctx = craft.navigation.context('mainMenu') %}
```

Pass additional node query criteria as the second argument, for example `craft.navigation.context('mainMenu', { siteId: currentSite.id })`.

For a sidebar example, see [Active State & Context](/template-guides/active-state-and-context). The method list is in the [Context reference](/reference/context).

## URL-Segment Breadcrumbs

`craft.navigation.urlBreadcrumbs()` walks the **current URL segments** and looks up Craft elements for each segment. It is **not** menu-tree aware.

```twig
{% for crumb in craft.navigation.urlBreadcrumbs() %}
    <a href="{{ crumb.url }}">{{ crumb.title }}</a>
{% endfor %}
```

Each `crumb` is an array:

| Key | Description |
| --- | --- |
| `title` | Segment or element title |
| `url` | Absolute URL for the segment |
| `segment` | URL segment string |
| `isElement` | Whether a Craft element matched |
| `element` | The element, if matched |
| `elementId` / `elementType` | Element metadata when matched |

Options: `limit` — maximum number of items.

## Menu Breadcrumbs

`craft.navigation.menuBreadcrumbs(handle)` returns breadcrumb arrays describing the nodes from the menu root to the deepest current node.

```twig
{% for crumb in craft.navigation.menuBreadcrumbs('mainMenu') %}
    <a href="{{ crumb.url }}">{{ crumb.title }}</a>
{% endfor %}
```

Each item includes `title`, `url`, `node`, `current`, and `link` keys. Respects per-site URLs and `urlSuffix`.

Dynamic projected pages can appear in the trail when they match the current URL.

## GraphQL

Headless equivalents: `navigationContext` and `navigationMenuBreadcrumbs`. See [Context & Breadcrumbs](/graphql/context-and-breadcrumbs).
