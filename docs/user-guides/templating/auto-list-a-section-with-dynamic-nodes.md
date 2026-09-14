# Auto-List a Section with Dynamic Nodes

Use a Dynamic node to keep a list of blog posts up to date as you publish content. This example lists up to five posts from a Blog channel without adding a stored menu item for each one. Navigation generates the matching children when the menu is read; these are called projected nodes.

Start with a saved `mainMenu`, a Blog channel containing at least two live entries with working page URLs, and access to its builder and your Twig templates. You need permission to view entries in that section. If the section is missing from the source picker, check your permissions and the menu’s Permissions settings.

## When to Use Dynamic Nodes

| Situation | Approach |
| --- | --- |
| Fixed set of links (About, Contact, Services) | Manual **Entry** or **Custom** nodes |
| Entire channel or section that changes often | **Dynamic** node with an **Entries** source |
| Category tree | **Dynamic** with a **Categories** source |
| Asset library links | **Dynamic** with an **Assets** source |

## Step 1 — Add a Dynamic Node

1. Open the menu in the **menu builder**.
2. Add a **Dynamic** node where children should appear — for example under a **Passive** parent titled **Blog**.
3. In the slide-out, choose a **source**:
   - **Entries** — pick a section and optional entry conditions
   - **Categories**, **Assets**, or **Products** (Commerce) as needed
4. Select the Blog section and set the limit to `5`. Choose a sort order appropriate to your list.
5. **Save menu**.

Built-in sources are listed in [Node Types](/feature-tour/node-types). Plugins can register more via [Events](/developers/events).

## Step 2 — Expected Behaviour

- Manual nodes nested under the Dynamic node render **before** projected children.
- Each projected item is a [ProjectedNode](/reference/projected-node) — not a stored CP node.
- New entries matching the source appear on the next front-end request without editing the menu.
- Deleting the source section (or group, volume, etc.) removes matching Dynamic nodes — see [Events — Linked-element lifecycle](/developers/events).

## Step 3 — Render in Twig

Create `template-guides/_partials/blog-menu.twig` with this code and include it in your page template using `{% include '_partials/blog-menu' %}`. Projected children appear in `node.children` like stored nodes:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

{% for node in nodes %}
    {% if node.type == 'verbb\\navigation\\nodetypes\\Dynamic' %}
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

### Tell Projected Nodes Apart

When markup or fields differ for projected vs stored children:

```twig
{% for child in node.children %}
    {% if child.isProjected ?? false %}
        {# Projected entry — no node custom fields #}
        <a href="{{ child.url }}">{{ child.title }}</a>
    {% else %}
        {# Manually placed node — may have custom fields #}
        <a href="{{ child.url }}">{{ child.title }}</a>
    {% endif %}
{% endfor %}
```

Projected nodes support `title`, `url`, active flags, and `element`. They do **not** have node custom fields or CP edit URLs.

## Check the List

Load the page containing the partial. The Blog entries should appear in the configured order, up to the chosen limit. Publish another matching entry and reload; it should be included if it falls within that limit. If no entries appear, confirm the source section, live status, selected site, and that you saved the menu.

## Step 4 — Active State and Context

Projected entries participate in active resolution. On a projected blog post URL:

- `node.getCurrent()` is true on the projected child.
- `craft.navigation.getActiveNode({ handle: 'mainMenu' })` can return a `ProjectedNode`.
- `craft.navigation.context('mainMenu').siblings()` includes projected siblings under the same Dynamic parent.

See [Active State & Context](/template-guides/active-state-and-context).

## Step 5 — Opt Out of Projection

Skip projected children on a specific query:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withProjectedChildren(false)
    .all() %}
```

Useful when you only want manually curated children for a partial.

## Performance and Cache

Dynamic nodes add cache tags for their source (section, category group, volume, or product type). Source changes invalidate cached trees automatically. See [Performance & Caching](/frontend/performance-and-caching) and [Navigation with Blitz](/user-guides/frontend-headless/navigation-with-blitz-and-full-page-cache).

## Headless Output

Projected children appear in `craft.navigation.tree()` with `"isProjected": true`. See [Headless Trees](/frontend/headless-trees) and [Expose a menu as JSON](/user-guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend).
