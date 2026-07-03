# The complete guide to Navigation performance

Your header menu probably loads on every page. In development it feels instant; in production, or once the menu grows, the debug toolbar can show far more database queries than you expected — sometimes one per child, sometimes one per linked entry.

Navigation is built to avoid that on typical front-end reads. Most projects never need to tune anything. This guide is for when you are profiling a slow layout, migrating from an older template pattern, or trying to understand **why** the plugin behaves the way it does.

For cache modes and query parameters, see [Performance & Caching](/frontend/performance-and-caching) and [Node Queries](/getting-elements/node-queries).

## Prerequisites

- A menu rendered in a shared partial (header, footer, or mobile nav)
- Craft's debug toolbar or another way to count queries per request
- Basic familiarity with [Rendering Nodes](/templates/rendering-nodes)

## What Navigation optimises by default

On **front-end** menu-scoped reads, Navigation:

1. Fetches nodes for the menu (often one query, sometimes two depending on site scoping).
2. Wires parent/child relationships **in memory** — you do not need Craft's `with(['children'])` for hierarchy in most cases.
3. Caches the tree when **Settings → Navigation → Performance** is in `auto` or `static` mode (the default is `auto`).
4. Resolves **active**, **current**, and **hasActiveChild** flags **after** a cache hit, so highlighting stays correct per request.

The CP, console, and preview requests skip tree caching. Active state is never cached.

If your template only calls `craft.navigation.render('mainMenu')` or loops nodes with `{% nav %}`, you are usually already on the happy path.

## How to measure on your project

1. Load a front-end page that includes the nav partial.
2. Note the total query count in Craft's debug toolbar (or your APM).
3. Comment out the nav partial and reload — the difference is roughly what the menu costs.
4. Change one thing at a time (a flag, a loop pattern) and compare.

The before/after counts in this guide come from Navigation's performance test fixtures (menus with roughly 15–60 nodes). Your numbers will differ, but the **shape** of the improvement — many queries down to a handful — should match when you hit the anti-patterns below.

---

## Chapter 1 — Loading children inside a loop

### Symptom

You fetch top-level nodes, then call `node.children.all()` (or `node.children`) inside a `for` loop to build dropdowns.

### Before — lazy child queries

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .all() %}

<ul>
    {% for node in nodes %}
        <li>
            <a href="{{ node.url }}">{{ node.title }}</a>

            {% set children = node.children.all() %}
            {% if children %}
                <ul>
                    {% for child in children %}
                        <li><a href="{{ child.url }}">{{ child.title }}</a></li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>
    {% endfor %}
</ul>
```

On a menu with five top-level items and three children each, this pattern tends toward **one query for the parents plus one query per parent** when children are accessed — roughly **6+ queries** for hierarchy alone.

### After — let Navigation wire the tree

On front-end reads, hierarchy wiring runs automatically when you omit `withNodeHierarchy(false)`. Use `{% nav %}`, or read children from the wired tree:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .all() %}

<ul>
    {% for node in nodes %}
        <li>
            <a href="{{ node.url }}">{{ node.title }}</a>

            {% if node.children %}
                <ul>
                    {% for child in node.children %}
                        <li><a href="{{ child.url }}">{{ child.title }}</a></li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>
    {% endfor %}
</ul>
```

Or use the `{% nav %}` tag, which expects the same wired hierarchy:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

<ul>
    {% nav node in nodes %}
        <li>
            {{ node.link }}
            {% ifchildren %}<ul>{% children %}</ul>{% endifchildren %}
        </li>
    {% endnav %}
</ul>
```

**Representative result:** the same fixture drops to **about 3 queries or fewer** for the full hierarchy touch — one fetch plus in-memory wiring.

### Why

Navigation loads matching nodes, then attaches `children` arrays in PHP. Calling `children.all()` opts into Craft's lazy element query per parent unless you have explicitly disabled auto hierarchy.

Pass **`withNodeHierarchy(false)`** only when you deliberately want on-demand child queries — for example, a CP or module context that should not batch-load the whole tree.

---

## Chapter 2 — Reading linked elements in a loop

### Symptom

Your mega menu shows entry images, category descriptions, or other data from `node.element` inside the loop.

### Before — lazy element hydration

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

<ul>
    {% for node in nodes %}
        <li>
            <a href="{{ node.url }}">{{ node.title }}</a>

            {% if node.element %}
                {{ node.element.myImage.one() }}
            {% endif %}
        </li>
    {% endfor %}
</ul>
```

Each access to `node.element` can trigger a separate query. A menu with a dozen entry-backed nodes can add **10+ queries** on top of the node fetch.

### After — batch hydration with `withLinkedElements()`

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withLinkedElements()
    .all() %}

<ul>
    {% for node in nodes %}
        <li>
            <a href="{{ node.url }}">{{ node.title }}</a>

            {% if node.element %}
                {{ node.element.myImage.one() }}
            {% endif %}
        </li>
    {% endfor %}
</ul>
```

**Representative result:** the same fixture with mixed node types drops from **many per-node element queries** to **about 3 queries total** for nodes plus linked elements.

### Trade-off

`withLinkedElements()` **disables tree caching** for that query. Use it when your template genuinely reads linked element data. If you only need titles and URLs from the node itself, skip the flag and keep cache eligibility.

`craft.navigation.render()` does not enable this flag — query nodes yourself when you need element fields.

For headless `tree()` output, linked elements are also opt-in: `{ withLinkedElements: true }`. See [Headless Trees](/frontend/headless-trees).

---

## Chapter 3 — Menu custom fields in the same partial

### Symptom

Promo copy, CTA buttons, or panel content live on the **Menu** element (menu fields), and you read them while looping nodes.

### Before — fetching the menu separately or lazily per node

```twig
{% set menu = craft.navigation.menu('mainMenu').one() %}
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

<div class="nav-promo">{{ menu.promoHeading }}</div>
<ul>{# … #}</ul>
```

That works, but some templates accidentally trigger extra menu loads inside loops via `node.getMenu()` without batch hydration.

### After — `withMenu()` on the node query

When menu field values must travel with the node query (and you accept the cache trade-off):

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withMenu()
    .all() %}

{% set menu = nodes[0].menu ?? null %}

{% if menu %}
    <div class="nav-promo">{{ menu.promoHeading }}</div>
{% endif %}
```

Like `withLinkedElements()`, **`withMenu()` disables tree caching** for that read. Prefer a single explicit `craft.navigation.menu()` query when the menu fields are only needed once outside the loop.

See [Menu Fields](/menus/menu-fields).

---

## Chapter 4 — Combining hierarchy and linked elements

Mega menus often need both nested structure **and** entry data on child nodes.

### Before — nested lazy loads

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .all() %}

{% for node in nodes %}
    {% for child in node.children.all() %}
        {% if child.element %}
            {{ child.element.title }}
        {% endif %}
    {% endfor %}
{% endfor %}
```

This combines the child-query problem with the element-query problem.

### After — explicit flags together

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .withNodeHierarchy()
    .withLinkedElements()
    .all() %}

{% for node in nodes %}
    {% for child in node.getChildren() %}
        {% if child.element %}
            {{ child.element.title }}
        {% endif %}
    {% endfor %}
{% endfor %}
```

On front-end reads, hierarchy is often already wired without passing `withNodeHierarchy(true)` — the explicit call documents intent in CP or edge cases. **`withLinkedElements()` is the important addition** when you touch `node.element`.

**Representative result:** combined lazy access on a small nested entry menu can run **10+ queries**; the optimised pattern lands around **4 or fewer** on the same fixture.

---

## Chapter 5 — Tree caching and the second request

With **cache mode `auto`** (default), eligible front-end menu reads store a serialised tree. The first request after a cache miss pays the database cost; subsequent requests reuse the cached structure.

Active and current flags are **always** recomputed for the current URL after a cache hit — so a cached menu still highlights the right branch.

| Cache mode | Typical use |
| --- | --- |
| `off` | Debugging, or you need every read to hit the database |
| `auto` | Default — cache eligible front-end reads |
| `static` | Auto plus longer TTL for mostly-static menus |
| `manual` | Only queries that pass `withNavigationCache()` |

Queries that use `withLinkedElements()` or `withMenu()` are **not** cached.

To bust cache after programmatic menu changes, or when integrating with full-page cache plugins:

```twig
{% do craft.navigation.invalidateCache('mainMenu') %}
```

See [Performance & Caching](/frontend/performance-and-caching) and the `invalidateNavigationCache` event in [Events](/developers/events).

---

## Chapter 6 — Active state is not your performance problem

It is reasonable to worry that resolving `node.getActive()`, `getActiveNode()`, or `craft.navigation.context()` on every request negates caching. It does not — those calculations run **after** the tree loads (or after a cache hit) and are skipped in the CP, console, and preview.

Do not disable caching to fix highlight behaviour. If highlighting is wrong, the fix is in [Active State & Context](/templates/active-state-and-context), not cache mode.

---

## Chapter 7 — `render()`, `nodes()`, and `tree()`

| API | Best for | Notes |
| --- | --- | --- |
| `craft.navigation.render()` | Simple nested `<ul>` output | No linked elements or menu fields; respects `getTag()` for passive nodes |
| `craft.navigation.nodes()` | Custom markup, mega menus, node fields | Full control; add hydration flags when needed |
| `craft.navigation.tree()` | JSON, JS components, static generators | Array output; linked elements opt-in |

On representative fixtures, **`render()` and `tree()` for a 50-node flat menu each use about 2 queries or fewer** on a front-end request with default caching — you do not need to pick one for raw speed alone. Pick based on output shape.

---

## Quick decision guide

| Your template does… | Do this |
| --- | --- |
| Loops nodes and calls `children.all()` per parent | Rely on default hierarchy, or use `{% nav %}` |
| Reads `node.element` fields in the loop | Add `withLinkedElements()` |
| Reads menu custom fields alongside many nodes | Add `withMenu()`, or fetch the menu once outside the loop |
| Uses only titles and URLs | Defaults are enough — no extra flags |
| Needs every DB hit while debugging | Set cache mode to `off` temporarily in Performance settings |
| Integrates with Blitz or similar | Hook `invalidateNavigationCache` or call `invalidateCache()` after menu saves |

## When defaults are enough

You do **not** need to optimise if:

- The menu has modest node counts and renders with `render()` or `{% nav %}`
- You are not reading linked element or menu field data in the loop
- Query counts on a cached page are already low

Reach for flags when profiling shows N+1 patterns tied to children or elements — not preemptively on every project.

## Related

- [Performance & Caching](/frontend/performance-and-caching)
- [Node Queries](/getting-elements/node-queries)
- [Rendering Nodes](/templates/rendering-nodes)
- [Custom Rendering](/templates/custom-rendering)
- [Headless Trees](/frontend/headless-trees)
