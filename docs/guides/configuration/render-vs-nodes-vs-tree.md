# render() vs nodes() vs tree()

Navigation offers three front-end output paths. They share the same read pipeline and caching rules — the choice is about **output shape**, not raw speed.

## At a glance

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

**Use when:**

- You want nested `<ul>` / `<li>` output without writing loops
- Passive and group nodes should respect `getTag()` automatically
- You do not need linked element fields or node custom fields in the loop

**Avoid when:**

- You need mega-menu panels, grid layouts, or non-list HTML
- You need node custom fields or menu fields in the same partial

See [Rendering Nodes](/templates/rendering-nodes).

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

**Use when:**

- Custom HTML, ARIA, and CSS classes
- Node custom fields or `withLinkedElements()` / `withMenu()` hydration
- Query scoping — `level()`, site criteria, `withProjectedChildren(false)`, etc.

**Avoid when:**

- A one-line render is sufficient and you will not customise markup

Guides: [Build a header menu with dropdowns](/guides/templating/build-a-header-menu-with-dropdowns), [Build a mega menu](/guides/templating/build-a-mega-menu).

## `craft.navigation.tree()`

Nested PHP arrays serialised to JSON for non-Twig consumers.

```twig
{% set tree = craft.navigation.tree('mainMenu') %}
{{ tree|json_encode }}
```

**Use when:**

- A JS front end, mobile app, or static site generator consumes menu data
- You want array keys (`title`, `url`, `children`, active flags) without Twig objects

**Avoid when:**

- You are rendering HTML in Twig — `nodes()` is usually clearer

Opt-in `{ withLinkedElements: true }` when the JSON payload needs entry data. See [Expose a menu as JSON](/guides/frontend-headless/expose-a-menu-as-json-for-a-js-frontend) and [Headless Trees](/frontend/headless-trees).

## Can you mix them?

Yes. A common setup:

- `render()` or `nodes()` in the Craft header partial
- `tree()` on a `/api/navigation/main-menu` route for a decoupled footer or app shell

Use the same menu handle; cache and invalidation apply per API call.

## Performance note

On typical front-end reads, all three use the same cached tree when eligible. Adding `withLinkedElements()` or `withMenu()` on `nodes()` opts out of tree caching for that query. See [The complete guide to Navigation performance](/guides/performance/the-complete-guide-to-navigation-performance).

## Related

- [Rendering Nodes](/templates/rendering-nodes)
- [Headless Trees](/frontend/headless-trees)
- [Node Queries](/getting-elements/node-queries)
