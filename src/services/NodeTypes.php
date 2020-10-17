<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\base\NodeTypeInterface;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\nodetypes\PassiveType;
use verbb\navigation\nodetypes\SiteType;

use Craft;
use craft\base\Component;
use craft\helpers\Component as ComponentHelper;

class NodeTypes extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_NODE_TYPES = 'registerNodeTypes';


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->getRegisteredNodeTypes();
    }

    public function getRegisteredNodeTypes()
    {
        $nodeTypes = [
            PassiveType::class,
        ];

        if (Craft::$app->getIsMultiSite()) {
            $nodeTypes[] = SiteType::class;
        }

        $event = new RegisterNodeTypeEvent([
            'types' => $nodeTypes,
        ]);

        $this->trigger(self::EVENT_REGISTER_NODE_TYPES, $event);

        $types = [];

        foreach ($event->types as $type) {
            $types[] = ComponentHelper::createComponent([
                'type' => $type,
            ], NodeTypeInterface::class);
        }

        return $types;
    }

}