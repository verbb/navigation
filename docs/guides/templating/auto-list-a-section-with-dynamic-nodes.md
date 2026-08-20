# Auto-list a section with Dynamic nodes

Manually adding every blog post or product to a menu does not scale. A **Dynamic** node projects matching Craft elements as children at read time — stored manual children appear first, then projected items.

## When to use Dynamic nodes

| Situation | Approach |
| --- | --- |
| Fixed set of links (About, Contact, Services) | Manual **Entry** or **Custom** nodes |
| Entire channel or section that changes often | **Dynamic** node with an **Entries** source |
| Category tree | **Dynamic** with a **Categories** source |
| Asset library links | **Dynamic** with an **Assets** source |

## Step 1 — Add a Dynamic node

1. Open the menu in the **menu builder**.
2. Add a **Dynamic** node where children should appear — for example under a **Passive** parent titled **Blog**.
3. In the slide-out, choose a **source**:
   - **Entries** — pick a section and optional entry conditions
   - **Categories**, **Assets**, or **Products** (Commerce) as needed
4. Set sort order, optional limit (e.g. latest 5 posts), and any source-specific filters.
5. **Save menu**.

Built-in sources are listed in [Node Types](/menus/node-types). Plugins can register more via [Events](/developers/events).

## Step 2 — Expected behaviour

- Manual nodes nested under the Dynamic node render **before** projected children.
- Each projected item is a [ProjectedNode](/reference/projected-node) — not a stored CP node.
- New entries matching the source appear on the next front-end request without editing the menu.
- Deleting the source section (or group, volume, etc.) removes matching Dynamic nodes — see [Events — Linked-element lifecycle](/developers/events).

## Step 3 — Render in Twig

Projected children appear in `node.children` like stored nodes:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

{% for node in nodes %}
    {% if 'Dynamic' in node.type|default %}
        <ul class="blog-list">
            {% for child in node.children %}
                <li>
                    <a href="{{ child.url }}" class="{{ child.getCurrent() ? 'is-current' : '' }}">
                        {{ child.title }}
                    </a>
                </li>
            {% endfor %}
        </ul>
    {% endif %}
{% endfor %}
```

### Tell projected nodes apart

When markup or fields differ for projected vs stored children:

```twig
{% for child in node.children %}
    {% if child.isProjected ?? false %}
        {# Projected entry — no node custom fields #}
        <a href="{{ child.url }}">{{ child.title }}</a>
    {% else %}
        {# Manually placed node — may have custom fields #}
        <a href="{{ child.url }}">{{ child.myBadge }}</a>
    {% endif %}
{% endfor %}
```

Projected nodes support `title`, `url`, active flags, and `element`. They do **not** have node custom fields or CP edit URLs.

## Step 4 — Active state and context

Projected entries participate in active resolution. On a projected blog post URL:

- `node.getCurrent()` is true on the projected child.
- `craft.navigation.getActiveNode({ handle: 'mainMenu' })` can return a `ProjectedNode`.
- `craft.navigation.context('mainMenu').siblings()` includes projected siblings under the same Dynamic parent.

See [Active State & Context](/templates/active-state-and-context).

## Step 5 — Opt out of projection

Skip projected children on a specific query:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withProjectedChildren(false)
    .all() %}
```

Useful when you only want manually curated children for a partial.

## Performance and cache

Dynamic nodes add cache tags for their source (section, category group, volume, or product type). Source changes invalidate cached trees automatically. See [Performance & Caching](/frontend/performance-and-caching) and [Navigation with Blitz](/guides/frontend-headless/navigation-with-blitz-and-full-page-cache).

## Headless output

Projected children appear in `craft.navigation.tree()` with `"isProjected": true`. See [Headless Trees](/frontend/headless-trees) and [Expose a menu as JSON](/guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend).

## Related

- [Projected Node](/reference/projected-node)
- [Build a section sidebar with Context API](/guides/templating/build-a-section-sidebar-with-context)
- [Node Types](/menus/node-types)
