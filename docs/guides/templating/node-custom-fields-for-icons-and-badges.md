# Node custom fields for icons and badges

Some designs attach metadata to individual links — icon classes, badge text, short descriptions — without hard-coding handles in Twig. Navigation supports **node custom fields** on each menu's node field layout.

Menu-level promos (shared across the whole panel) use [Menu Fields](/menus/menu-fields) instead.

## Step 1 — Configure the field layout

1. Go to **Navigation → Menus** and edit the menu (for example **Main Menu**).
2. Open the field layout for **nodes** (not Menu Fields).
3. Add fields — common choices:
   - **Plain Text** — badge label (`New`, `Sale`)
   - **Plain Text** — icon class string (`fa fa-home` or your design-system token)
   - **Lightswitch** — `showBadge`
4. Save the menu settings.

Field handles must be unique on the layout. Values are stored per node like any Craft element.

## Step 2 — Fill values in the builder

1. Open the **menu builder**.
2. Edit a node in the slide-out.
3. Set icon or badge values on the node's custom field tab.
4. **Save menu**.

Linked entry nodes can override titles while keeping synced URLs — custom fields are always menu-specific.

## Step 3 — Read fields in Twig

Node fields are available on the node object in loops:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').all() %}

<ul class="nav">
    {% for node in nodes %}
        <li>
            <a href="{{ node.url }}" class="nav__link">
                {% if node.iconClass|default %}
                    <span class="nav__icon {{ node.iconClass }}" aria-hidden="true"></span>
                {% endif %}
                <span class="nav__label">{{ node.title }}</span>
                {% if node.showBadge|default and node.badgeText|default %}
                    <span class="nav__badge">{{ node.badgeText }}</span>
                {% endif %}
            </a>
        </li>
    {% endfor %}
</ul>
```

Replace `iconClass`, `showBadge`, and `badgeText` with your handles.

## Step 4 — Projected Dynamic children

[ProjectedNode](/reference/projected-node) objects **do not** have node custom fields. Only stored `Node` elements do. If icons are required for projected entries, derive them from the linked entry's fields via `child.element`, or use manual nodes instead of projection for those items.

## Migrating from FreeNav

FreeNav's built-in icon and badge properties map cleanly to this pattern. See [Migrating from FreeNav](/guides/migrations-upgrades/migrating-from-freenav).

## Related

- [Build a mega menu](/guides/templating/build-a-mega-menu)
- [Custom Rendering](/templates/custom-rendering)
- [Node](/reference/node)
