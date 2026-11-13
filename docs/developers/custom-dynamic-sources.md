# Custom Dynamic Sources

A Dynamic node lists elements from a source under itself when the menu renders, so the menu keeps up with new content without editors adding each item by hand. Navigation includes sources for entry sections, category groups, asset volumes and Commerce product types. A custom dynamic source adds another option to the Dynamic node’s **Source** setting, for a set of elements the built-in sources can’t describe.

The items a source returns aren’t saved as nodes. Navigation projects them into the menu each time it’s read, as [Projected Node](/reference/projected-node) objects. The examples on this page assume a module with the namespace `modules\sitemodule`.

## Create the Dynamic Source Class

A source implements `verbb\navigation\base\DynamicSourceProvider`. Extending `verbb\navigation\base\DynamicSource` is the easiest way to do that, because it provides `displayName()` (the element type’s plural name) and `canAuthorNode()`. Every method on a source is static and receives the Dynamic node it’s working for, and the node’s settings are stored in its `data` array.

This source lists the live entries of one entry type, whichever sections use it. Save it as `modules/sitemodule/dynamic/EntryTypeDynamicSource.php` in your Craft project.

```php
<?php
namespace modules\sitemodule\dynamic;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\helpers\Cp;
use verbb\navigation\base\DynamicSource;
use verbb\navigation\elements\Node;
use verbb\navigation\models\ProjectedNode;
use verbb\navigation\Navigation;

class EntryTypeDynamicSource extends DynamicSource
{
    public static function handle(): string
    {
        return 'entryType';
    }

    public static function displayName(): string
    {
        return 'Entry Type';
    }

    public static function elementType(): string
    {
        return Entry::class;
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [
            [
                '$field' => 'select',
                'name' => 'data.entryTypeId',
                'label' => 'Entry type',
                'validation' => 'required',
                'options' => self::_entryTypeOptions(),
            ],
        ];
    }

    public static function getAddNodeDefaultData(): array
    {
        return ['entryTypeId' => ''];
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        return Craft::$app->getView()->namespaceInputs(
            fn() => Cp::selectFieldHtml([
                'label' => 'Entry type',
                'id' => 'entryTypeId',
                'name' => 'entryTypeId',
                'value' => $node->data['entryTypeId'] ?? '',
                'options' => self::_entryTypeOptions(),
                'required' => true,
            ]),
            'data',
        );
    }

    public static function applyPostData(Node $node): void
    {
        // The slide-out field is already saved to `data`, so there's nothing to normalise.
    }

    public static function validateNode(Node $node): bool
    {
        if (!self::_getEntryType($node)) {
            $node->addError('data', 'Choose an entry type.');

            return false;
        }

        return true;
    }

    public static function getDefaultTitle(Node $node): ?string
    {
        return self::_getEntryType($node)?->name;
    }

    public static function getTypeLabel(Node $node): ?string
    {
        return self::_getEntryType($node)?->name;
    }

    public static function getProjectedChildren(Node $parent, int $siteId): array
    {
        $entryType = self::_getEntryType($parent);

        if (!$entryType) {
            return [];
        }

        $query = Entry::find()
            ->typeId($entryType->id)
            ->siteId($siteId)
            ->orderBy(['title' => SORT_ASC]);

        Navigation::$plugin->getDynamicSources()->applyProjectionStatus($query);

        return array_map(
            fn(Entry $entry) => ProjectedNode::fromElement($entry, $parent),
            $query->all(),
        );
    }

    public static function getCacheTags(Node $node): array
    {
        $entryType = self::_getEntryType($node);

        if (!$entryType) {
            return [];
        }

        $cache = Navigation::$plugin->getNavigationCache();
        $tags = [];

        foreach (Craft::$app->getEntries()->getAllSections() as $section) {
            foreach ($section->getEntryTypes() as $sectionEntryType) {
                if ($sectionEntryType->id === $entryType->id) {
                    $tags[] = $cache->sectionTag($section->uid);
                }
            }
        }

        return $tags;
    }

    public static function getCacheTagsForProjectedElement(ElementInterface $element): array
    {
        if (!$element instanceof Entry || !$element->getSection()) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->sectionTag($element->getSection()->uid)];
    }

    public static function shouldDeleteNodeOnSourceDelete(Node $node, string $sourceType, int $sourceId): bool
    {
        return false;
    }

    private static function _getEntryType(Node $node): ?\craft\models\EntryType
    {
        $id = (int)($node->data['entryTypeId'] ?? 0);

        return $id ? Craft::$app->getEntries()->getEntryTypeById($id) : null;
    }

    private static function _entryTypeOptions(): array
    {
        $options = [['label' => 'Select an entry type', 'value' => '']];

        foreach (Craft::$app->getEntries()->getAllEntryTypes() as $entryType) {
            $options[] = ['label' => $entryType->name, 'value' => (string)$entryType->id];
        }

        return $options;
    }
}
```

`applyProjectionStatus()` keeps the projected entries to live ones on the front end, and includes disabled ones only when Navigation asks for them, such as in a tokenised preview request or a query with `includePendingProjections`. Call it on every query a source runs, so unpublished content never appears in a public menu.

## Register the Dynamic Source

Navigation finds dynamic sources through the `registerDynamicSources` event. Register your class from your module or plugin’s `init()` method.

```php
use modules\sitemodule\dynamic\EntryTypeDynamicSource;
use verbb\navigation\events\RegisterDynamicSourceEvent;
use verbb\navigation\services\DynamicSources;
use yii\base\Event;

Event::on(DynamicSources::class, DynamicSources::EVENT_REGISTER_DYNAMIC_SOURCES, function(RegisterDynamicSourceEvent $event) {
    $event->providers[] = EntryTypeDynamicSource::class;
});
```

`$event->providers` holds the built-in source classes, and you can also remove one from it. Once registered, **Entry Type** appears in the **Source** setting when editors add a Dynamic node. Add one, choose an entry type and save the menu, and the menu then lists that type’s entries under the Dynamic node.

## Caching

When Navigation caches a menu, it tags the cache with each Dynamic node’s `getCacheTags()`. When an element of your source’s `elementType()` is saved or deleted, Navigation asks every source for `getCacheTagsForProjectedElement()` and clears the caches with those tags. Return the same tags from both methods, so a change to a projected element clears the menus that show it. The `sectionTag()`, `categoryGroupTag()`, `volumeTag()` and `productTypeTag()` methods on `Navigation::$plugin->getNavigationCache()` build the tags the built-in sources use.

## Methods

Every method on a dynamic source is static.

::: reference
### `handle()`

**Returns:** `string`

Returns the unique handle Navigation stores in a Dynamic node’s `data` array to remember its source. Changing it disconnects the nodes that already use the source.
:::

::: reference
### `displayName()`

**Returns:** `string`

Returns the source’s name in the **Source** setting. `DynamicSource` returns the plural name of the source’s element type.
:::

::: reference
### `elementType()`

**Returns:** `string`

Returns the class of the elements the source projects, such as `craft\elements\Entry`. Navigation uses it to find the sources affected when an element is saved.
:::

::: reference
### `getAddNodeSchema()`

**Arguments:** `$context` (`array`), the menu builder tab the panel is for.

**Returns:** `array`

Returns the fields for the source’s settings in the menu builder’s quick-add panel. Navigation shows them only when this source is selected. Name a field `data.[key]` to store it in the node’s `data` array.
:::

::: reference
### `getAddNodeDefaultData()`

**Returns:** `array`

Returns the starting values for the source’s settings in the quick-add panel.
:::

::: reference
### `renderSlideoutHtml()`

**Arguments:** `$node` ([Node](/reference/node)), the Dynamic node being edited.

**Returns:** `string`

Returns the HTML for the source’s settings in the node’s slide-out editor. Namespace the inputs with `data` so they’re saved to the node’s `data` array.
:::

::: reference
### `applyPostData()`

**Arguments:** `$node` ([Node](/reference/node)), the Dynamic node being saved.

**Returns:** `void`

Updates the node’s `data` array before it’s validated and saved, such as normalising a value or reading a condition builder from the request. Leave it empty when the slide-out fields already save everything.
:::

::: reference
### `validateNode()`

**Arguments:** `$node` ([Node](/reference/node)), the Dynamic node being saved.

**Returns:** `bool`

Returns whether the node’s settings are valid. Add an error to the node and return `false` to stop the save.
:::

::: reference
### `getDefaultTitle()`

**Arguments:** `$node` ([Node](/reference/node)), the Dynamic node.

**Returns:** `string|null`

Returns the title Navigation gives the node when it’s saved without one. Return `null` to use the Dynamic node type’s own name.
:::

::: reference
### `getTypeLabel()`

**Arguments:** `$node` ([Node](/reference/node)), the Dynamic node.

**Returns:** `string|null`

Returns the label shown for the node’s type in the menu builder. Return `null` to use the Dynamic node type’s own name.
:::

::: reference
### `getProjectedChildren()`

**Arguments:** `$parent` ([Node](/reference/node)), the Dynamic node. `$siteId` (`int`), the site the menu is being read for.

**Returns:** `array`

Returns the [Projected Node](/reference/projected-node) objects to list under the Dynamic node. Build each one with `ProjectedNode::fromElement($element, $parent)`.
:::

::: reference
### `getCacheTags()`

**Arguments:** `$node` ([Node](/reference/node)), the Dynamic node.

**Returns:** `array`

Returns the cache tags for the menus that include this node. Return an empty array when the node has no source selected.
:::

::: reference
### `getCacheTagsForProjectedElement()`

**Arguments:** `$element` (`ElementInterface`), the element that was saved or deleted.

**Returns:** `array`

Returns the cache tags to clear when an element of the source’s type changes. Return an empty array when the element doesn’t belong to this source.
:::

::: reference
### `shouldDeleteNodeOnSourceDelete()`

**Arguments:** `$node` ([Node](/reference/node)), a Dynamic node. `$sourceType` (`string`), the kind of source that was deleted (`section`, `categoryGroup`, `volume` or `productType`). `$sourceId` (`int`), its ID.

**Returns:** `bool`

Returns whether Navigation should delete the Dynamic node because the section, category group, volume or product type it lists was deleted.
:::

::: reference
### `canAuthorNode()`

**Arguments:** `$node` ([Node](/reference/node)), the Dynamic node. `$user` (`User`), the person editing the menu.

**Returns:** `bool`

Returns whether this person can add or edit a Dynamic node using the source, such as only when they can view the section it lists. `DynamicSource` returns `true`.
:::
