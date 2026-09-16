# Events
Navigation provides a collection of events for extending its functionality. Modules and plugins can register event listeners, typically in their `init()` methods, to modify Navigation’s behaviour.

## Menu Events

Menu events provide a [Menu Settings](/reference/menu-settings) object through `$event->menu`. They concern menu configuration, rather than individual node edits or menu field values. `MenuEvent` does not support cancellation through `isValid`.

### The `beforeSaveMenu` Event
The event that is triggered before menu configuration is validated and saved.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_BEFORE_SAVE_MENU, function(MenuEvent $event) {
    $menu = $event->menu;
    $isNew = $event->isNew;
    \Craft::info(($isNew ? 'Creating' : 'Updating') . " menu {$menu->handle}.", __METHOD__);
});
```

### The `afterSaveMenu` Event
The event that is triggered after menu configuration has been saved.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_AFTER_SAVE_MENU, function(MenuEvent $event) {
    $menu = $event->menu;
    $isNew = $event->isNew;
    \Craft::info(($isNew ? 'Creating' : 'Updating') . " menu {$menu->handle}.", __METHOD__);
});
```

### The `beforeDeleteMenu` Event
The event that is triggered before a menu deletion is requested.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_BEFORE_DELETE_MENU, function(MenuEvent $event) {
    $menu = $event->menu;
    \Craft::info("Deleting menu {$menu->handle}.", __METHOD__);
});
```

### The `beforeApplyMenuDelete` Event
The event that is triggered before a menu deletion is applied from project config.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_BEFORE_APPLY_MENU_DELETE, function(MenuEvent $event) {
    $menu = $event->menu;
    \Craft::info("Applying deletion of menu {$menu->handle}.", __METHOD__);
});
```

### The `afterDeleteMenu` Event
The event that is triggered after a menu has been deleted.

```php
use verbb\navigation\events\MenuEvent;
use verbb\navigation\services\Menus;
use yii\base\Event;

Event::on(Menus::class, Menus::EVENT_AFTER_DELETE_MENU, function(MenuEvent $event) {
    $menu = $event->menu;
    \Craft::info("Deleted menu {$menu->handle}.", __METHOD__);
});
```

## Node Events

### The `beforeSave` Event
The event that is triggered before a node is saved. Read the node from `$event->sender`; set `$event->isValid` to `false` to prevent the save.

```php
use craft\events\ModelEvent;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_BEFORE_SAVE, function(ModelEvent $event) {
    $node = $event->sender;
    \Craft::info("Saving node {$node->title}.", __METHOD__);
});
```

### The `afterSave` Event
The event that is triggered after a node is saved.

```php
use craft\events\ModelEvent;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_AFTER_SAVE, function(ModelEvent $event) {
    $node = $event->sender;
    \Craft::info("Saved node {$node->title}.", __METHOD__);
});
```

### The `modifyNodeActive` Event
The event that is triggered when a node’s active state is calculated. Set `$event->isActive` to override branch highlighting; this does not change the exact current-page match. This example highlights a node named Campaign.

```php
use verbb\navigation\elements\Node;
use verbb\navigation\events\NodeActiveEvent;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_NODE_ACTIVE, function(NodeActiveEvent $event) {
    if ($event->node->title === 'Campaign') {
        $event->isActive = true;
    }
});
```

## Structure Events

### The `beforeUpdateElement` Event
The event that is triggered before an existing element is moved within a structure. These Craft events apply to other element types too, so check that `$event->element` is a Navigation node.

```php
use craft\events\MoveElementEvent;
use craft\services\Structures;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Structures::class, Structures::EVENT_BEFORE_UPDATE_ELEMENT, function(MoveElementEvent $event) {
    if ($event->element instanceof Node) {
        \Craft::info("Moving node {$event->element->title}.", __METHOD__);
    }
});
```

### The `afterUpdateElement` Event
The event that is triggered after an existing element is moved within a structure. These Craft events apply to other element types too, so check that `$event->element` is a Navigation node.

```php
use craft\events\MoveElementEvent;
use craft\services\Structures;
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Structures::class, Structures::EVENT_AFTER_UPDATE_ELEMENT, function(MoveElementEvent $event) {
    if ($event->element instanceof Node) {
        \Craft::info("Moving node {$event->element->title}.", __METHOD__);
    }
});
```

## Node Type Events

### The `registerNodeTypes` Event
The event that is triggered when Navigation registers its node types. Add classes to `$event->types`. This example registers the Group class from [Node Types](/developers/node-types).

```php
use modules\sitemodule\nodetypes\Group;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, function(RegisterNodeTypeEvent $event) {
    $event->types[] = Group::class;
});
```

## Dynamic Source Events

### The `registerDynamicSources` Event
The event that is triggered when Navigation registers its Dynamic source providers. Modify `$event->providers` to add or remove provider classes. This example removes asset volumes from the available Dynamic sources.

```php
use verbb\navigation\dynamic\sources\AssetVolumeDynamicSource;
use verbb\navigation\events\RegisterDynamicSourceEvent;
use verbb\navigation\services\DynamicSources;
use yii\base\Event;

Event::on(DynamicSources::class, DynamicSources::EVENT_REGISTER_DYNAMIC_SOURCES, function(RegisterDynamicSourceEvent $event) {
    $event->providers = array_values(array_filter(
        $event->providers,
        fn($provider) => $provider !== AssetVolumeDynamicSource::class,
    ));
});
```

Custom providers must implement `verbb\navigation\base\DynamicSourceProvider`, or extend `DynamicSource`. See [Dynamic Sources](/developers/node-types#dynamic-sources) for the extension contract.

## Cache Events

### The `invalidateNavigationCache` Event
The event that is triggered when Navigation invalidates its cache. The payload provides `tags`, plus nullable `menuUid`, `siteId`, and `nodeId` properties. Source-wide invalidations may have tags without menu, site, or node identifiers.

```php
use verbb\navigation\events\NavigationCacheInvalidationEvent;
use verbb\navigation\services\NavigationCache;
use yii\base\Event;

Event::on(NavigationCache::class, NavigationCache::EVENT_INVALIDATE, function(NavigationCacheInvalidationEvent $event) {
    \Craft::info('Invalidated navigation tags: ' . implode(', ', $event->tags), __METHOD__);
});
```

| Tag | Scope |
| --- | --- |
| `navigation:menu:{uid}` | Menu |
| `navigation:menu:{uid}:site:{id}` | Menu site |
| `navigation:node:{id}` | Node |
| `navigation:section:{uid}` | Dynamic entry section |
| `navigation:categoryGroup:{uid}` | Dynamic category group |
| `navigation:volume:{uid}` | Dynamic asset volume |
| `navigation:productType:{uid}` | Dynamic product type |

Menu-site invalidation also includes the shared menu tag. Use the emitted tags as invalidation information; do not assume an event only affects one site because `siteId` is populated.

[Navigation with Blitz](/user-guides/frontend-headless/navigation-with-blitz-and-full-page-cache) shows a working tag-and-listener example.

