# Node Custom Fields for Icons and Badges

Some designs attach metadata to individual links — icon classes, badge text, short descriptions — without hard-coding handles in Twig. Navigation supports **node custom fields** on each menu's node field layout.

Menu-level promos (shared across the whole panel) use [Menu Fields](/feature-tour/menu-fields) instead.

Start with a saved `mainMenu` and a Twig template that displays it. You need permission to edit its settings and create Craft fields. If the fields below do not exist, create them in **Settings → Fields** before adding them to the menu layout.

Use [Custom Rendering](/template-guides/custom-rendering) if you need to control the surrounding menu markup as well as the fields displayed on each node.

## Step 1 — Configure the Field Layout

1. Go to **Navigation → Menus** and edit the menu (for example **Main Menu**).
2. Open **Node Fields**.
3. Add fields — common choices:
   - **Plain Text** — name **Badge Text**, handle `badgeText`, for labels such as `New` or `Sale`
   - **Plain Text** — name **Icon Class**, handle `iconClass`, for a class supplied by your site’s icon stylesheet
   - **Lightswitch** — name **Show Badge**, handle `showBadge`
4. Save the menu settings.

Field handles must be unique on the layout. Values are stored per node like any Craft element.

## Step 2 — Fill Values in the Builder

1. Open the **menu builder**.
2. Edit a node in the slide-out.
3. Set icon or badge values on the node's custom field tab.
4. **Save menu**.

Linked entry nodes can override titles while keeping synced URLs — custom fields are always menu-specific.

## Step 3 — Read Fields in Twig

Create `templates/_partials/menu-badges.twig` with the following code, then include it in your page template with `{% include '_partials/menu-badges' %}`. This example displays a flat list of stored links:

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

## Step 4 — Projected Dynamic Children

[ProjectedNode](/reference/projected-node) objects **do not** have node custom fields. Only stored `Node` elements do. If icons are required for projected entries, derive them from the linked entry's fields via `child.element`, or use manual nodes instead of projection for those items.

## Check the Result

Set Badge Text to `New` on one node, enable Show Badge, save the menu, and load the page containing the partial. The label should appear beside that link. Turn Show Badge off and confirm it disappears. An icon class only draws an icon if your site loads a stylesheet defining it; Navigation does not supply the icon font.

If you see an unknown-field error, confirm the handles and the **Node Fields** layout. Use Menu Fields only for content shared by the whole menu.
