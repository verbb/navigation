# Navigation with Blitz and full-page cache

Full-page cache plugins such as [Blitz](https://putyourlightson.com/plugins/blitz) store entire HTML responses. Navigation maintains its **own** tagged tree cache for menu reads. When a menu changes, both layers may need invalidation.

## How the layers interact

| Layer | What it caches | Invalidates when |
| --- | --- | --- |
| **Navigation tree cache** | Serialised node trees for menu queries | Menu/node save, linked element changes, Dynamic source changes, `invalidateCache()` |
| **Blitz (or similar)** | Full page HTML | Tag-based rules, manual purge, your integration hooks |

Navigation's active/current flags are computed **after** a tree cache hit on each request — so a Blitz-cached page still highlights correctly **as long as the cached HTML is regenerated** when menu **content** (titles, URLs, structure) changes.

If Blitz serves stale HTML after an editor saves a menu, the problem is outer cache invalidation — not Navigation active state.

## Default behaviour

- Navigation **tree cache** mode defaults to `auto` — see [Performance & Caching](/frontend/performance-and-caching).
- Menu and node saves invalidate Navigation cache tags automatically.
- Blitz does not know about Navigation's tags unless you connect them.

## Connect Navigation invalidation to Blitz

Listen for Navigation's cache invalidation event and bust matching Blitz tags (or purge URLs that include the nav).

```php
use putyourlightson\blitz\Blitz;
use verbb\navigation\events\NavigationCacheInvalidationEvent;
use verbb\navigation\services\NavigationCache;
use yii\base\Event;

Event::on(
    NavigationCache::class,
    NavigationCache::EVENT_INVALIDATE,
    function (NavigationCacheInvalidationEvent $event) {
        // Example: refresh cached pages tagged for site headers.
        // Adjust to your Blitz tag strategy.
        Blitz::$plugin->refreshCache->refreshCacheTags(['navigation']);
    }
);
```

Place the listener in a custom module's `init()` method. Tag names should match what your Blitz config uses when caching layouts that render menus.

See [Events — Cache events](/developers/events#cache-events).

## Manual invalidation

From Twig (for example after a custom module saves menu data outside normal CP flows):

::: code
```twig [Twig]
{% do craft.navigation.invalidateCache('mainMenu') %}
```

```php [PHP]
use verbb\navigation\Navigation;

Navigation::$plugin->getNavigationCache()->invalidateByHandle('mainMenu');
```
:::

That clears Navigation's tree cache. Pair it with Blitz refresh logic if pages are still stale.

## Template considerations

- Menus rendered in a global partial included on every cached page share one invalidation scope — tag by menu handle or a shared `navigation` tag.
- Avoid embedding user-specific menu variations in Blitz-cached HTML without [Blitz's dynamic content](https://putyourlightson.com/plugins/blitz) mechanisms — Navigation does not handle user-segment caching for you.

## Dynamic nodes and linked elements

Navigation cache tags include source identifiers for **Dynamic** nodes (`navigation:section:{uid}`, etc.). Linked element updates invalidate affected menus. Ensure Blitz rules also refresh when those tags fire if entries change titles or URLs shown in the nav.

## Debugging stale menus

1. Confirm the menu save succeeded in the CP.
2. Check Navigation cache mode is not `off` unexpectedly in `config/navigation.php`.
3. Call `craft.navigation.invalidateCache('mainMenu')` once — if the nav updates, Navigation cache was fine and Blitz (or CDN) needs hooking up.
4. Review Blitz's cached URL list and tag map for pages that include the header partial.

## Related

- [The complete guide to Navigation performance](/guides/performance/the-complete-guide-to-navigation-performance)
- [Performance & Caching](/frontend/performance-and-caching)
- [Events](/developers/events)
