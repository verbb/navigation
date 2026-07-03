# Performance & Caching

Navigation reduces query count on front-end menu reads through in-memory hierarchy wiring, optional batch hydration, and tagged tree caching.

For typical `render()` and `nodes()` usage, the default settings are enough. Tune performance when menus are large, heavily nested, or integrated with full-page cache plugins like [Blitz](https://putyourlightson.com/plugins/blitz).

::: tip User guide
For before-and-after template examples and a walkthrough of when each flag matters, see [The complete guide to Navigation performance](/guides/performance/the-complete-guide-to-navigation-performance).
:::

## Automatic hierarchy

On **front-end** menu-scoped reads, Navigation wires parent/child relationships in memory after the query executes. That happens on the node query — not because you use Craft's `{% nav %}` tag.

::: code
```twig [For loop]
{% for node in nodes %}
    {{ node.link }}

    {% if node.children %}
        <ul>
            {% for child in node.children %}
                <li>{{ child.link }}</li>
            {% endfor %}
        </ul>
    {% endif %}
{% endfor %}
```

```twig [{% nav %}]
{% nav node in nodes %}
    {{ node.link }}
    {% ifchildren %}<ul>{% children %}</ul>{% endifchildren %}
{% endnav %}
```
:::

Use **`node.children`** (or `node.getChildren()`) — not **`node.children.all()`**, which opts back into per-parent queries. You do **not** need Craft's `with(['children'])` for node hierarchy in most cases. Pass **`withNodeHierarchy(false)`** only if you want on-demand child queries.

The `{% nav %}` tag works with wired hierarchies, but it carries Craft's own tree-walking rules (level limits, `{% ifchildren %}`, and so on). A plain `for` loop is often simpler when you control the markup. See [Rendering Nodes](/templates/rendering-nodes).

## Opt-in hydration

| Flag | Loads | Disables tree cache |
| --- | --- | --- |
| `withLinkedElements()` | Linked Craft elements (`node.element`) | Yes |
| `withMenu()` | Parent Menu element + menu custom fields | Yes |
| `withNavigationCache()` | — | No (opts **in** when cache mode is Manual) |

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .withLinkedElements()
    .withMenu()
    .all() %}
```

`craft.navigation.render()` does not enable these flags — query nodes yourself when templates read linked element or menu fields.

Parameter details: [Node Queries](/getting-elements/node-queries).

## Tree caching

**Settings → Navigation → Performance** controls cache behaviour:

| Mode | Behaviour |
| --- | --- |
| `off` | No plugin tree cache |
| `auto` | Cache eligible front-end menu-scoped reads (default) |
| `static` | Auto + longer TTL |
| `manual` | Only queries with `withNavigationCache()` |

**Never cached:** active/current flags (always resolved after a cache hit), CP/console reads, queries with `withLinkedElements()` or `withMenu()`.

### Invalidate cache

After programmatic menu changes, or for Blitz integration:

::: code
```twig [Twig]
{% do craft.navigation.invalidateCache('mainMenu') %}
```

```php [PHP]
use verbb\navigation\Navigation;

Navigation::$plugin->getNavigationCache()->invalidateByHandle('mainMenu');
```
:::

Listen for invalidation (for example, to bust full-page cache tags):

```php
use verbb\navigation\services\NavigationCache;
use yii\base\Event;

Event::on(NavigationCache::class, NavigationCache::EVENT_INVALIDATE, function ($event) {
    // React to cache busts
});
```

Cache tags include `navigation:menu:{uid}`, `navigation:menu:{uid}:site:{id}`, `navigation:node:{id}`, and source tags such as `navigation:section:{uid}`, `navigation:categoryGroup:{uid}`, `navigation:volume:{uid}`, and `navigation:productType:{uid}` for Dynamic nodes.

## Craft eager-loading

If you opt out of auto hierarchy with `withNodeHierarchy(false)`, you can still use Craft's element eager-loading:

```twig
{% set nodes = craft.navigation.nodes()
    .handle('mainMenu')
    .level(1)
    .withNodeHierarchy(false)
    .with(['children'])
    .all() %}
```

This pattern is rarely needed on front-end reads when auto hierarchy is enabled.
