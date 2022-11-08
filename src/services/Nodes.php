<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;

use Craft;
use craft\base\Component;
use craft\events\ElementEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\ElementHelper;

class Nodes extends Component
{
    // Properties
    // =========================================================================

    private array $_tempNodes = [];


    // Public Methods
    // =========================================================================

    public function getNodeById(int $id, mixed $siteId = null, array $criteria = []): ?NodeElement
    {
        return Craft::$app->getElements()->getElementById($id, NodeElement::class, $siteId, $criteria);
    }

    public function getNodesForNav($navId, $siteId = null, $includeTemp = false): array
    {
        $nodes = NodeElement::find()
            ->navId($navId)
            ->status(null)
            ->siteId($siteId)
            ->status(null)
            ->all();

        if ($includeTemp) {
            $nodes = array_merge($nodes, $this->_tempNodes);
        }

        return $nodes;
    }

    public function onSaveElement(ElementEvent $event): void
    {
        // Skip this when updating Craft is currently in progress
        if (Craft::$app->getIsInMaintenanceMode()) {
            return;
        }

        $element = $event->element;
        $isNew = $event->isNew;

        // We only care about already-existing elements and if they have a URL
        if ($isNew || !$element->getUrl()) {
            return;
        }

        // This triggers for every element - including a Node!
        if (get_class($element) === NodeElement::class) {
            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->siteId($element->siteId)
            ->slug((string)$element->siteId)
            ->status(null)
            ->type(get_class($element))
            ->all();

        foreach ($nodes as $node) {
            // If no nav for the node, skip. Just to protect against nodes in some cases
            $nav = Navigation::$plugin->getNavs()->getNavById($node->navId);

            if (!$nav) {
                return;
            }

            // Check if the element is propagating, and in the allowed sites
            if ($element->propagating) {
                $supportedSites = ElementHelper::supportedSitesForElement($node);
                $supportedSiteIds = ArrayHelper::getColumn($supportedSites, 'siteId');

                if (!in_array($node->siteId, $supportedSiteIds, false)) {
                    return;
                }
            }

            $currentElement = Craft::$app->getElements()->getElementById($element->id, get_class($element), $element->siteId);

            if ($element->uri) {
                $node->url = $element->uri;
            }

            // Only update the node name if they were the same before the element was saved
            if ($currentElement && $currentElement->title === $node->title) {
                $node->title = $element->title;
            }

            // Only update URL if its changed
            if ($currentElement && $currentElement->enabled === $node->enabled) {
                $node->enabled = $element->enabled;
            }

            $node->elementSiteId = $element->siteId;

            Craft::$app->getElements()->saveElement($node, true, false);
        }
    }

    public function onDeleteElement(ElementEvent $event): void
    {
        $element = $event->element;

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->type(get_class($element))
            ->siteId($element->siteId)
            ->ids();

        foreach ($nodes as $nodeId) {
            Craft::$app->getElements()->deleteElementById($nodeId);
        }
    }

    public function getParentOptions($nodes, $nav): array
    {
        $maxLevels = $nav->maxLevels ?: false;

        $parentOptions[] = [
            'label' => '',
            'value' => 0,
        ];

        foreach ($nodes as $node) {
            $label = '';

            for ($i = 1; $i < $node->level; $i++) {
                $label .= '    ';
            }

            $label .= $node->title;

            $parentOptions[] = [
                'label' => $label,
                'value' => $node->id,
                'disabled' => $maxLevels !== false && $node->level >= $maxLevels,
            ];
        }

        return $parentOptions;
    }

    public function setTempNodes(array $nodes): void
    {
        $this->_tempNodes = $nodes;
    }

    public function getTempNodes(): array
    {
        return $this->_tempNodes;
    }
}
