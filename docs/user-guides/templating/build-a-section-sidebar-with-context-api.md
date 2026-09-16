# Build a Section Sidebar with Context API

Content-heavy sites often show a sidebar listing sibling pages in the current section — every page under **Products**, for example, with the current page highlighted. Navigation's [Context API](/template-guides/active-state-and-context) resolves that branch without manual parent lookups.

## What You Are Building

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

Start with Navigation installed, permission to edit `mainMenu`, and working page URLs for the tree below. You also need access to the Twig template used by those pages.

## Step 1 — Structure the Menu

Build the tree in **Navigation → Menus** so section roots (**Products**, **Services**) sit at the level you want sidebar context to begin. Nest section pages as children.

You do not need a separate menu for sidebars — one `mainMenu` is enough if the sidebar reads from the same tree.

## Step 2 — Choose Siblings or Branch

| Output | Context method | When |
| --- | --- | --- |
| Siblings only | `ctx.siblings()` | List pages alongside the current one under the same parent |
| Full branch | `ctx.branch()` | Ancestors + current + siblings — common “section nav” set |
| Direct children of current | `ctx.children()` | Sub-nav when the current node is a parent |

Most section sidebars use **`siblings()`** or **`branch()`**.

## Step 3 — Render the Sidebar

Create `templates/_partials/section-sidebar.twig` with this code and include it from the section’s page template using `{% include '_partials/section-sidebar' %}`.

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

### Branch Variant

Include the section root and ancestors when the design calls for it:

```twig
{% set items = craft.navigation.context('mainMenu').branch() %}
```

## Step 4 — When the Current URL Has No Exact Node

`context()` uses the same resolution as `getActiveNode()` — the deepest node whose URL **exactly** matches the current page.

If the visitor is on a URL with no matching node (for example a deep entry not added to the menu), siblings may be empty. Options:

1. Replace the `ctx` and `items` assignments at the start of the partial with a branch lookup and its children. This shows pages under the closest matching menu branch when there is no exact current node:

```twig
{% set sectionNode = craft.navigation.getActiveNode({ handle: 'mainMenu' }, true) %}
{% set items = sectionNode ? sectionNode.children : [] %}
```

2. Add the missing page to the menu, or use a **Dynamic** node to project section entries — see [Auto-List a Section with Dynamic Nodes](/user-guides/templating/auto-list-a-section-with-dynamic-nodes).

## Step 5 — Projected Dynamic Children

When the current page is a projected entry from a **Dynamic** node, `context()` and active helpers can return a [ProjectedNode](/reference/projected-node). Sibling lists include stored nodes; projected siblings appear when they share the same Dynamic parent.

## Multisite

Pass criteria when the menu handle is not enough:

```twig
{% set ctx = craft.navigation.context('mainMenu', { siteId: currentSite.id }) %}
```

See [Multisite Menus](/user-guides/configuration/multisite-menus).

## Check the Sidebar

Open `/products/gadgets` and confirm Widgets, Gadgets, and Accessories appear, with Gadgets marked current. Visit `/services/consulting` and confirm the list changes to that branch. Test a URL absent from the menu to check your chosen empty-state or fallback behaviour.
