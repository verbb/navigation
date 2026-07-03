<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\helpers\DynamicSourceTypes;
use verbb\navigation\helpers\NodeTypeHelper;
use verbb\navigation\nodetypes\Dynamic;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\db\ElementQueryInterface;
use craft\events\CategoryGroupEvent;
use craft\events\DeleteElementEvent;
use craft\events\ElementEvent;
use craft\events\MoveElementEvent;
use craft\events\SectionEvent;
use craft\events\VolumeEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\ElementHelper;

use Throwable;
use yii\base\UserException;

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

    public function getNodesForNav($menuId, $siteId = null, $includeTemp = false): array
    {
        $nodes = NodeElement::find()
            ->menuId($menuId)
            ->status(null)
            ->siteId($siteId)
            ->status(null)
            ->all();

        if ($includeTemp) {
            $nodes = array_merge($nodes, $this->_tempNodes);
        }

        return $nodes;
    }

    public function getPendingPublishNodes(int $menuId, ?int $siteId = null): array
    {
        $nodes = $this->getNodesForNav($menuId, $siteId);

        return array_values(array_filter(
            $nodes,
            fn(NodeElement $node) => $node->getIsPendingPublish(),
        ));
    }

    public function getPendingPublishCount(int $menuId, ?int $siteId = null): int
    {
        return count($this->getPendingPublishNodes($menuId, $siteId));
    }

    public function getPendingDeleteNodes(int $menuId, ?int $siteId = null): array
    {
        $nodes = $this->getNodesForNav($menuId, $siteId);

        return array_values(array_filter(
            $nodes,
            fn(NodeElement $node) => $node->getIsPendingDelete(),
        ));
    }

    public function getPendingDeleteCount(int $menuId, ?int $siteId = null): int
    {
        return count($this->getPendingDeleteNodes($menuId, $siteId));
    }

    /**
     * Enables nodes flagged during bulk add in deferred builder mode.
     *
     * @throws UserException
     */
    public function publishPendingNodes(int $menuId, int $siteId): int
    {
        $elementsService = Craft::$app->getElements();
        $count = 0;

        foreach ($this->getPendingPublishNodes($menuId, $siteId) as $node) {
            $node->enabled = true;
            $node->setEnabledForSite(true);
            $node->clearPendingPublish();

            if (!$elementsService->saveElement($node)) {
                throw new UserException(Craft::t('navigation', 'Couldn’t save menu.'));
            }

            $count++;
        }

        return $count;
    }

    public function onSaveElement(ElementEvent $event): void
    {
        // Skip this when updating Craft is currently in progress
        if (Craft::$app->getUpdates()->getAreMigrationsPending()) {
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

        // Ignore any drafts
        if ($element->getIsDraft()) {
            return;
        }

        $typeClass = NodeTypeHelper::resolveTypeClass(get_class($element));

        if (!$typeClass) {
            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->status(null)
            ->type($typeClass)
            ->all();

        foreach ($nodes as $node) {
            if ($node->getElementSiteId() !== (int)$element->siteId) {
                continue;
            }

            // If no nav for the node, skip. Just to protect against nodes in some cases
            $nav = Navigation::$plugin->getMenus()->getMenuById($node->menuId);

            if (!$nav) {
                continue;
            }

            // Do not sync nodes for sites where this nav is disabled (avoids UnsupportedSiteException)
            $supportedSites = ElementHelper::supportedSitesForElement($node);
            $supportedSiteIds = ArrayHelper::getColumn($supportedSites, 'siteId');

            if (!in_array($node->siteId, $supportedSiteIds, false)) {
                continue;
            }

            $currentElement = Craft::$app->getElements()->getElementById($element->id, get_class($element), $element->siteId);

            if ($element->uri) {
                $node->url = $element->uri;
            }

            // Only update the node title when it still mirrors the linked element title.
            if (!$node->hasOverriddenTitle()) {
                $node->title = $element->title;
            }

            if ($currentElement) {
                $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;

                // Sync the enabled status - if it's changed. Note that there's an inconsistency with reporting of a node is enabled for multi-site
                $nodeEnabled = $isMultiSite ? $node->getEnabledForSite() : $node->enabled;
                $elementEnabled = $isMultiSite ? $element->getEnabledForSite() : $element->enabled;
                $currentElementEnabled = $isMultiSite ? $currentElement->getEnabledForSite() : $currentElement->enabled;

                // Is the status different between the element and the node?
                if ($elementEnabled !== $currentElementEnabled && $elementEnabled !== $nodeEnabled) {
                    if ($isMultiSite) {
                        $node->enabled = true;
                        $node->setEnabledForSite($elementEnabled);
                    } else {
                        $node->enabled = $elementEnabled;
                        $node->setEnabledForSite(true);
                    }
                }
            }

            $node->setElementSiteId($element->siteId);

            Craft::$app->getElements()->saveElement($node, true, false);
        }
    }

    public function onBeforeDeleteElement(DeleteElementEvent $event): void
    {
        $element = $event->element;
        $hardDelete = $event->hardDelete || (bool)($element->hardDelete ?? false);

        if (!$hardDelete) {
            return;
        }

        $typeClass = NodeTypeHelper::resolveTypeClass(get_class($element));

        if (!$typeClass) {
            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->status(null)
            ->type($typeClass)
            ->site('*')
            ->all();

        foreach ($nodes as $node) {
            if ($node->getElementSiteId() !== (int)$element->siteId) {
                continue;
            }

            Craft::$app->getElements()->deleteElement($node, true);
        }
    }

    public function onDeleteElement(ElementEvent $event): void
    {
        $element = $event->element;
        $typeClass = NodeTypeHelper::resolveTypeClass(get_class($element));

        if (!$typeClass) {
            return;
        }

        if ((bool)($element->hardDelete ?? false)) {
            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->status(null)
            ->type($typeClass)
            ->site('*')
            ->all();

        foreach ($nodes as $node) {
            if ($node->getElementSiteId() !== (int)$element->siteId) {
                continue;
            }

            $this->disableNodeForLinkedElement($node);
        }
    }

    public function onRestoreElement(ElementEvent $event): void
    {
        $element = $event->element;
        $typeClass = NodeTypeHelper::resolveTypeClass(get_class($element));

        if (!$typeClass) {
            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->status(null)
            ->type($typeClass)
            ->all();

        foreach ($nodes as $node) {
            if ($node->getElementSiteId() !== (int)$element->siteId) {
                continue;
            }

            if ($node->getIsDisabledByLinkedElement()) {
                $this->restoreNodeFromLinkedElement($node);
            }
        }
    }

    public function onDeleteSection(SectionEvent $event): void
    {
        $this->onDeleteDynamicSource(DynamicSourceTypes::SECTION, (int)$event->section->id);
    }

    public function onDeleteCategoryGroup(CategoryGroupEvent $event): void
    {
        $this->onDeleteDynamicSource(DynamicSourceTypes::CATEGORY_GROUP, (int)$event->categoryGroup->id);
    }

    public function onDeleteVolume(VolumeEvent $event): void
    {
        $this->onDeleteDynamicSource(DynamicSourceTypes::VOLUME, (int)$event->volume->id);
    }

    public function onDeleteProductType(int $productTypeId): void
    {
        $this->onDeleteDynamicSource(DynamicSourceTypes::PRODUCT_TYPE, $productTypeId);
    }

    public function onDeleteDynamicSource(string $sourceType, int $sourceId): void
    {
        $nodes = NodeElement::find()
            ->type(Dynamic::class)
            ->status(null)
            ->site('*')
            ->all();

        foreach ($nodes as $node) {
            if (!Navigation::$plugin->getDynamicSources()->shouldDeleteNodeOnSourceDelete($node, $sourceType, $sourceId)) {
                continue;
            }

            Craft::$app->getElements()->deleteElement($node, true);
        }
    }

    public function disableNodeForLinkedElement(NodeElement $node): void
    {
        if ($node->getIsDisabledByLinkedElement()) {
            return;
        }

        $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;
        $enabledForSite = $isMultiSite ? $node->getEnabledForSite() : $node->enabled;

        $node->setLinkedElementDisabledRestoreState($node->enabled, $enabledForSite);
        $node->setDisabledByLinkedElement(true);

        if ($isMultiSite) {
            $node->enabled = true;
            $node->setEnabledForSite(false);
        } else {
            $node->enabled = false;
            $node->setEnabledForSite(true);
        }

        Craft::$app->getElements()->saveElement($node, true, false);
    }

    public function restoreNodeFromLinkedElement(NodeElement $node): void
    {
        if (!$node->getIsDisabledByLinkedElement()) {
            return;
        }

        $state = $node->getLinkedElementDisabledRestoreState();
        $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;

        if ($isMultiSite) {
            $node->enabled = $state['enabled'];
            $node->setEnabledForSite($state['enabledForSite']);
        } else {
            $node->enabled = $state['enabled'];
            $node->setEnabledForSite(true);
        }

        $node->clearLinkedElementDisabledState();
        Craft::$app->getElements()->saveElement($node, true, false);
    }

    public function onAfterSaveNode(ElementEvent $event): void
    {
        if (!($event->element instanceof NodeElement)) {
            return;
        }

        // Pending bulk adds and staged deletes are not front-end visible; defer cache churn until publish.
        if ($event->element->getIsPendingPublish() || $event->element->getIsPendingDelete()) {
            return;
        }

        $this->_invalidateNodeCache($event->element);
    }

    public function onAfterDeleteNode(ElementEvent $event): void
    {
        if (!($event->element instanceof NodeElement)) {
            return;
        }

        $this->_invalidateNodeCache($event->element);
    }

    public function onAfterSaveProjectedContent(ElementEvent $event): void
    {
        $this->_invalidateProjectedContentCache($event->element);
    }

    public function onAfterDeleteProjectedContent(ElementEvent $event): void
    {
        $this->_invalidateProjectedContentCache($event->element);
    }

    public function onMoveElement(MoveElementEvent $event): void
    {
        if (!($event->element instanceof NodeElement)) {
            return;
        }

        $nav = Navigation::$plugin->getMenus()->getMenuById($event->element->menuId);

        // The element we're moving won't have its destination level set yet, 
        // so use the target element (where we're moving to) to deduce that.
        $event->element->level = $event->getTargetElement()->level ?? $event->element->level;

        // Check if we are adding a new node to a parent. It's more complicated than it should
        // as `getTargetElement()` doesn't report the new level.
        if ($event->getTargetElement() && $event->action === 'prepend') {
            $event->element->level++;
        }

        if ($nav->maxNodesSettings) {
            Navigation::$plugin->getNodes()->setTempNodes([$event->element]);

            if ($nav->isOverMaxLevel($event->element, $event->getTargetElement())) {
                throw new UserException('Unable to move node due to the maximum nodes per level.');
            }
        }

        $this->_invalidateNodeCache($event->element);
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

    public function duplicateNodesForBuilder(
        int $menuId,
        int $siteId,
        array $nodeIds,
        bool $deep,
        bool $deferPublish = false,
    ): array {
        $query = NodeElement::find()
            ->id($nodeIds)
            ->menuId($menuId)
            ->siteId($siteId)
            ->status(null);

        if ($deep) {
            $query->orderBy(['structureelements.lft' => SORT_ASC]);
        }

        $successCount = 0;
        $failCount = 0;
        $duplicatedSourceIds = [];
        $duplicatedNodeIds = [];

        $this->_duplicateNodesQuery(
            $query,
            $deferPublish,
            $successCount,
            $failCount,
            $duplicatedSourceIds,
            $duplicatedNodeIds,
            null,
            $deep,
        );

        return [
            'successCount' => $successCount,
            'failCount' => $failCount,
            'duplicatedNodeIds' => $duplicatedNodeIds,
        ];
    }

    public function copyNodeToSite(NodeElement $node, int $targetSiteId): ?NodeElement
    {
        $duplicate = Craft::$app->getElements()->duplicateElement($node, [
            'siteId' => $targetSiteId,
        ]);

        if (!$duplicate instanceof NodeElement) {
            return null;
        }

        $sourceSettings = Navigation::$plugin->getNodeSites()->getSettings($node->id, $node->siteId);

        if ($sourceSettings) {
            $targetSettings = new \verbb\navigation\models\NodeSiteSettings([
                'nodeId' => $duplicate->id,
                'siteId' => $targetSiteId,
                'linkedElementSiteId' => $sourceSettings->linkedElementSiteId,
                'url' => $sourceSettings->url,
                'urlSuffix' => $sourceSettings->urlSuffix,
            ]);

            Navigation::$plugin->getNodeSites()->saveSettings($targetSettings);
        }

        return $duplicate;
    }


    // Private Methods
    // =========================================================================

    private function _duplicateNodesQuery(
        ElementQueryInterface $query,
        bool $deferPublish,
        int &$successCount,
        int &$failCount,
        array &$duplicatedSourceIds,
        array &$duplicatedNodeIds,
        ?ElementInterface $newParent = null,
        bool $deep = false,
    ): void {
        $elementsService = Craft::$app->getElements();
        $structuresService = Craft::$app->getStructures();

        foreach (Db::each($query) as $element) {
            if (!$element instanceof NodeElement) {
                continue;
            }

            if (isset($duplicatedSourceIds[$element->id])) {
                continue;
            }

            try {
                $duplicate = $elementsService->duplicateElement(
                    $element,
                    $this->_duplicateAttributesForBuilder($element, $deferPublish),
                );
            } catch (Throwable) {
                $failCount++;
                continue;
            }

            if (!$duplicate instanceof NodeElement) {
                $failCount++;
                continue;
            }

            if ($deferPublish && $duplicate->getEnabledForSite()) {
                $duplicate->setEnabledForSite(false);

                if (!$elementsService->saveElement($duplicate)) {
                    $failCount++;
                    continue;
                }
            }

            $successCount++;
            $duplicatedSourceIds[$element->id] = true;
            $duplicatedNodeIds[] = (int)$duplicate->id;

            if ($newParent) {
                $structuresService->append($element->structureId, $duplicate, $newParent);
            } elseif ($element->structureId) {
                $structuresService->moveAfter($element->structureId, $duplicate, $element);
            }

            if ($deep) {
                $childQuery = NodeElement::find()
                    ->siteId($element->siteId)
                    ->menuId($element->menuId)
                    ->descendantOf($element->id)
                    ->descendantDist(1)
                    ->status(null);

                $this->_duplicateNodesQuery(
                    $childQuery,
                    $deferPublish,
                    $successCount,
                    $failCount,
                    $duplicatedSourceIds,
                    $duplicatedNodeIds,
                    $duplicate,
                    true,
                );
            }
        }
    }

    private function _duplicateAttributesForBuilder(NodeElement $element, bool $deferPublish): array
    {
        $attributes = [
            'isProvisionalDraft' => false,
            'draftId' => null,
        ];

        if (!$deferPublish) {
            return $attributes;
        }

        $attributes['enabled'] = false;

        $data = $element->data ?? [];
        unset(
            $data[NodeElement::PENDING_DELETE_DATA_KEY],
            $data[NodeElement::PENDING_DELETE_STATE_DATA_KEY],
        );
        $data[NodeElement::PENDING_PUBLISH_DATA_KEY] = true;
        $attributes['data'] = $data;

        return $attributes;
    }

    private function _invalidateNodeCache(NodeElement $node): void
    {
        $nav = Navigation::$plugin->getMenus()->getMenuById($node->menuId);

        if (!$nav) {
            return;
        }

        Navigation::$plugin->getNavigationCache()->invalidateNode($node->id, $nav->uid, $node->siteId);
    }

    private function _invalidateProjectedContentCache(\craft\base\ElementInterface $element): void
    {
        if ($element instanceof NodeElement || $element->getIsDraft()) {
            return;
        }

        $tags = Navigation::$plugin->getDynamicSources()->getCacheTagsForProjectedElement($element);

        if ($tags === []) {
            return;
        }

        Navigation::$plugin->getNavigationCache()->invalidateTags($tags);
    }
}
