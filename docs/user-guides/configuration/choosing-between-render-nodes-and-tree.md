# Choosing Between Render(), Nodes(), and Tree()

Navigation offers three front-end output paths. They share the same read pipeline and caching rules — the choice is about **output shape**, not raw speed.

## At a Glance

| API | Returns | Best for |
| --- | --- | --- |
| `craft.navigation.render()` | HTML string (`<ul>` nested list) | Quick default nav, prototypes |
| `craft.navigation.nodes()` | `Node` element objects | Custom markup, mega menus, node/menu fields |
| `craft.navigation.tree()` | Nested arrays | JSON APIs, JavaScript menus, SSG exports |

Active and current flags are resolved on all three for the current request.

## `craft.navigation.render()`

The fastest path to a working menu when standard list markup is enough.

```twig
{{ craft.navigation.render('mainMenu', {
    ulClass: 'nav',
    liClass: 'nav__item',
    aClass: 'nav__link',
    activeClass: 'is-active',
    currentClass: 'is-current',
    hasChildrenClass: 'has-children',
}) }}
```

Choose this when a nested list fits your design and you only need link titles, destinations, and classes. The helper respects each node’s tag, so a Passive node remains a label. For custom fields, promotional panels, or a different HTML structure, query nodes and render them yourself.

See [Rendering Nodes](/template-guides/rendering-nodes).

## `craft.navigation.nodes()`

Full control in Twig with element objects and query parameters.

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .all() %}

<ul>
    {% for node in nodes %}
        <li class="{{ node.getActive() ? 'is-active' : '' }}">
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

Use a node query when your layout needs custom markup, node fields, or related content. Query criteria control which menu, site, and levels you read; loading options supply linked elements or menu fields when needed. The example shows two levels of links. For Passive items and a complete dropdown interaction, follow the header guide below.

Guides: [Build a Header Menu with Dropdowns](/user-guides/templating/build-a-header-menu-with-dropdowns), [Build a Mega Menu](/user-guides/templating/build-a-mega-menu).

## `craft.navigation.tree()`

Nested PHP arrays serialised to JSON for non-Twig consumers.

```twig
{% set tree = craft.navigation.tree('mainMenu') %}
{{ tree|json_encode }}
```

Choose tree output when JavaScript, a mobile app, or a static site generator needs nested data. The arrays include link information and children without requiring the consumer to work with Twig objects. If your output is ordinary server-rendered HTML, `render()` or `nodes()` keeps that work in the template.

Pass `{ withLinkedElements: true }` when the JSON payload needs entry data. See [Expose a menu as JSON](/user-guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend) and [Headless Trees](/frontend/headless-trees).

## Use More Than One Output

A site can use more than one API for the same menu:

- `render()` or `nodes()` in the Craft header partial
- `tree()` on a `/api/navigation/main-menu` route for a decoupled footer or app shell

Use the same menu handle; cache and invalidation apply per API call.

## Performance Note

On typical front-end reads, all three use the same cached tree when eligible. Adding `withLinkedElements()` or `withMenu()` on `nodes()` opts out of tree caching for that query. See [The Complete Guide to Navigation Performance](/user-guides/performance/the-complete-guide-to-navigation-performance).
