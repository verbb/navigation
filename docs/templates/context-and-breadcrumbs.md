# Context & Breadcrumbs

Navigation provides two breadcrumb strategies and a context resolver for section-style sub-nav. Choose based on whether you need URL structure or menu-tree semantics.

## When to use which

| Need | Use |
| --- | --- |
| SEO trail matching URL segments / entries not in the menu | `urlBreadcrumbs()` |
| “You are here” through the menu tree, mega-menu context, headless menu trail | `menuBreadcrumbs()` |
| Sidebar siblings or branch for the current page | `context()` |

## Context API

```twig
{% set ctx = craft.navigation.context('mainMenu') %}
```

Optional criteria mirror node query scoping (`handle`, `site`, etc.).

Full method list: [Active State & Context](/templates/active-state-and-context).

## URL-segment breadcrumbs

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

## Menu breadcrumbs

`craft.navigation.menuBreadcrumbs(handle)` returns a trail of **nodes** from the menu root to the deepest current node.

```twig
{% for crumb in craft.navigation.menuBreadcrumbs('mainMenu') %}
    <a href="{{ crumb.url }}">{{ crumb.title }}</a>
{% endfor %}
```

Each item includes `title`, `url`, `node`, `current`, and `link` keys. Respects per-site URLs and `urlSuffix`.

Dynamic projected pages can appear in the trail when they match the current URL.

## GraphQL

Headless equivalents: `navigationContext` and `navigationMenuBreadcrumbs`. See [Context & Breadcrumbs](/graphql/context-and-breadcrumbs).
