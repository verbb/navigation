<?php
namespace verbb\navigation\services;

use verbb\navigation\base\NodeTypeInterface;
use verbb\navigation\events\RegisterNodeTypeEvent;
use verbb\navigation\nodetypes as types;

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

    // public function init(): void
    // {
    //     parent::init();

    //     $this->getRegisteredNodeTypes();
    // }

    public function getRegisteredNodeTypes(): array
    {
        $nodeTypes = [
            types\PassiveType::class,
        ];

        if (Craft::$app->getIsMultiSite()) {
            $nodeTypes[] = types\SiteType::class;
        }

        $event = new RegisterNodeTypeEvent([
            'types' => $nodeTypes,
        ]);
        $this->trigger(self::EVENT_REGISTER_NODE_TYPES, $event);

        // Always add custom node at the end
        $event->types[] = types\CustomType::class;

        // Ensure that we filter out only supported node types
        return $this->_filterSupportedNodeTypes($event->types);
    }

    public function getAllNodeTypes(): array
    {
        $types = [];

        foreach ($this->getRegisteredNodeTypes() as $type) {
            $types[] = $this->createNodeType($type);
        }

        return $types;
    }

    public function createNodeType(mixed $config): NodeTypeInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $nodeType = ComponentHelper::createComponent($config, NodeTypeInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $nodeType = new nodetypes\MissingNodeType($config);
        }

        return $nodeType;
    }


    // Private Methods
    // =========================================================================

    private function _filterSupportedNodeTypes(array $types): array
    {
        foreach ($types as $nodeTypeKey => $nodeType) {
            // foreach ($nodeType::getRequiredPlugins() as $handle) {
            //     $version = 0;

            //     if (is_array($handle)) {
            //         $version = $handle['version'] ?? $version;
            //         $handle = $handle['handle'] ?? '';
            //     }

            //     if (!Navigation::$plugin->getService()->isPluginInstalledAndEnabled($handle)) {
            //         unset($event->types[$nodeTypeKey]);
            //         continue;
            //     }

            //     $plugin = Craft::$app->getPlugins()->getPlugin($handle);

            //     if (!$plugin) {
            //         unset($event->types[$nodeTypeKey]);
            //         continue;
            //     }

            //     if (version_compare($plugin->getVersion(), $version, '<')) {
            //         unset($event->types[$nodeTypeKey]);
            //     }
            // }
        }

        return $types;
    }

}