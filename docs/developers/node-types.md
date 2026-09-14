# Node Types

Register custom node types when [built-in types](/feature-tour/node-types) are not enough — specialised URLs, structural wrappers, or integration-specific behaviour.

The examples assume an existing module using the namespace `modules\sitemodule`. The Group type below supplies a non-link label for a custom layout.

## Create a Class

Create `modules/sitemodule/nodetypes/Group.php` in the Craft project. Extend `verbb\navigation\base\NodeType` (or `ElementNodeType` for Craft element pickers):

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

Common static methods: `hasTitle()`, `hasUrl()`, `hasNewWindow()`, `getColor()`, `getTag()`.

Implement instance methods such as `getUrl()`, `getDefaultTitle()`, `beforeSaveNode()`, and `getEditorHtml()` as needed. The quick-add schema method `getAddNodeSchema(array $context)` is static.

For element-backed types, extend `ElementNodeType` and implement `getElementType()`.

## Register the Type

Place this registration in your module’s `init()` method, with the imports at the top of its PHP file. It adds your class when Navigation collects the available types:

```php
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, function (RegisterNodeTypeEvent $event) {
    $event->types[] = \modules\sitemodule\nodetypes\Group::class;
});
```

The registered Group type appears in the builder and the menu’s Permissions tab. Its title field comes from the base class. `getBuilderConfig()` belongs to element-backed picker types; the Group example does not require it.

Open the builder, add a Group node, enter a title, and save the menu. Render it with `node.link`: the output should be a span containing that title. If Group is missing, check the module’s Composer namespace, bootstrap entry, and the menu’s Permissions tab.

## Builder Schema

Override `getAddNodeSchema()` and `getAddNodeDefaultData()` for quick-add fields in the menu builder. Override `getEditorHtml()` for slide-out settings.

See built-in types in `src/nodetypes/` for examples — especially `Dynamic` (with sources in `src/dynamic/sources/`) and `GroupColumn`.

## Dynamic Sources

Dynamic nodes delegate projection to **source providers** registered on `DynamicSources::EVENT_REGISTER_DYNAMIC_SOURCES`. Implement `verbb\navigation\base\DynamicSourceProvider` (or extend `DynamicSource`) and add your class to the event’s `providers` array. See [Events — Register dynamic sources](/developers/events#the-registerdynamicsources-event).
