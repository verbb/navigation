# Performance & Caching

Start with Navigation’s defaults when displaying a menu. Menu queries on frontend requests load parent and child relationships together and can reuse a cached tree. You generally only need additional loading options when your template reads custom data from linked entries or from the menu itself.

For a walkthrough of measuring a menu and choosing those options, see [The Complete Guide to Navigation Performance](/user-guides/performance/the-complete-guide-to-navigation-performance).

## Load Nested Links

This template displays a menu’s top level and its direct children. Put it in the partial where the menu belongs, replacing `mainMenu` with your saved menu handle:

```twig
{% set nodes = craft.navigation.nodes('mainMenu').level(1).all() %}

<ul>
    {% for node in nodes %}
        <li>
            {{ node.link }}
            {% if node.children|length %}
                <ul>
                    {% for child in node.children %}
                        <li>{{ child.link }}</li>
                    {% endfor %}
                </ul>
            {% endif %}
        </li>
    {% endfor %}
</ul>
```

For this menu-scoped frontend query, Navigation loads the hierarchy automatically. `node.children` is then a collection of loaded elements. Calling `.all()` on that collection returns its items; it does not turn it back into a database query.

When hierarchy loading is disabled with `withNodeHierarchy(false)`, child access can instead return an element query and executing it can query the database. The distinction is whether the hierarchy is loaded, not whether your Twig expression ends in `.all()`.

You can also use Craft’s `{% nav %}` tag for recursive markup. It does not enable hierarchy loading itself. See [Rendering Nodes](/template-guides/rendering-nodes).

## Load Linked Entries or Menu Fields

A node already supplies its title and URL. If your design also reads `node.element`, use `withLinkedElements()` to load the linked Craft elements together. If it reads menu field values through many nodes, `withMenu()` loads their menus together.

| Query option | Use when | Tree cache |
| --- | --- | --- |
| `withLinkedElements()` | You read fields from linked entries, categories, assets, or products | Bypassed |
| `withMenu()` | You read menu field values through the returned nodes | Bypassed |
| `withNavigationCache()` | Cache mode is Manual and this query should use the cache | Eligible queries can be cached |

For example, add `withLinkedElements()` to a query before reading a linked entry’s fields. It loads the linked entry itself; any relational fields on that entry, such as an Assets field, can need their own eager-loading strategy.

When promotional content is needed once above the menu, fetch `craft.navigation.menu('mainMenu').one()` separately. This keeps the node query eligible for tree caching. See [Menu Fields](/feature-tour/menu-fields).

The second argument to `tree()` is an output-options array. Its `withLinkedElements` option loads linked elements after the node fetch and does not itself bypass the underlying tree cache. This differs from setting the flag on the node query. See [Headless Trees](/frontend/headless-trees).

## Choose a Cache Mode

Open **Navigation → Settings → Performance** to choose how Navigation caches trees:

| Mode | Behaviour |
| --- | --- |
| Off | Fetch nodes without the plugin tree cache |
| Auto | Cache eligible frontend menu queries; the default |
| Static | Cache eligible queries using the configured duration |
| Manual | Cache eligible queries only when they opt in with `withNavigationCache()` |

Caching is limited to supported menu-scoped query shapes. Additional filters such as search or arbitrary ordering can bypass it. Queries that request linked elements or menus through loading flags also bypass it. Control-panel, console, and preview reads do not use the public tree cache.

Active and current flags are resolved for the current request after the tree loads, including after a cache hit. Cached structure can therefore be reused across pages without reusing their highlights. An outer HTML cache still stores the rendered highlights for its own page URL.

## Refresh Cached Menus

Normal menu and node saves invalidate Navigation’s cache automatically. Linked content and Dynamic source changes also trigger invalidation. To clear a menu explicitly while diagnosing a stale result, run this temporarily in a Twig template:

```twig
{% do craft.navigation.invalidateCache('mainMenu') %}
```

Remove the call after checking the result; leaving it in a shared partial clears the cache on every render. The PHP equivalent, for a custom module, is:

```php
use verbb\navigation\Navigation;

Navigation::$plugin->getNavigationCache()->invalidateByHandle('mainMenu');
```

Full-page caches and CDNs are separate layers. See [Navigation with Blitz and Full-Page Cache](/user-guides/frontend-headless/navigation-with-blitz-and-full-page-cache) for connecting invalidation and diagnosing which layer is stale. The [cache event reference](/developers/events#cache-events) lists the event payload and tag formats.

## Explicit Hierarchy Loading

Outside normal frontend rendering, use `withNodeHierarchy()` when you need Navigation to load relationships. If you deliberately disable it, Craft eager-loading remains available:

```twig
{% set nodes = craft.navigation.nodes('mainMenu')
    .level(1)
    .withNodeHierarchy(false)
    .with(['children'])
    .all() %}
```

This loads direct children through Craft. It is not needed for the default frontend query shown above.
