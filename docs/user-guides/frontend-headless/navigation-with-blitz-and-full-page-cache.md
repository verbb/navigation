# Navigation with Blitz and Full-Page Cache

Full-page cache plugins such as [Blitz](https://putyourlightson.com/plugins/blitz) store entire HTML responses. Navigation maintains its **own** tagged tree cache for menu reads. When a menu changes, both layers may need invalidation.

Start with a working menu and [Blitz configured to cache your pages](https://putyourlightson.com/plugins/blitz). The example assumes you have a bootstrapped custom module where you can register an event handler; [Events](/developers/events) shows that setup.

## How the Layers Interact

| Layer | What it caches | Invalidates when |
| --- | --- | --- |
| **Navigation tree cache** | Serialised node trees for menu queries | Menu/node save, linked element changes, Dynamic source changes, `invalidateCache()` |
| **Blitz (or similar)** | Full page HTML | Tag-based rules, manual purge, your integration hooks |

Navigation's active/current flags are computed **after** a tree cache hit on each request — so a Blitz-cached page still highlights correctly **as long as the cached HTML is regenerated** when menu **content** (titles, URLs, structure) changes.

A cached HTML response keeps the menu produced when that page was generated. Compare it with a fresh origin render before deciding which cache layer is stale.

## Default Behaviour

- Navigation **tree cache** mode defaults to `auto` — see [Performance & Caching](/frontend/performance-and-caching).
- Menu and node saves invalidate Navigation cache tags automatically.
- Blitz does not know about Navigation's tags unless you connect them.

## Connect Navigation Invalidation to Blitz

Add the following Twig line to the shared layout that includes your menu. It attaches a `navigation` tag without replacing other Blitz tags:

```twig
{% do craft.blitz.options.addTags(['navigation']) %}
```

Then put the following registration in your custom module’s `init()` method, with its `use` imports at the top of the PHP file. It refreshes pages carrying that tag when Navigation invalidates menu data:

```php
use putyourlightson\blitz\Blitz;
use verbb\navigation\events\NavigationCacheInvalidationEvent;
use verbb\navigation\services\NavigationCache;
use yii\base\Event;

Event::on(
    NavigationCache::class,
    NavigationCache::EVENT_INVALIDATE,
    function (NavigationCacheInvalidationEvent $event) {
        // Refresh every page using the shared menu layout.
        Blitz::$plugin->refreshCache->refreshCacheTags(['navigation']);
    }
);
```

Generate a cached page after adding the tag. Change a menu title and save, then let the configured Blitz refresh process finish before reloading the page. The title should update. This example uses one shared tag, so any Navigation invalidation can refresh all tagged pages.

See [Events — Cache events](/developers/events#cache-events).

## Manual Invalidation

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

## Template Considerations

- Menus rendered in a global partial included on every cached page share one invalidation scope — tag by menu handle or a shared `navigation` tag.
- Avoid embedding user-specific menu variations in Blitz-cached HTML without [Blitz's dynamic content](https://putyourlightson.com/plugins/blitz) mechanisms — Navigation does not handle user-segment caching for you.

## Dynamic Nodes and Linked Elements

Navigation cache tags include source identifiers for **Dynamic** nodes (`navigation:section:{uid}`, etc.). Linked element updates invalidate affected menus. Ensure Blitz rules also refresh when those tags fire if entries change titles or URLs shown in the nav.

## Debugging Stale Menus

Confirm the save succeeded in the builder, then compare a fresh Craft render with the cached response. In development, bypass Blitz and any CDN using their configured controls so the comparison actually reaches Craft.

If the fresh render is correct but the cached page is old, inspect the page’s Blitz tags, refresh queue, and any CDN cache. If the fresh render is also old, temporarily invalidate Navigation’s menu cache and render again. A change after that invalidation points to the tree cache or its invalidation path; it does not prove the outer cache was responsible.

Remove temporary invalidation calls once the check is complete. Cache mode Off bypasses Navigation’s tree cache and is a valid diagnostic choice; it cannot itself explain stale cached HTML.
