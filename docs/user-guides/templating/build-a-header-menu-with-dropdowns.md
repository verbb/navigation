# Build a Header Menu with Dropdowns

Build a header with a Shop dropdown and separate About and Contact links. You’ll create the menu in Craft, display it in your shared layout, and check that visitors can open the dropdown with a mouse or keyboard. The Sale link will be marked as the current page on `/shop/sale`.

This example uses HTML’s `details` and `summary` elements for the dropdown. They provide an operable disclosure control without a JavaScript library. Navigation supplies the links and current-page information; the template supplies the layout and interaction.

## Before You Start

You need Navigation [installed](/get-started/installation-setup), permission to create menus, and access to your Craft project’s `templates/` and public `web/` folders. The example assumes your site has working pages at `/shop`, `/shop/new`, `/shop/sale`, `/about`, and `/contact`. Substitute your own page paths if they differ.

A template partial is a file included by another template. We’ll create `templates/_partials/header-nav.twig`, then include it from the layout that surrounds your site’s page content. The leading underscore keeps the partial from being requested as a page itself.

## Create the Menu

Go to **Navigation → Menus**, choose **New menu**, and name it **Main Menu**. Set its handle to `mainMenu`. A handle is the name your Twig code uses to find the menu. Save the settings and open the menu builder.

Add a **Passive** node titled **Shop**. It acts as a heading for the dropdown rather than a destination. Add three **Custom** nodes beneath it, using these titles and URLs:

| Title | URL |
| --- | --- |
| All Products | `/shop` |
| New Arrivals | `/shop/new` |
| Sale | `/shop/sale` |

Add **About** (`/about`) and **Contact** (`/contact`) as top-level Custom nodes. Drag the product links beneath Shop, then click **Save menu**. Your tree should look like this:

```text
Shop
  All Products
  New Arrivals
  Sale
About
Contact
```

For real Craft entries, you can use Entry nodes instead of Custom nodes so their destinations follow the entries. The rendering below works with either type.

## Create the Header Partial

Create `templates/_partials/header-nav.twig` with this complete template:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').level(1).all() %}

<nav class="header-nav" aria-label="Main">
    <ul class="header-nav__list">
        {% for node in nodes %}
            <li class="header-nav__item{{ node.getActive() ? ' is-active' }}">
                {% if node.children|length %}
                    <details class="header-nav__disclosure"{% if node.hasActiveChild() %} open{% endif %}>
                        <summary>{{ node.title }}</summary>
                        <ul class="header-nav__dropdown">
                            {% if node.url %}
                                <li>{{ node.link }}</li>
                            {% endif %}
                            {% for child in node.children %}
                                <li>{{ child.getLink(child.getCurrent() ? { 'aria-current': 'page' } : {}) }}</li>
                            {% endfor %}
                        </ul>
                    </details>
                {% else %}
                    {{ node.getLink(node.getCurrent() ? { 'aria-current': 'page' } : {}) }}
                {% endif %}
            </li>
        {% endfor %}
    </ul>
</nav>
```

The query selects the top level. Navigation loads the child relationships for this menu on frontend requests, so `node.children` gives the dropdown’s items. `node.link` and `getLink()` preserve each item’s target, custom attributes, and link type. The extra `aria-current` attribute identifies the exact current page to assistive technology.

Shop opens initially when one of its descendants is current. Visitors can also open and close it themselves. A parent that has its own URL receives a link inside its dropdown, keeping the disclosure control and the destination separate.

Include the partial where the header belongs in your existing shared layout, for example `templates/_layouts/site.twig`:

```twig
{% include '_partials/header-nav' %}
```

Load a page that uses that layout. Before styling, you should see About, Contact, and a Shop disclosure that opens to show the three product links. If nothing appears, check the saved menu handle and that the menu and its nodes are enabled for the current site.

## Style the Menu

Create `web/css/header-nav.css` with the following CSS. Include it once in your layout’s `<head>` using the link tag below the CSS.

```css
.header-nav__list,
.header-nav__dropdown {
    list-style: none;
    margin: 0;
    padding: 0;
}

.header-nav__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.header-nav__item {
    position: relative;
}

.header-nav a,
.header-nav summary {
    padding: 0.75rem 1rem;
    color: #162534;
}

.header-nav a {
    display: block;
}

.header-nav summary {
    cursor: pointer;
}

.header-nav__dropdown {
    position: absolute;
    z-index: 10;
    min-width: 12rem;
    background: white;
    border: 1px solid #667788;
}

.header-nav .is-active > details > summary,
.header-nav [aria-current='page'] {
    font-weight: 700;
}

.header-nav :focus-visible {
    outline: 3px solid #005fcc;
    outline-offset: 2px;
}

@media (max-width: 40rem) {
    .header-nav__list {
        display: block;
    }

    .header-nav__dropdown {
        position: static;
    }
}
```

```twig
<link rel="stylesheet" href="{{ siteUrl('css/header-nav.css') }}">
```

The desktop dropdown sits below its trigger. On a narrow screen it stays in the page flow so it does not cover the following links. Keep the summary marker: it helps visitors recognise the control as expandable.

## Close with Escape

Enter and Space toggle the focused summary through the browser’s built-in behaviour. Add Escape handling so a visitor inside an open dropdown can close it and return to its trigger.

Create `web/js/header-nav.js`:

```js
document.querySelectorAll('.header-nav__disclosure').forEach((disclosure) => {
    disclosure.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || !disclosure.open) {
            return;
        }

        disclosure.open = false;
        disclosure.querySelector('summary').focus();
        event.preventDefault();
    });
});
```

Load it once from your shared layout:

```twig
<script src="{{ siteUrl('js/header-nav.js') }}" defer></script>
```

## Check the Finished Header

Open `/about`: About should be marked current and Shop should start closed. Tab to Shop and press Enter or Space. The links should appear, and Tab should move through them. Press Escape from a product link: the dropdown should close and focus should return to Shop.

Open `/shop/sale`: Shop should start open and Sale should have `aria-current="page"`. Click the summary to close and reopen it. Resize to a narrow viewport and confirm every link remains reachable. With JavaScript disabled, the summary should still open and close; only the added Escape behaviour is unavailable.

This template renders one dropdown level. For columns and promotional content, continue with [Build a Mega Menu](/user-guides/templating/build-a-mega-menu). For deeper nested lists, use the recursive example in [Rendering Nodes](/template-guides/rendering-nodes).
