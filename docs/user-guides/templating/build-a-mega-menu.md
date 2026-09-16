# Build a Mega Menu

A mega menu groups links into columns and can place a promotional image beside them. You’ll build a Shop panel with two columns and a menu-wide promotion, using a native disclosure control that works with a mouse or keyboard.

Start with the working menu, shared layout, stylesheet, and Escape script from [Build a Header Menu with Dropdowns](/user-guides/templating/build-a-header-menu-with-dropdowns). You also need permission to edit menu settings and configure Craft fields. This guide replaces that header partial; it does not add a second header.

## Add Promotional Fields

Go to **Settings → Fields** and create an Assets field named **Promo Image** with handle `promoImage`, limited to one image from a volume you use for website images. Create two Plain Text fields: **Promo Heading** (`promoHeading`) and **Promo URL** (`promoUrl`). The URL will be a destination such as `/shop/sale`.

Edit **Main Menu** in **Navigation → Menus**. On **Menu Fields**, add these three fields to the layout, then save the menu settings. Open the builder’s **Menu Content** tab for the site you are editing. Choose an image, set the heading to **Shop the Sale**, set the URL to `/shop/sale`, and save.

These values belong to the whole menu. They are suitable for a shared promotion; use node fields instead when every dropdown needs different content.

## Arrange the Columns

Under the existing Shop node, add two **GroupColumn** nodes titled **Browse** and **Offers**. Move All Products and New Arrivals into Browse, and Sale into Offers. Add an optional Custom node called **Featured Collection** with a working URL directly beneath Shop, outside both columns. Save the menu.

```text
Shop (Passive)
  Browse (GroupColumn)
    All Products
    New Arrivals
  Offers (GroupColumn)
    Sale
  Featured Collection (Custom)
About
Contact
```

GroupColumn nodes organise the panel and do not link anywhere. Their titles become column headings. The direct Featured Collection link demonstrates how to include a link outside the columns.

## Replace the Header Partial

Replace `templates/_partials/header-nav.twig` with this template. Keep its existing include in your shared layout:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').level(1).all() %}
{% set menu = craft.navigation.menu('mainMenu').one() %}
{% set promoImage = menu ? menu.promoImage.one() : null %}

<nav class="header-nav" aria-label="Main">
    <ul class="header-nav__list">
        {% for node in nodes %}
            <li class="header-nav__item{{ node.getActive() ? ' is-active' }}">
                {% if node.children|length %}
                    <details class="header-nav__disclosure"{% if node.hasActiveChild() %} open{% endif %}>
                        <summary>{{ node.title }}</summary>
                        <div class="header-nav__mega">
                            <div class="header-nav__columns">
                                {% if node.url %}
                                    <div>{{ node.link }}</div>
                                {% endif %}
                                {% for child in node.children %}
                                    {% if child.isGroupColumn() %}
                                        <section>
                                            <h2>{{ child.title }}</h2>
                                            <ul>
                                                {% for link in child.children %}
                                                    <li>{{ link.getLink(link.getCurrent() ? { 'aria-current': 'page' } : {}) }}</li>
                                                {% endfor %}
                                            </ul>
                                        </section>
                                    {% else %}
                                        <div>{{ child.getLink(child.getCurrent() ? { 'aria-current': 'page' } : {}) }}</div>
                                    {% endif %}
                                {% endfor %}
                            </div>
                            {% if menu and (promoImage or menu.promoHeading) %}
                                <aside class="header-nav__promo">
                                    {% if promoImage %}
                                        <img src="{{ promoImage.url }}" alt="{{ promoImage.alt ?? '' }}">
                                    {% endif %}
                                    {% if menu.promoHeading %}<p>{{ menu.promoHeading }}</p>{% endif %}
                                    {% if menu.promoUrl %}<a href="{{ menu.promoUrl }}">View the Collection</a>{% endif %}
                                </aside>
                            {% endif %}
                        </div>
                    </details>
                {% else %}
                    {{ node.getLink(node.getCurrent() ? { 'aria-current': 'page' } : {}) }}
                {% endif %}
            </li>
        {% endfor %}
    </ul>
</nav>
```

The menu is fetched once for its promotional fields. That lets the separate node query retain its normal cache eligibility. Each GroupColumn becomes a section inside the panel; direct links such as Featured Collection are rendered too. The image is checked before its URL is read, so leaving it empty does not break the header.

## Style the Panel

Append this CSS to `web/css/header-nav.css` from the header guide. Keep the existing disclosure script: it also closes this panel with Escape and restores focus to Shop.

```css
.header-nav__mega {
    position: absolute;
    z-index: 10;
    inset-inline-start: 0;
    width: min(44rem, 85vw);
    padding: 1rem;
    box-sizing: border-box;
    background: white;
    border: 1px solid #667788;
}

.header-nav__columns {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
    gap: 1rem;
}

.header-nav__columns ul {
    list-style: none;
    padding: 0;
}

.header-nav__columns h2 {
    font-size: 1rem;
    margin: 0;
}

.header-nav__promo {
    margin-top: 1rem;
}

.header-nav__promo img {
    display: block;
    max-width: 100%;
    height: auto;
}

@media (max-width: 40rem) {
    .header-nav__mega {
        position: static;
        width: 100%;
    }
}
```

## Check the Panel

Open Shop and confirm both column headings, all links, and the promotion appear. Follow the promotion to check its destination. Tab through the panel, press Escape, and confirm focus returns to Shop. On `/shop/sale`, Shop should start open and Sale should be marked current.

Remove the promotional image and save: the heading and link should still display. Restore it, then check the panel on a narrow screen. If a field is unknown, compare its handle with the three handles created at the start and confirm it is on **Menu Fields**, not **Node Fields**.

For icons or badges on individual links, continue with [Node Custom Fields for Icons and Badges](/user-guides/templating/node-custom-fields-for-icons-and-badges).
