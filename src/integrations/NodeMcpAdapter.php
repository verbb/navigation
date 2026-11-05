<?php
namespace verbb\navigation\integrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\MenuAuth;

use Craft;
use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\mcp\elements\BaseElementAdapter;
use craft\mcp\ElementQueryCriteria;

use Mcp\Exception\ToolCallException;

/**
 * Exposes menu nodes through Craft's `elements.*` MCP tools (Craft 5.12+).
 */
class NodeMcpAdapter extends BaseElementAdapter
{
    // Static Methods
    // =========================================================================

    public static function handle(): string
    {
        return 'navigation-nodes';
    }

    public static function elementType(): string
    {
        return Node::class;
    }


    // Properties
    // =========================================================================

    private ?array $_types = null;


    // Public Methods
    // =========================================================================

    public function fieldSchemaElement(?ElementInterface $element, array $context, User $actor): ElementInterface
    {
        if ($element !== null) {
            return parent::fieldSchemaElement($element, $context, $actor);
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($context['menuId'] ?? 0);
        $siteId = $context['siteId'] ?? Craft::$app->getSites()->getPrimarySite()->id;

        if (!MenuAuth::canManageMenuSite($actor, $menu, $siteId)) {
            throw new ToolCallException('Provide a menuId and siteId you can manage.');
        }

        // A menu owns the layout; an unsaved node has no type or linked element to authorize yet.
        return $this->newElement(['menuId' => $menu->id, 'siteId' => $siteId], $actor);
    }


    // Protected Methods
    // =========================================================================

    protected function newElement(array $context, User $actor): ElementInterface
    {
        return parent::newElement(['siteId' => Craft::$app->getSites()->getPrimarySite()->id, ...$context], $actor);
    }

    protected function criteriaProperties(): array
    {
        return [
            'menuId' => [...ElementQueryCriteria::IntegerOrIntegersSchema, 'description' => 'Menu ID or IDs.'],
            'handle' => [...ElementQueryCriteria::StringOrStringsSchema, 'description' => 'Menu handle or handles.'],
            'type' => [...ElementQueryCriteria::StringOrStringsSchema, 'description' => 'Node type class name or names.'],
            'elementId' => [...ElementQueryCriteria::IntegerOrIntegersSchema, 'description' => 'Linked element ID or IDs.'],
            'level' => ['type' => 'integer', 'description' => 'Structure level, starting at 1.'],
        ];
    }

    protected function createAttributesSchema(): array
    {
        $schema = $this->_attributesSchema();
        $schema['properties'] = [
            'menuId' => ['type' => 'integer', 'description' => 'Menu ID. Provide this or menuHandle.'],
            'menuHandle' => ['type' => 'string', 'description' => 'Menu handle. Provide this or menuId.'],
            'siteId' => ['type' => 'integer', 'description' => 'Site ID. Defaults to the primary site.'],
            ...$schema['properties'],
        ];
        $schema['required'] = ['type'];

        return $schema;
    }

    protected function updateAttributesSchema(): array
    {
        $schema = $this->_attributesSchema();
        unset($schema['properties']['parentId']);

        return $schema;
    }

    protected function fieldSchemaContextSchema(): array
    {
        return [
            'type' => 'object',
            'description' => 'For a new node, provide menuId to select its menu’s field layout.',
            'properties' => [
                'menuId' => ['type' => 'integer', 'description' => 'Menu ID.'],
                'siteId' => ['type' => 'integer', 'description' => 'Site ID. Defaults to the primary site.'],
            ],
            'additionalProperties' => false,
        ];
    }

    protected function notes(): array
    {
        return [
            'Nodes linking to an element (such as verbb\\navigation\\nodetypes\\Entry) take linkedElementId. Custom nodes take url.',
            'parentId nests a new node under another node in the same menu. Use navigation.nodes.move to move existing nodes; structures.move-element only moves entries.',
            'Saves go live immediately. They do not go through the menu builder’s staged changes.',
        ];
    }

    protected function findQuery(): ElementQueryInterface
    {
        return Node::find()->status(null);
    }

    protected function populate(ElementInterface $element, array $attributes, array $fields, User $actor): void
    {
        // Reparenting carries descendants, so it needs the move tool’s subtree authorization.
        if ($element->id !== null && array_key_exists('parentId', $attributes)) {
            throw new ToolCallException('Use navigation.nodes.move to move an existing node.');
        }

        if (isset($attributes['menuHandle'])) {
            $menu = Navigation::$plugin->getMenus()->getMenuByHandle($attributes['menuHandle']);

            if (!$menu) {
                throw new ToolCallException("No menu exists with the handle “{$attributes['menuHandle']}”.");
            }

            $attributes['menuId'] = $menu->id;
            unset($attributes['menuHandle']);
        }

        if ($element->id === null && empty($attributes['menuId']) && empty($element->menuId)) {
            throw new ToolCallException('Provide menuId or menuHandle for a new node.');
        }

        parent::populate($element, $attributes, $fields, $actor);
    }


    // Private Methods
    // =========================================================================

    private function _attributesSchema(): array
    {
        $types = $this->_types ??= array_map(
            static fn($type) => $type::class,
            Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes(),
        );

        return [
            'type' => 'object',
            'properties' => [
                'type' => ['type' => 'string', 'enum' => $types, 'description' => 'Node type class name.'],
                'title' => ['type' => ['string', 'null'], 'description' => 'Node title. Element nodes default to the linked element’s title.'],
                'linkedElementId' => ['type' => ['integer', 'null'], 'description' => 'ID of the linked element, for element node types.'],
                'url' => ['type' => ['string', 'null'], 'description' => 'URL, for custom nodes.'],
                'urlSuffix' => ['type' => ['string', 'null'], 'description' => 'Appended to the URL, such as an anchor.'],
                'classes' => ['type' => ['string', 'null'], 'description' => 'CSS classes for the rendered link.'],
                'newWindow' => ['type' => 'boolean', 'description' => 'Whether the link opens in a new window.'],
                'enabled' => ['type' => 'boolean', 'description' => 'Whether the node is enabled.'],
                'parentId' => ['type' => ['integer', 'null'], 'description' => 'Parent node ID in the same menu, or null for the top level.'],
            ],
            'additionalProperties' => false,
        ];
    }
}
