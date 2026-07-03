# Breadcrumbs: URL trail vs menu trail

Breadcrumbs answer “where am I?” — but the right answer depends on whether you mean **URL structure** or **position in the menu tree**. Navigation provides both.

## Quick decision

| Question | Use |
| --- | --- |
| Should crumbs match URL segments and entries, even when those pages are not in the menu? | `urlBreadcrumbs()` |
| Should crumbs follow the menu the visitor clicked through (including mega-menu context)? | `menuBreadcrumbs()` |
| Need sibling links in a sidebar, not a trail? | `context()` — see [Build a section sidebar](/guides/templating/build-a-section-sidebar-with-context) |

## Example site

**Menu tree (`mainMenu`):**

```
Shop → Sale → Clearance
About
```

**Current URL:** `/blog/my-post` (Blog entry **not** in the menu)

| API | Typical trail |
| --- | --- |
| `urlBreadcrumbs()` | Home → Blog → My post |
| `menuBreadcrumbs('mainMenu')` | *(empty or no match — page not in menu)* |

**Current URL:** `/shop/sale/clearance` (all nodes exist in menu)

| API | Typical trail |
| --- | --- |
| `urlBreadcrumbs()` | Home → Shop → Sale → Clearance *(if segments resolve to elements)* |
| `menuBreadcrumbs('mainMenu')` | Shop → Sale → Clearance |

## URL-segment breadcrumbs

Walks the current URL path and resolves Craft elements per segment. Not menu-aware.

```twig
<nav aria-label="Breadcrumb">
    <ol class="breadcrumbs">
        <li><a href="/">Home</a></li>
        {% for crumb in craft.navigation.urlBreadcrumbs() %}
            <li>
                {% if loop.last %}
                    <span aria-current="page">{{ crumb.title }}</span>
                {% else %}
                    <a href="{{ crumb.url }}">{{ crumb.title }}</a>
                {% endif %}
            </li>
        {% endfor %}
    </ol>
</nav>
```

Each `crumb` includes `title`, `url`, `segment`, `isElement`, and optional `element`. Pass `{ limit: 4 }` to cap length.

**Best for:** SEO-style trails, blog hierarchies, URLs that do not mirror the main nav.

## Menu breadcrumbs

Returns nodes from the menu root to the deepest **current** node for this request.

```twig
<nav aria-label="Breadcrumb">
    <ol class="breadcrumbs">
        {% for crumb in craft.navigation.menuBreadcrumbs('mainMenu') %}
            <li>
                {% if crumb.current %}
                    <span aria-current="page">{{ crumb.title }}</span>
                {% else %}
                    <a href="{{ crumb.url }}">{{ crumb.title }}</a>
                {% endif %}
            </li>
        {% endfor %}
    </ol>
</nav>
```

Each item includes `title`, `url`, `current`, `node`, and `link`.

**Best for:** “You are here” in the primary nav, mega-menu context, headless menu trails.

## Dynamic projected pages

When the current URL matches a **Dynamic** projected entry, both APIs can include it — `menuBreadcrumbs()` when that entry appears under a Dynamic parent in the menu tree; `urlBreadcrumbs()` when the segment resolves to the entry.

## Using both on one site

Common pattern:

- **Main marketing pages** — `menuBreadcrumbs('mainMenu')` in the header partial.
- **Blog / knowledge base** — `urlBreadcrumbs()` in the article layout.

They are not mutually exclusive — pick per template.

## GraphQL

Headless equivalents: `navigationContext` and `navigationMenuBreadcrumbs`. See [Context & Breadcrumbs](/graphql/context-and-breadcrumbs).

## Related

- [Context & Breadcrumbs](/templates/context-and-breadcrumbs)
- [Auto-list a section with Dynamic nodes](/guides/templating/auto-list-a-section-with-dynamic-nodes)
