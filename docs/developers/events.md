# Events

## Menu Events

### The `beforeSaveMenu` event
The event that is triggered before a menu is saved.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_BEFORE_SAVE_MENU, function (MenuEvent $event) {
    $menu = $event->menu;
    $isNew = $event->isNew;
    // ...
});
```

### The `afterSaveMenu` event
The event that is triggered after a menu is saved.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_AFTER_SAVE_MENU, function (MenuEvent $event) {
    $menu = $event->menu;
    // ...
});
```

### The `beforeDeleteMenu` event
The event that is triggered before a menu is deleted.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_BEFORE_DELETE_MENU, function (MenuEvent $event) {
    $menu = $event->menu;
    // ...
});
```

### The `beforeApplyMenuDelete` event
The event that is triggered before a menu delete is applied from project config.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_BEFORE_APPLY_MENU_DELETE, function (MenuEvent $event) {
    $menu = $event->menu;
    // ...
});
```

### The `afterDeleteMenu` event
The event that is triggered after a menu is deleted.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_AFTER_DELETE_MENU, function (MenuEvent $event) {
    $menu = $event->menu;
    // ...
});
```

## Node Events

### The `beforeSaveNode` event
The event that is triggered before a node is saved. You can set `$event->isValid` to `false` to prevent saving.

```php
use craft\events\ModelEvent;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_BEFORE_SAVE, function (ModelEvent $event) {
    $node = $event->sender;
    $event->isValid = false;
    // ...
});
```

### The `afterSaveNode` event
The event that is triggered after a node is saved.

```php
use craft\events\ModelEvent;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_AFTER_SAVE, function (ModelEvent $event) {
    $node = $event->sender;
    // ...
});
```

### The `modifyNodeActive` event
The event that is triggered when resolving whether a node is active. Modify the `isActive` event property to change the result.

This event is fired from `Node::getActive()`.

```php
use verbb\navigation\elements\Node;
use verbb\navigation\events\NodeActiveEvent;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_NODE_ACTIVE, function (NodeActiveEvent $event) {
    $node = $event->node;
    $event->isActive = true;
    // ...
});
```

## Structure Events

### The `beforeMoveElement` event
The event that is triggered before an element is moved in its structure.

When handling navigation nodes, check that `$event->element` is an instance of `Node`.

```php
use craft\events\MoveElementEvent;
use craft\services\Structures;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Structures::class, Structures::EVENT_BEFORE_MOVE_ELEMENT, function (MoveElementEvent $event) {
    $element = $event->element;

    if ($element instanceof Node) {
        // ...
    }
});
```

### The `afterMoveElement` event
The event that is triggered after an element is moved in its structure.

```php
use craft\events\MoveElementEvent;
use craft\services\Structures;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Structures::class, Structures::EVENT_AFTER_MOVE_ELEMENT, function (MoveElementEvent $event) {
    $element = $event->element;

    if ($element instanceof Node) {
        // ...
    }
});
```

## Node Type Events

### The `registerNodeTypes` event
The event that is triggered when registering node types for the menu builder.

For more examples, see [Node Types](/developers/node-types).

```php
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, function (RegisterNodeTypeEvent $event) {
    $event->types[] = \modules\sitemodule\nodetypes\Group::class;
    // ...
});
```

## Dynamic Source Events

### The `registerDynamicSources` event
The event that is triggered when registering dynamic sources for **Dynamic** nodes. Add provider class names to the `providers` array.

```php
use verbb\navigation\base\DynamicSourceProvider;
use verbb\navigation\elements\Node;
use verbb\navigation\events\RegisterDynamicSourceEvent;
use verbb\navigation\models\ProjectedNode;
use verbb\navigation\services\DynamicSources;
use yii\base\Event;

Event::on(DynamicSources::class, DynamicSources::EVENT_REGISTER_DYNAMIC_SOURCES, function (RegisterDynamicSourceEvent $event) {
    $event->providers[] = MyEntrySectionSource::class;
});
```

Each provider implements `DynamicSourceProvider` (prefer extending `DynamicSource`, which derives `displayName()` from `elementType()::pluralDisplayName()`). Provide a unique `handle()` (stored on the node as `data.dynamicSource`), builder schema fields, slide-out HTML, validation, projection logic, and cache tags. See `EntrySectionDynamicSource` in `src/dynamic/sources/` for a full example.

## Cache Events

### The `invalidateNavigationCache` event
The event that is triggered when Navigation cache tags are invalidated — for example after a menu or node save, or when calling `craft.navigation.invalidateCache()`.

```php
use verbb\navigation\events\NavigationCacheInvalidationEvent;
use verbb\navigation\services\NavigationCache;
use yii\base\Event;

Event::on(NavigationCache::class, NavigationCache::EVENT_INVALIDATE, function (NavigationCacheInvalidationEvent $event) {
    $tags = $event->tags;
    $menuUid = $event->menuUid;
    // ...
});
```

```twig
{% do craft.navigation.invalidateCache('mainMenu') %}
```

Cache tags include `navigation:menu:{uid}`, `navigation:menu:{uid}:site:{id}`, `navigation:node:{id}`, and source tags such as `navigation:section:{uid}`, `navigation:categoryGroup:{uid}`, `navigation:volume:{uid}`, and `navigation:productType:{uid}` for Dynamic nodes.

## Linked-Element Lifecycle

Navigation keeps linked nodes in sync when Craft elements change. These behaviours are built in — you do not need to register event handlers for them.

| Linked element event | Node behaviour |
| --- | --- |
| Soft-deleted | Node is **disabled** (not deleted); prior enabled state is stored |
| Restored from trash | Node is **re-enabled** to its prior state |
| Hard-deleted | Linked node is **deleted** |
| Source deleted (Dynamic nodes) | Matching Dynamic node is **deleted** (section, category group, volume, or product type) |

Entry title changes sync to linked nodes **only when the node title still mirrors the element title**. Custom per-site node titles are preserved.
