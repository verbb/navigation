# Extensibility

## Elements
You can add your own custom elements to be compatible with Navigation by using the provided events. The below shows an example of how entries are set up.

```php
use verbb\navigation\services\Elements;
use verbb\navigation\events\RegisterElementEvent;
use yii\base\Event;

Event::on(Elements::class, Elements::EVENT_REGISTER_NAVIGATION_ELEMENT, function(RegisterElementEvent $event) {
    $event->elements['entries'] = [
        'label' => Craft::t('navigation', 'Entries'),
        'button' => Craft::t('navigation', 'Add an entry'),
        'type' => 'craft\\elements\\Entry',
    ];
});
```

## Node Types
Node types allow you to define your own type of nodes for various cases. You might like to have certain types of "Custom URL" nodes for instance.

You'll need to first create a class to implement your node type. Here's an example for a Group.

```php
<?php
namespace modules;

use verbb\navigation\base\NodeType;

class Group extends NodeType
{
    // Static
    // =========================================================================

    public static function displayName(): string
    {
        return 'Group';
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return false;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function hasClasses(): bool
    {
        return false;
    }
}
```

Here you can see there's various functions to enable or disable certain properties for your node type.

Lastly, you'll want to register this class with Navigation:

```php
use verbb\navigation\services\NodeTypes;
use verbb\navigation\events\RegisterNodeTypeEvent;
use yii\base\Event;

Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, function(RegisterNodeTypeEvent $event) {
    $event->types[] = Group::class;
});
```
