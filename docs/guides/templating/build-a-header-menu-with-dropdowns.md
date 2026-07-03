# Build a header menu with dropdowns

Most site headers need a top-level row of links, with nested items hidden until the user opens a dropdown. This guide walks through building that menu in the control panel and rendering it in Twig with correct active states.

For a one-line output option, see `craft.navigation.render()` in [Rendering Nodes](/templates/rendering-nodes). This guide assumes custom markup.

## What you are building

A menu like:

```
Shop (passive — opens dropdown)
  ├── All products
  ├── New arrivals
  └── Sale
About
Contact
```

On `/shop/sale`, **Sale** is current, **Shop** is active with an open dropdown, and **About** / **Contact** are inactive.

## Step 1 — Create the menu

1. Go to **Navigation → Menus → New menu**.
2. Set name **Main Menu** and handle `mainMenu`.
3. Save and open the **menu builder**.

## Step 2 — Add nodes in the builder

1. Add a **Passive** node titled **Shop**. Passive nodes are labels or dropdown triggers — they do not link anywhere by default.
2. Nest **Entry** or **Custom** nodes under **Shop** for each dropdown link.
3. Add top-level **Entry** or **Custom** nodes for **About** and **Contact**.

Drag to reorder and nest. Click **Save menu** when the structure looks right.

::: tip
Link entry nodes to Craft entries when titles and URLs should follow the entry. Use **Custom** for external URLs or special routes.
:::

## Step 3 — Render in Twig

Fetch top-level nodes and loop children from the wired hierarchy. On front-end reads, Navigation attaches `node.children` in memory — use that property, not `node.children.all()`.

```twig
{# _partials/header-nav.twig #}
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .all() %}

<nav aria-label="Main">
    <ul class="header-nav">
        {% for node in nodes %}
            {% set hasDropdown = node.children|length %}
            <li class="header-nav__item{{ node.getActive() ? ' is-active' }}{{ node.getCurrent() ? ' is-current' }}{{ hasDropdown ? ' has-children' }}{{ node.hasActiveChild() ? ' is-open' }}">
                {% set tag = node.getTag() %}
                <{{ tag }}
                    {% if node.url and tag == 'a' %}href="{{ node.url }}"{% endif %}
                    class="header-nav__link"
                    {% if node.getCurrent() %}aria-current="page"{% endif %}
                    {% if hasDropdown %}aria-haspopup="true" aria-expanded="{{ node.hasActiveChild() ? 'true' : 'false' }}"{% endif %}
                >
                    {{ node.title }}
                </{{ tag }}

                {% if hasDropdown %}
                    <ul class="header-nav__dropdown">
                        {% for child in node.children %}
                            <li class="header-nav__dropdown-item{{ child.getCurrent() ? ' is-current' }}">
                                <a href="{{ child.url }}" class="header-nav__dropdown-link"{% if child.getCurrent() %} aria-current="page"{% endif %}>
                                    {{ child.title }}
                                </a>
                            </li>
                        {% endfor %}
                    </ul>
                {% endif %}
            </li>
        {% endfor %}
    </ul>
</nav>
```

### Active classes

| Class source | Meaning |
| --- | --- |
| `node.getCurrent()` | Exact URL match — use for `aria-current="page"` |
| `node.getActive()` | Current page or active descendant — highlight parent |
| `node.hasActiveChild()` | A child nav node is current — open dropdown |

See [Active State & Context](/templates/active-state-and-context).

### Passive nodes

Passive nodes use `getTag()` → `span` by default. The example above respects that so you do not output an empty `href`.

## Step 4 — Style and behaviour

- Toggle `.is-open` or `[aria-expanded="true"]` in CSS/JS for visible dropdowns.
- Keyboard and hover behaviour are project-specific — Navigation supplies structure and ARIA hooks; your theme handles interaction.

## Variations

**Deeper nesting** — recurse with a macro instead of a single dropdown level. See the recursive macro in [Rendering Nodes](/templates/rendering-nodes).

**Mobile nav** — reuse the same node query in a separate partial with different classes, or output a flat list for a drawer component.

**Quick default markup** — `craft.navigation.render('mainMenu', { hasChildrenClass: 'has-children', activeClass: 'is-active', currentClass: 'is-current' })` when you do not need custom HTML.

## Related

- [Node Types](/menus/node-types) — Passive vs Custom vs Entry
- [Custom Rendering](/templates/custom-rendering)
- [The complete guide to Navigation performance](/guides/performance/the-complete-guide-to-navigation-performance)
