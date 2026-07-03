# Build a mega menu

Mega menus show a wide panel of links, promos, and sometimes multiple columns under a top-level trigger. Navigation does not ship render presets — you combine **Passive** triggers, **GroupColumn** layout nodes, **menu fields** for promos, and custom Twig.

## What you are building

```
[ Shop ▾ ]
  ┌─────────────────────────────────────────┐
  │ Column 1      │ Column 2    │ Promo     │
  │ • Category A  │ • Sale      │ [image]   │
  │ • Category B  │ • New       │ Shop now  │
  └─────────────────────────────────────────┘
```

## Step 1 — Menu fields for panel promos

1. Edit the menu → **Menu Fields** tab.
2. Add fields for promo content — for example **Assets** (image), **Plain Text** (heading), **URL** (CTA link).
3. Save the menu, then fill values on the **Menu Content** tab (per site).

See [Menu Fields](/menus/menu-fields).

## Step 2 — Node custom fields (optional)

For per-link icons or badges in the mega panel, add fields to the **node** field layout on the same menu. See [Node custom fields for icons and badges](/guides/templating/node-custom-fields-for-icons-and-badges).

## Step 3 — Build the tree

Example structure under **Shop** (Passive trigger):

```
Shop (Passive)
├── GroupColumn
│   ├── Category A (Entry)
│   └── Category B (Entry)
├── GroupColumn
│   ├── Sale (Custom)
│   └── New (Custom)
└── Featured link (Custom)   ← optional full-width row outside columns
```

- **Passive** — top-level trigger (`span`, no `href`).
- **GroupColumn** — structural wrapper with no URL; children render inside a column.
- **Entry / Custom** — actual links inside each column.

Save the menu when the structure is complete.

## Step 4 — Render the mega panel

Query nodes with hierarchy and menu fields. `withMenu()` loads menu field values but disables tree caching for that read — acceptable for a header partial that needs promo data.

```twig
{# _partials/mega-menu.twig #}
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .withMenu()
    .all() %}

{% set menu = nodes[0].menu ?? craft.navigation.menu('mainMenu').one() %}

<nav aria-label="Main">
    <ul class="mega-nav">
        {% for node in nodes %}
            {% if node.children|length %}
                <li class="mega-nav__item{{ node.getActive() ? ' is-active' }}{{ node.hasActiveChild() ? ' is-open' }}">
                    <span class="mega-nav__trigger" aria-haspopup="true" aria-expanded="{{ node.hasActiveChild() ? 'true' : 'false' }}">
                        {{ node.title }}
                    </span>

                    <div class="mega-nav__panel" hidden>
                        <div class="mega-nav__columns">
                            {% for child in node.children %}
                                {% if child.isGroupColumn() %}
                                    <div class="mega-nav__column">
                                        {% for link in child.children %}
                                            <a href="{{ link.url }}" class="mega-nav__link{{ link.getCurrent() ? ' is-current' : '' }}">
                                                {{ link.title }}
                                            </a>
                                        {% endfor %}
                                    </div>
                                {% endif %}
                            {% endfor %}
                        </div>

                        {% if menu and menu.promoImage|default %}
                            <aside class="mega-nav__promo">
                                <img src="{{ menu.promoImage.one().url }}" alt="">
                                {% if menu.promoHeading|default %}
                                    <p>{{ menu.promoHeading }}</p>
                                {% endif %}
                            </aside>
                        {% endif %}
                    </div>
                </li>
            {% else %}
                <li class="mega-nav__item">
                    <a href="{{ node.url }}" class="mega-nav__link">{{ node.title }}</a>
                </li>
            {% endif %}
        {% endfor %}
    </ul>
</nav>
```

Adjust field handles (`promoImage`, `promoHeading`) to match your layout.

### Linked entry data in the panel

When columns show entry thumbnails or summaries:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .withLinkedElements()
    .all() %}
```

See [The complete guide to Navigation performance](/guides/performance/the-complete-guide-to-navigation-performance) for cache trade-offs.

## Step 5 — Accessibility and JS

- Toggle panel visibility with JS; sync `aria-expanded` on the trigger.
- Ensure focus can move into the panel and return on close.
- Passive and group nodes must not output empty anchors — use `getTag()` when building links manually. See [Custom Rendering](/templates/custom-rendering).

## Related

- [Menu Fields](/menus/menu-fields)
- [Node Types](/menus/node-types) — Passive, GroupColumn
- [Build a header menu with dropdowns](/guides/templating/build-a-header-menu-with-dropdowns)
