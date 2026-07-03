# Node Types

Register custom node types when built-in types are not enough — specialised URLs, structural wrappers, or integration-specific behaviour.

## Create a class

Extend `verbb\navigation\base\NodeType` (or `ElementNodeType` for Craft element pickers):

```php
<?php
namespace modules\sitemodule\nodetypes;

use verbb\navigation\base\NodeType;

class Group extends NodeType
{
    public static function displayName(): string
    {
        return 'Group';
    }

    public static function hasUrl(): bool
    {
        return false;
    }

    public static function getTag(): string
    {
        return 'span';
    }
}
```

Common static methods: `hasTitle()`, `hasUrl()`, `hasNewWindow()`, `hasClasses()`, `getColor()`, `getTag()`.

Implement instance methods such as `getUrl()`, `getDefaultTitle()`, `beforeSaveNode()`, `getEditorHtml()`, and `getAddNodeSchema()` as needed.

For element-backed types, extend `ElementNodeType` and implement `getElementType()`.

## Register the type

The event that is triggered when registering node types for the menu builder is `NodeTypes::EVENT_REGISTER_NODE_TYPES`. Add your class to the `types` array on the event:

```php
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, function (RegisterNodeTypeEvent $event) {
    $event->types[] = \modules\sitemodule\nodetypes\Group::class;
});
```

Registered types appear in the menu builder sidebar and in permissions settings when `getBuilderConfig()` is provided.

## Builder schema

Override `getAddNodeSchema()` and `getAddNodeDefaultData()` for quick-add fields in the menu builder. Override `getEditorHtml()` for slide-out settings.

See built-in types in `src/nodetypes/` for examples — especially `Dynamic` (with sources in `src/dynamic/sources/`) and `GroupColumn`.

## Dynamic sources

Dynamic nodes delegate projection to **source providers** registered on `DynamicSources::EVENT_REGISTER_DYNAMIC_SOURCES`. Implement `verbb\navigation\base\DynamicSourceProvider` and add your class to the event’s `providers` array. See [Events — Register dynamic sources](/developers/events#the-registerdynamicsources-event).

## Related

- [Node Types](/menus/node-types)
- [Events](/developers/events)
