# Build a section sidebar with Context API

Content-heavy sites often show a sidebar listing sibling pages in the current section — every page under **Products**, for example, with the current page highlighted. Navigation's Context API resolves that branch without manual parent lookups.

## What you are building

Given a menu:

```
Products
  ├── Widgets
  ├── Gadgets
  └── Accessories
Services
  └── Consulting
```

On `/products/gadgets`, the sidebar lists **Widgets**, **Gadgets**, and **Accessories**, with **Gadgets** marked current.

## Step 1 — Structure the menu

Build the tree in **Navigation → Menus** so section roots (**Products**, **Services**) sit at the level you want sidebar context to begin. Nest section pages as children.

You do not need a separate menu for sidebars — one `mainMenu` is enough if the sidebar reads from the same tree.

## Step 2 — Choose siblings or branch

| Output | Context method | When |
| --- | --- | --- |
| Siblings only | `ctx.siblings()` | List pages alongside the current one under the same parent |
| Full branch | `ctx.branch()` | Ancestors + current + siblings — common “section nav” set |
| Direct children of current | `ctx.children()` | Sub-nav when the current node is a parent |

Most section sidebars use **`siblings()`** or **`branch()`**.

## Step 3 — Render the sidebar

```twig
{# _partials/section-sidebar.twig #}
{% set ctx = craft.navigation.context('mainMenu') %}
{% set items = ctx.siblings() %}

{% if items|length %}
    <nav aria-label="Section" class="section-sidebar">
        <ul>
            {% for node in items %}
                <li class="{{ node.getCurrent() ? 'is-current' : '' }}">
                    <a href="{{ node.url }}"{% if node.getCurrent() %} aria-current="page"{% endif %}>
                        {{ node.title }}
                    </a>
                </li>
            {% endfor %}
        </ul>
    </nav>
{% endif %}
```

### Branch variant

Include the section root and ancestors when the design calls for it:

```twig
{% set items = craft.navigation.context('mainMenu').branch() %}
```

## Step 4 — When the current URL has no exact node

`context()` uses the same resolution as `getActiveNode()` — the deepest node whose URL **exactly** matches the current page.

If the visitor is on a URL with no matching node (for example a deep entry not added to the menu), siblings may be empty. Options:

1. Pass branch fallback when fetching context-related data elsewhere:

```twig
{% set sectionNode = craft.navigation.getActiveNode({ handle: 'mainMenu' }, true) %}
```

2. Add the missing page to the menu, or use a **Dynamic** node to project section entries — see [Auto-list a section with Dynamic nodes](/guides/templating/auto-list-a-section-with-dynamic-nodes).

## Step 5 — Projected Dynamic children

When the current page is a projected entry from a **Dynamic** node, `context()` and active helpers can return a [ProjectedNode](/reference/projected-node). Sibling lists include stored nodes; projected siblings appear when they share the same Dynamic parent.

## Multisite

Pass criteria when the menu handle is not enough:

```twig
{% set ctx = craft.navigation.context({ handle: 'mainMenu', siteId: currentSite.id }) %}
```

See [Multisite menus](/guides/configuration/multisite-menus).

## Related

- [Active State & Context](/templates/active-state-and-context)
- [Context & Breadcrumbs](/templates/context-and-breadcrumbs)
- [Build a header menu with dropdowns](/guides/templating/build-a-header-menu-with-dropdowns)
