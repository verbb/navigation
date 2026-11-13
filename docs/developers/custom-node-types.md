# Custom Node Types

A node type decides what a node in a menu points to and how editors set it up. Navigation includes types for entries, categories, assets, Commerce products, sites, custom URLs, passive labels, groups and dynamic sources, which [Node Types](/feature-tour/node-types) describes. A custom node type adds another option to the menu builder, for a link whose URL your module works out, a structural wrapper with its own markup, or an integration with another plugin.

To list elements from a source such as a section automatically, add a [Custom Dynamic Source](/developers/custom-dynamic-sources) instead. Dynamic sources plug into the built-in Dynamic node type, so you don't need a node type of your own.

The examples on this page assume a module with the namespace `modules\sitemodule`.

## Create the Node Type Class

Extend `verbb\navigation\base\NodeType`, which implements `NodeTypeInterface` and provides a default for every method. Override only what your type needs. For a type that links to a Craft element and uses an element picker, extend `verbb\navigation\base\ElementNodeType` instead and implement `getElementType()`.

This Telephone type creates `tel:` links for a header or footer menu. Editors enter a title and a phone number, and Navigation stores the number in the node’s `data` array. Save it as `modules/sitemodule/nodetypes/Telephone.php` in your Craft project.

```php
<?php
namespace modules\sitemodule\nodetypes;

use Craft;
use craft\helpers\Cp;
use verbb\navigation\base\NodeType;

class Telephone extends NodeType
{
    public static function displayName(): string
    {
        return 'Telephone';
    }

    public static function getColor(): string
    {
        return '#0f766e';
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [
            [
                '$field' => 'text',
                'name' => 'title',
                'label' => Craft::t('app', 'Title'),
                'validation' => 'required',
            ],
            [
                '$field' => 'text',
                'name' => 'data.phone',
                'label' => 'Phone number',
                'validation' => 'required',
            ],
        ];
    }

    public static function getAddNodeDefaultData(): array
    {
        return ['phone' => ''];
    }

    public function getEditorHtml(): ?string
    {
        return Craft::$app->getView()->namespaceInputs(
            fn() => Cp::textFieldHtml([
                'label' => 'Phone number',
                'id' => 'phone',
                'name' => 'phone',
                'value' => $this->node->data['phone'] ?? '',
                'required' => true,
            ]),
            'data',
        );
    }

    public function getUrl(): ?string
    {
        $phone = preg_replace('/[^\d+]/', '', (string)($this->node->data['phone'] ?? ''));

        return $phone !== '' ? 'tel:' . $phone : null;
    }

    public function beforeSaveNode(bool $isNew): bool
    {
        if (trim((string)($this->node->data['phone'] ?? '')) === '') {
            $this->node->addError('data', 'Enter a phone number.');

            return false;
        }

        return true;
    }
}
```

The type keeps the default `hasTitle()` and `hasUrl()` values of `true`, so the node renders as a link with the editor’s title. `getUrl()` returns `null` when there’s no number, and the node then has no URL.

## Register the Node Type

Navigation finds node types through the `registerNodeTypes` event. Register your class from your module or plugin’s `init()` method.

```php
use modules\sitemodule\nodetypes\Telephone;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\services\NodeTypes;
use yii\base\Event;

Event::on(NodeTypes::class, NodeTypes::EVENT_REGISTER_NODE_TYPES, function(RegisterNodeTypeEvent $event) {
    $event->types[] = Telephone::class;
});
```

`$event->types` holds the built-in type classes, and you can also remove a type from it. Navigation always adds the Custom URL type after the event, so it stays available.

Once registered, Telephone appears in the menu builder and on the menu’s **Permissions** tab, where you can turn it off for a menu. To check it works, open a menu, add a Telephone node with a number, and save the menu. Rendering the menu with `node.link` outputs a link such as `<a href="tel:+61400000000">Call us</a>`. If the type doesn’t appear, check that the module loads (its Composer namespace and bootstrap entry) and that the type is enabled on the menu’s Permissions tab.

## Builder Settings

A node type has two places for its settings. `getAddNodeSchema()` returns the fields in the menu builder’s quick-add panel, and `getAddNodeDefaultData()` sets their starting values. Each field is an array with a `$field` key (such as `text`, `select` or `lightswitch`), a `name` and a `label`, plus options such as `instructions`, `validation` and `options`. Name a field `data.[key]` to store it in the node’s `data` array.

`getEditorHtml()` returns the HTML for the node’s slide-out editor. Namespace your inputs with `data`, as the example does, so their values are saved to the node’s `data` array when the editor saves. The built-in types in Navigation’s `src/nodetypes/` folder, such as `Site` and `GroupColumn`, are useful starting points.

## Methods

Static methods describe the type as a whole, and the other methods run for one node, which is available as `$this->node`.

::: reference
### `displayName()`

**Returns:** `string`

Returns the type’s name, shown in the menu builder and as a node’s default type label. Static.
:::

::: reference
### `hasTitle()`

**Returns:** `bool`

Returns whether nodes of this type have a title field. The default is `true`. Static.
:::

::: reference
### `hasUrl()`

**Returns:** `bool`

Returns whether nodes of this type link somewhere. The default is `true`. Return `false` for a label or wrapper that never has a URL. Static.
:::

::: reference
### `hasNewWindow()`

**Returns:** `bool`

Returns whether editors can choose to open nodes of this type in a new window. The default is `false`. Static.
:::

::: reference
### `getColor()`

**Returns:** `string`

Returns the hex colour used for this type in the menu builder. The default is `#888888`. Static.
:::

::: reference
### `getTag()`

**Returns:** `string`

Returns the HTML tag a node of this type renders with. The default is `a` when `hasUrl()` is `true`, and `span` otherwise. Static.
:::

::: reference
### `getAddNodeSchema()`

**Arguments:** `$context` (`array`), the menu builder tab the panel is for.

**Returns:** `array`

Returns the fields shown in the menu builder’s quick-add panel. The default adds the new window, title and URL fields that `hasNewWindow()`, `hasTitle()` and `hasUrl()` allow. Static.
:::

::: reference
### `getAddNodeDefaultData()`

**Returns:** `array`

Returns the starting values for a new node’s `data` array in the quick-add panel. The default is an empty array. Static.
:::

::: reference
### `getEditorHtml()`

**Returns:** `string|null`

Returns the HTML for this type’s settings in the node’s slide-out editor. Return `null` when the type has no settings of its own.
:::

::: reference
### `getUrl()`

**Returns:** `string|null`

Returns the node’s URL, before Navigation adds the node’s URL suffix. Return `null` when the node has no URL.
:::

::: reference
### `getDefaultTitle()`

**Returns:** `string`

Returns the title Navigation gives the node when it’s saved without one. The default is the type’s `displayName()`.
:::

::: reference
### `getTypeLabel()`

**Returns:** `string`

Returns the label shown for the node’s type in the menu builder and in GraphQL’s `typeLabel` field. The default is the type’s `displayName()`.
:::

::: reference
### `beforeSaveNode()`

**Arguments:** `$isNew` (`bool`), whether the node is being created.

**Returns:** `bool`

Returns whether the node can be saved. Add an error to `$this->node` and return `false` to stop the save. The default returns `true`.
:::

::: reference
### `getPermissionEnabledDefault()`

**Returns:** `bool`

Returns whether the type is enabled on a menu’s **Permissions** tab before an administrator changes it. The default is `true`.
:::

::: reference
### `getPermissionSourceOptions()`

**Returns:** `array`

Returns the sources an administrator can limit this type to on a menu’s **Permissions** tab, as `value` and `label` pairs. The default is an empty array, which shows no source options.
:::

::: reference
### `getPermissionSettingsHtml()`

**Arguments:** `$settings` (`array`), the saved permission settings for this type. `$fieldPrefix` (`string`, optional), the input name prefix.

**Returns:** `string`

Returns extra HTML for this type’s settings on a menu’s **Permissions** tab. The default is an empty string.
:::

::: reference
### `getElementType()`

**Returns:** `string`

Returns the class of the Craft element this type links to, such as `craft\elements\Entry`. Required when you extend `ElementNodeType`. Static.
:::

::: reference
### `getBuilderConfig()`

**Returns:** `array`

Returns the settings for this type’s tab in the menu builder, including its `label`, `button` text and `color`. Only `ElementNodeType` has this method, and its default is built from the element type’s name. Static.
:::

::: reference
### `getBulkSoftDeletedElementIds()`

**Arguments:** `$sourceType` (`string`), the kind of source Craft removed, such as `section`. `$sourceId` (`int`), the source’s ID.

**Returns:** `array|null`

Returns the IDs of elements that Craft soft-deleted along with a whole source, such as a section, without firing a delete event for each one. Navigation disables the matching nodes, so they come back if the source is restored. Return `null` when the type doesn’t handle that kind of source. Only `ElementNodeType` has this method. Static.
:::
