# Events

Events can be used to extend the functionality of Navigation.

## Nav related events

### The `beforeSaveNav` event

Plugins can get notified before an navigation is saved

```php
use verbb\navigation\events\NavEvent;
use verbb\navigation\services\Navs;
use yii\base\Event;

Event::on(Navs::class, Navs::EVENT_BEFORE_SAVE_NAV, function(NavEvent $e) {
    // Do something
});
```

### The `afterSaveNav` event

Plugins can get notified after a navigation has been saved

```php
use verbb\navigation\events\NavEvent;
use verbb\navigation\services\Navs;
use yii\base\Event;

Event::on(Navs::class, Navs::EVENT_AFTER_SAVE_NAV, function(NavEvent $e) {
    // Do something
});
```

### The `beforeDeleteNav` event

Plugins can get notified before an navigation is deleted

```php
use verbb\navigation\events\NavEvent;
use verbb\navigation\services\Navs;
use yii\base\Event;

Event::on(Navs::class, Navs::EVENT_BEFORE_DELETE_NAV, function(NavEvent $e) {
    // Do something
});
```

### The `afterDeleteNav` event

Plugins can get notified after a navigation has been deleted

```php
use verbb\navigation\events\NavEvent;
use verbb\navigation\services\Navs;
use yii\base\Event;

Event::on(Navs::class, Navs::EVENT_AFTER_DELETE_NAV, function(NavEvent $e) {
    // Do something
});
```


## Node related events

### The `beforeSaveNode` event

Plugins can get notified before a node is saved. Event handlers can prevent the node from getting sent by setting `$event->isValid` to false.

```php
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_BEFORE_SAVE, function(Event $e) {
    $node = $e->sender;
    $e->isValid = false;
});
```

### The `afterSaveNode` event

Plugins can get notified after a node has been saved

```php
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_AFTER_SAVE, function(Event $e) {
    $node = $e->sender;
});
```

### The `modifyNodeActive` event

Plugins can modify the active state of a node.

```php
use verbb\navigation\elements\Node;
use yii\base\Event;

Event::on(Node::class, Node::EVENT_NODE_ACTIVE, function(Event $e) {
    $node = $e->node;
    $e->isActive = true;
});
```

### The `beforeMoveElement` event

Plugins can get notified before a node is moved in its structure.

```php
use verbb\navigation\elements\Node;
use craft\events\MoveElementEvent;
use craft\services\Structures;
use yii\base\Event;

Event::on(Structures::class, Structures::EVENT_BEFORE_MOVE_ELEMENT, function(MoveElementEvent $event) {
    $element = $event->element;

    if ($element instanceof Node) {
        // ...
    }
});
```

### The `afterMoveElement` event

Plugins can get notified after a node is moved in its structure.

```php
use verbb\navigation\elements\Node;
use craft\events\MoveElementEvent;
use craft\services\Structures;
use yii\base\Event;

Event::on(Structures::class, Structures::EVENT_AFTER_MOVE_ELEMENT, function(MoveElementEvent $event) {
    $element = $event->element;

    if ($element instanceof Node) {
        // ...
    }
});
```
