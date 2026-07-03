<?php
namespace verbb\navigation\services;

use verbb\navigation\base\ElementNodeType;
use verbb\navigation\base\NodeTypeInterface;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\nodetypes\Asset;
use verbb\navigation\nodetypes\Category;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\nodetypes\GroupColumn;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Product;
use verbb\navigation\nodetypes\Site;

use Craft;
use craft\base\Component;
use craft\helpers\Component as ComponentHelper;

class NodeTypes extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_REGISTER_NODE_TYPES = 'registerNodeTypes';


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        $this->getRegisteredNodeTypes();
    }

    public function getRegisteredNodeTypes(): array
    {
        $nodeTypes = [
            Entry::class,
            Category::class,
            Asset::class,
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce') && class_exists(Product::class)) {
            $nodeTypes[] = Product::class;
        }

        $nodeTypes = array_merge($nodeTypes, [
            Passive::class,
            GroupColumn::class,
            Dynamic::class,
        ]);

        if (Craft::$app->getIsMultiSite()) {
            $nodeTypes[] = Site::class;
        }

        $event = new RegisterNodeTypeEvent([
            'types' => $nodeTypes,
        ]);

        $this->trigger(self::EVENT_REGISTER_NODE_TYPES, $event);

        $nodeTypes = $event->types;

        // Always add custom node at the end
        $nodeTypes[] = Custom::class;

        $types = [];

        foreach ($nodeTypes as $type) {
            $types[] = ComponentHelper::createComponent([
                'type' => $type,
            ], NodeTypeInterface::class);
        }

        return $types;
    }

    public function getRegisteredElementNodeTypeClasses(): array
    {
        $classes = [];

        foreach ($this->getRegisteredNodeTypes() as $nodeType) {
            if ($nodeType instanceof ElementNodeType) {
                $classes[] = $nodeType::class;
            }
        }

        return $classes;
    }
}
