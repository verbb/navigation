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

    public function getNodeById(int $id, $siteId = null)
    {
        return Craft::$app->getElements()->getElementById($id, NodeElement::class, $siteId);
    }

    public function getNodesForNav(int $navId, $siteId = null)
    {
        return NodeElement::find()
            ->navId($navId)
            ->status(null)
            ->siteId($siteId)
            ->enabledForSite(false)
            ->all();
    }

    public function onSaveElement(ElementEvent $event)
    {
        $element = $event->element;
        $isNew = $event->isNew;

        // We only care about already-existing elements
        if ($isNew) {
            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->status(null)
            ->type(get_class($element))
            ->siteId($element->siteId)
            ->all();

        foreach ($nodes as $node) {
            $currentElement = Craft::$app->getElements()->getElementById($element->id, null, $element->siteId);

            $node->enabled = (int)$element->enabled;

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