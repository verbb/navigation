<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;
use craft\events\ElementEvent;
use craft\events\SiteEvent;
use craft\queue\jobs\ResaveElements;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;

use yii\web\UserEvent;

class Nodes extends Component
{
    // Public Methods
    // =========================================================================

    public function getNodeById($id, $siteId = null)
    {
        return Craft::$app->getElements()->getElementById($id, NodeElement::class, $siteId);
    }

    public function getNodesForNav($navId, $siteId = null)
    {
        return NodeElement::find()
            ->navId($navId)
            ->status(null)
            ->siteId($siteId)
            ->enabledForSite(false)
            ->all();
    }

    public function saveNode(&$node)
    {
        $errors = [];

        $propagateNodes = $node->nav->propagateNodes;
        $isNew = !$node->id;

        $clonedNodes = [];
        $currentSite = Craft::$app->getSites()->getCurrentSite();

        // Whilst there is an easier way to just have node elements propagated by passing it into the saveElement
        // service, we can't rely on that because of the complexities with the linked element and multi-site.
        // Instead, let's just create each as a new element, in turn, a new node record as well.
        if ($propagateNodes && $isNew) {
            $errors = [];
            $siteIds = Craft::$app->getSites()->getAllSiteIds();

            // Create new entries for each site, localised as required
            foreach ($siteIds as $siteId) {
                $canCreateNode = true;

                $clonedNode = clone $node;
                $clonedNode->setElement(null);
                $clonedNode->siteId = $siteId;

                if ($clonedNode->elementId) {
                    $clonedNode->elementSiteId = $siteId;
                    $element = $clonedNode->getElement();

                    if ($element) {
                        $clonedNode->title = $element->title;
                    } else {
                        // In this instance, we've got an element ID for an element, but it doesn't
                        // exist for this site, so we don't want to add it, otherwise it'd be a manual link
                        // This can often happen if a new site was added, but the element hasn't been resaved
                        $canCreateNode = false;
                    }
                }

                if ($canCreateNode) {
                    if (!Craft::$app->getElements()->saveElement($clonedNode, true, false)) {
                        $errors[] = $clonedNode->getErrors();
                    } else {
                        $clonedNodes[$siteId] = $clonedNode;
                    }
                }
            }
        } else {
            if (!Craft::$app->getElements()->saveElement($node, true, false)) {
                $errors[] = $node->getErrors();
            }
        }

        // Make sure to send back the correct, updated node to the controller
        if (!$errors && $clonedNodes) {
            if (isset($clonedNodes[$currentSite->id])) {
                $node = $clonedNodes[$currentSite->id];
            }
        }

        return $errors;
    }

    public function deleteNodes($nav, $nodeIds)
    {
        $errors = [];
        $nodesToDelete = [];

        $siteIds = Craft::$app->getSites()->getAllSiteIds();
        $propagateNodes = $nav->propagateNodes;

        // We need to go against `deleteElement()` which will kick up any child elements in the structure
        // to be attached to the parent - not what we want in this case, it'd be pandemonium.
        foreach ($nodeIds as $nodeId) {
            // If we've set our nodes to propagate, we should also delete any propagated ones
            if ($propagateNodes) {
                $node = Navigation::$plugin->nodes->getNodeById($nodeId);

                foreach ($siteIds as $siteId) {
                    if ($node) {
                        $propagatedNode = NodeElement::find()
                            ->navId($node->navId)
                            ->elementId($node->elementId)
                            ->siteId($siteId)
                            ->one();

                        if ($propagatedNode) {
                            $nodesToDelete[] = $propagatedNode->id;
                        }
                    }
                }
            } else {
                $nodesToDelete[] = $nodeId;
            }
        }

        foreach ($nodesToDelete as $nodeToDelete) {
            if (!Craft::$app->getElements()->deleteElementById($nodeToDelete)) {
                $errors[] = true;
            }
        }

        return $errors;
    }

    public function onSaveElement(ElementEvent $event)
    {
        $element = $event->element;
        $isNew = $event->isNew;

        // We only care about already-existing elements
        if ($isNew) {
            return;
        }

        // This triggers for every element - including a Node!
        if (get_class($element) === NodeElement::class) {
            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->elementSiteId($element->siteId)
            ->status(null)
            ->type(get_class($element))
            ->all();

        foreach ($nodes as $node) {
            $currentElement = Craft::$app->getElements()->getElementById($element->id, get_class($element), $element->siteId);

            $node->enabled = (int)$element->enabled;

            if ($element->uri) {
                $node->url = $element->uri;
            }

            // Only update the node name if they were the same before the element was saved
            if ($currentElement && $currentElement->title === $node->title) {
                $node->title = $element->title;
            }

            Craft::$app->getElements()->saveElement($node, true, false);
        }
    }

    public function onDeleteElement(ElementEvent $event)
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

    public function getParentOptions($nodes, $nav)
    {
        $maxLevels = $nav->maxLevels ?: false;

        $parentOptions[] = [
            'label' => '',
            'value' => 0
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
                'disabled' => ($maxLevels !== false && $node->level >= $maxLevels) ? true : false,
            ];
        }

        return $parentOptions;
    }

    public function afterSaveSiteHandler(SiteEvent $event)
    {
        $queue = Craft::$app->getQueue();
        $siteId = $event->oldPrimarySiteId;

        // Only propagate nodes if we want to for the nav
        $navs = Navigation::$plugin->getNavs()->getAllNavs();
        $nodes = [];

        foreach ($navs as $nav) {
            if ($nav->propagateNodes) {
                foreach (Navigation::$plugin->getNodes()->getNodesForNav($nav->id) as $node) {
                    $nodes[] = $node->id;
                }
            }
        }

        $elementTypes = [
            NodeElement::class,
        ];

        foreach ($elementTypes as $elementType) {
            $queue->push(new ResaveElements([
                'elementType' => $elementType,
                'criteria' => [
                    'id' => $nodes,
                    'siteId' => $siteId,
                    'status' => null,
                    'enabledForSite' => false
                ]
            ]));
        }
    }
}