<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\base\ElementNodeType;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\events\CopyNodeToSiteEvent;
use verbb\navigation\helpers\DynamicSourceTypes;
use verbb\navigation\helpers\NodeTypeHelper;
use verbb\navigation\helpers\StructureLimits;
use verbb\navigation\models\NodeSiteSettings;
use verbb\navigation\nodetypes\Dynamic;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\db\Table;
use craft\elements\db\ElementQueryInterface;
use craft\events\CategoryGroupEvent;
use craft\events\DeleteElementEvent;
use craft\events\ElementEvent;
use craft\events\MoveElementEvent;
use craft\events\SectionEvent;
use craft\events\VolumeEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;

use yii\base\UserException;

use Throwable;

class Nodes extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_COPY_NODE_TO_SITE = 'beforeCopyNodeToSite';


    // Properties
    // =========================================================================

    private array $_tempNodes = [];
    private array $_linkedElementSaveState = [];
    private array $_nodesPendingHardDelete = [];
    private array $_syncingLinkedNodes = [];


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
            $node->publishPendingAdd();

            if (!$elementsService->saveElement($node)) {
                throw new UserException(Craft::t('navigation', 'Couldn’t save menu.'));
            }

            $count++;
        }

        return $count;
    }

    public function onBeforeSaveElement(ElementEvent $event): void
    {
        $element = $event->element;
        $key = $element->id . ':' . $element->siteId;
        unset($this->_linkedElementSaveState[$key]);

        // Skip this when updating Craft is currently in progress
        if (Craft::$app->getUpdates()->getAreMigrationsPending()) {
            return;
        }

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
            ->site('*')
            ->all();

        if ($nodes === []) {
            return;
        }

        // EVENT_BEFORE_SAVE_ELEMENT lets us compare against the persisted title/status.
        // Resolve it once, then share it across every linked node to avoid an N+1 lookup.
        $currentElement = Craft::$app->getElements()->getElementById(
            $element->id,
            get_class($element),
            $element->siteId,
        );

        // A source validation failure or veto must leave linked nodes untouched.
        $this->_linkedElementSaveState[$key] = [$nodes, $currentElement];
    }

    public function onSaveElement(ElementEvent $event): void
    {
        $element = $event->element;
        $key = $element->id . ':' . $element->siteId;
        [$nodes, $currentElement] = $this->_linkedElementSaveState[$key] ?? [[], null];
        unset($this->_linkedElementSaveState[$key]);
        $globalStatusChanged = $currentElement && $element->enabled !== $currentElement->enabled;
        $linkedElements = [(int)$element->siteId => $currentElement];

        foreach ($nodes as $node) {
            $linkedSiteId = $node->getElementSiteId();
            $sameSite = $linkedSiteId === (int)$element->siteId;

            if (!$sameSite && !$globalStatusChanged) {
                continue;
            }

            if (!$this->_canSyncLinkedNode($node)) {
                continue;
            }

            if ($sameSite) {
                if ($element->uri) {
                    $node->url = $element->uri;
                }

                // Only update titles that still mirror this linked locale.
                $node->setElement($currentElement);

                if (!$node->hasOverriddenTitle()) {
                    $node->title = $element->title;
                }
            }

            if ($currentElement && !$node->getIsPendingPublish()) {
                $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;

                // A global source toggle affects every linked locale. Resolve each
                // locale once, retaining its own enabled flag and authored title.
                if (!array_key_exists($linkedSiteId, $linkedElements)) {
                    $linkedElements[$linkedSiteId] = Craft::$app->getElements()->getElementById(
                        $element->id, get_class($element), $linkedSiteId,
                    );
                }

                $siteWasEnabled = $linkedElements[$linkedSiteId]?->getEnabledForSite() ?? false;
                $siteIsEnabled = $element->getEnabledForSite($linkedSiteId) ?? $siteWasEnabled;
                $nodeEnabled = $isMultiSite ? $node->getEnabledForSite() : $node->enabled;
                $elementEnabled = $element->enabled && $siteIsEnabled;
                $currentElementEnabled = $currentElement->enabled && $siteWasEnabled;

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

            $this->_saveLinkedNode($node);
        }
    }

    public function isSyncingLinkedNode(NodeElement $node): bool
    {
        return isset($this->_syncingLinkedNodes[spl_object_id($node)]);
    }

    public function onBeforeDeleteElement(DeleteElementEvent $event): void
    {
        $element = $event->element;
        unset($this->_nodesPendingHardDelete[$element->id]);
        $hardDelete = $event->hardDelete || (bool)($element->hardDelete ?? false);

        if (!$hardDelete) {
            return;
        }

        $typeClass = NodeTypeHelper::resolveTypeClass(get_class($element));

        if (!$typeClass) {
            return;
        }

        // The foreign key clears elementId on hard delete. Remember identities now,
        // but defer removal until the source's cancellable beforeDelete has passed.
        $this->_nodesPendingHardDelete[$element->id] = NodeElement::find()
            ->elementId($element->id)
            ->status(null)
            ->type($typeClass)
            ->site('*')
            ->unique()
            ->ids();
    }

    public function onDeleteElement(ElementEvent $event): void
    {
        $element = $event->element;
        $typeClass = NodeTypeHelper::resolveTypeClass(get_class($element));

        if (!$typeClass) {
            return;
        }

        if ((bool)($element->hardDelete ?? false)) {
            $nodeIds = $this->_nodesPendingHardDelete[$element->id] ?? [];
            unset($this->_nodesPendingHardDelete[$element->id]);

            if ($nodeIds) {
                $nodes = NodeElement::find()->id($nodeIds)->site('*')->unique()->status(null)->all();

                foreach ($nodes as $node) {
                    if (!Craft::$app->getElements()->deleteElement($node, true)) {
                        throw new UserException(Craft::t('navigation', 'Couldn’t delete node.'));
                    }
                }
            }

            return;
        }

        $nodes = NodeElement::find()
            ->elementId($element->id)
            ->status(null)
            ->type($typeClass)
            ->site('*')
            ->all();

        $this->_disableLinkedNodes($nodes);
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
            ->site('*')
            ->all();

        $dataById = [];
        foreach ($nodes as $node) {
            $node->data = $dataById[$node->id] ?? $node->data;

            if ($node->getIsDisabledByLinkedElement()) {
                $this->restoreNodeFromLinkedElement($node);
            }

            $dataById[$node->id] = $node->data;
        }
    }

    public function onDeleteSection(SectionEvent $event): void
    {
        $this->_handleSourceDelete(DynamicSourceTypes::SECTION, (int)$event->section->id);
    }

    public function reconcileLinkedNodesForMenuSites(int $menuId, array $siteIds): void
    {
        $nodes = NodeElement::find()->menuId($menuId)->siteId($siteIds)->status(null)->all();
        $dataById = [];
        $elementIds = array_values(array_unique(array_filter(ArrayHelper::getColumn($nodes, 'elementId'))));

        // Deletion events skip inactive menu sites. Check the shared element rows
        // once so reactivation cannot expose links whose sources are still trashed.
        $deletedIds = $elementIds ? array_fill_keys((new Query())
            ->select('id')
            ->from(Table::ELEMENTS)
            ->where(['id' => $elementIds])
            ->andWhere(['not', ['dateDeleted' => null]])
            ->column(), true) : [];

        foreach ($nodes as $node) {
            $node->data = $dataById[$node->id] ?? $node->data;

            if ($node->isElement() && isset($deletedIds[$node->elementId])) {
                $this->disableNodeForLinkedElement($node);
            } elseif ($node->getIsDisabledByLinkedElement() && $node->getElement()) {
                $this->restoreNodeFromLinkedElement($node);
            }

            $dataById[$node->id] = $node->data;
        }
    }

    public function onDeleteCategoryGroup(CategoryGroupEvent $event): void
    {
        $this->_handleSourceDelete(DynamicSourceTypes::CATEGORY_GROUP, (int)$event->categoryGroup->id);
    }

    public function onDeleteVolume(VolumeEvent $event): void
    {
        $this->_handleSourceDelete(DynamicSourceTypes::VOLUME, (int)$event->volume->id);
    }

    public function onDeleteProductType(int $productTypeId): void
    {
        $this->_handleSourceDelete(DynamicSourceTypes::PRODUCT_TYPE, $productTypeId);
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
        if (!$this->_canSyncLinkedNode($node) || $node->getIsDisabledByLinkedElement()) {
            return;
        }

        $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;
        $enabledForSite = $isMultiSite ? $node->getEnabledForSite() : $node->enabled;

        $node->setLinkedElementDisabledRestoreState($node->enabled, $enabledForSite);
        $node->setDisabledByLinkedElement(true);

        if ($isMultiSite) {
            $node->setEnabledForSite(false);
        } else {
            $node->enabled = false;
            $node->setEnabledForSite(true);
        }

        $this->_saveLinkedNode($node);
    }

    public function restoreNodeFromLinkedElement(NodeElement $node): void
    {
        if (!$this->_canSyncLinkedNode($node) || !$node->getIsDisabledByLinkedElement()) {
            return;
        }

        $state = $node->getLinkedElementDisabledRestoreState();
        $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;

        if ($isMultiSite) {
            $node->enabled = $state['enabled'];
            $node->setEnabledForSite($state['enabledForSite']);
        } else {
            // A formerly localized disabled state must remain disabled if the
            // menu now supports only one site and Craft uses the global flag.
            $node->enabled = $state['enabled'] && $state['enabledForSite'];
            $node->setEnabledForSite(true);
        }

        $node->clearLinkedElementDisabledState();
        $this->_saveLinkedNode($node);
    }

    public function onAfterSaveNode(ElementEvent $event): void
    {
        if (!($event->element instanceof NodeElement)) {
            return;
        }

        // Staging metadata does not change public output, but linked content updates
        // still affect live nodes whose menu deletion has only been staged.
        if ($event->element->getIsPendingPublish()
            || ($event->element->getIsPendingDelete() && !$this->isSyncingLinkedNode($event->element))) {
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

        StructureLimits::requireMove($event);

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
        $duplications = [];

        $this->_duplicateNodesQuery(
            $query,
            $deferPublish,
            $successCount,
            $failCount,
            $duplicatedSourceIds,
            $duplicatedNodeIds,
            $duplications,
            null,
            $deep,
        );

        return [
            'successCount' => $successCount,
            'failCount' => $failCount,
            'duplicatedNodeIds' => $duplicatedNodeIds,
            'duplications' => $duplications,
        ];
    }

    public function copyNodesToSite(
        int $menuId,
        int $sourceSiteId,
        array $nodeIds,
        int $targetSiteId,
        bool $deep = false,
        bool $remapLinkedElements = false,
    ): array {
        $nodeIds = array_values(array_unique(array_map('intval', $nodeIds)));

        if ($nodeIds === []) {
            return [
                'successCount' => 0,
                'failCount' => 0,
                'copiedNodeIds' => [],
                'remappedLinkedElementCount' => 0,
                'skippedLinkedElementRemapCount' => 0,
            ];
        }

        $query = NodeElement::find()
            ->id($nodeIds)
            ->menuId($menuId)
            ->siteId($sourceSiteId)
            ->status(null);

        if ($deep) {
            $query->orderBy(['structureelements.lft' => SORT_ASC]);
        }

        $successCount = 0;
        $failCount = 0;
        $copiedSourceToTargetIds = [];
        $copiedNodeIds = [];
        $remappedLinkedElementCount = 0;
        $skippedLinkedElementRemapCount = 0;

        $this->_copyNodesToSiteQuery(
            $query,
            $targetSiteId,
            $successCount,
            $failCount,
            $copiedSourceToTargetIds,
            $copiedNodeIds,
            null,
            $deep,
            $remapLinkedElements,
            $remappedLinkedElementCount,
            $skippedLinkedElementRemapCount,
        );

        return [
            'successCount' => $successCount,
            'failCount' => $failCount,
            'copiedNodeIds' => $copiedNodeIds,
            'remappedLinkedElementCount' => $remappedLinkedElementCount,
            'skippedLinkedElementRemapCount' => $skippedLinkedElementRemapCount,
        ];
    }

    public function copyNodeToSite(NodeElement $node, int $targetSiteId): ?NodeElement
    {
        $result = $this->copyNodesToSite(
            (int)$node->menuId,
            (int)$node->siteId,
            [(int)$node->id],
            $targetSiteId,
        );

        if ($result['successCount'] === 0 || $result['copiedNodeIds'] === []) {
            return null;
        }

        return $this->getNodeById($result['copiedNodeIds'][0], $targetSiteId);
    }


    // Private Methods
    // =========================================================================

    private function _canSyncLinkedNode(NodeElement $node): bool
    {
        // A settings change can leave localized rows until Craft next resaves the
        // node. Those unsupported locales must not block the source's lifecycle.
        $menu = Navigation::$plugin->getMenus()->getMenuById($node->menuId);

        return $menu && in_array($node->siteId, $menu->getSiteIds(), true);
    }

    private function _disableLinkedNodes(array $nodes): void
    {
        // Node data is shared across locales. Carry each saved site's restore
        // state forward for individual deletions and bulk source deletions alike.
        $dataById = [];

        foreach ($nodes as $node) {
            $node->data = $dataById[$node->id] ?? $node->data;
            $this->disableNodeForLinkedElement($node);
            $dataById[$node->id] = $node->data;
        }
    }

    private function _saveLinkedNode(NodeElement $node): void
    {
        $key = spl_object_id($node);
        $this->_syncingLinkedNodes[$key] = true;

        try {
            if (!Craft::$app->getElements()->saveElement($node, true, false)) {
                throw new UserException(Craft::t('navigation', 'Couldn’t save node.'));
            }
        } finally {
            unset($this->_syncingLinkedNodes[$key]);
        }
    }

    private function _handleSourceDelete(string $sourceType, int $sourceId): void
    {
        $this->onDeleteDynamicSource($sourceType, $sourceId);

        foreach (Navigation::$plugin->getNodeTypes()->getRegisteredElementNodeTypeClasses() as $typeClass) {
            $elementIds = $typeClass::getBulkSoftDeletedElementIds($sourceType, $sourceId);

            if ($elementIds === null) {
                continue;
            }

            $this->_disableLinkedElementNodes($typeClass, $elementIds);
        }
    }

    private function _disableLinkedElementNodes(string $typeClass, array $elementIds): void
    {
        if (!$elementIds) {
            return;
        }

        $nodes = NodeElement::find()
            ->type($typeClass::getStoredTypeValues())
            ->elementId($elementIds)
            ->status(null)
            ->site('*')
            ->all();

        $this->_disableLinkedNodes($nodes);
    }

    private function _duplicateNodesQuery(
        ElementQueryInterface $query,
        bool $deferPublish,
        int &$successCount,
        int &$failCount,
        array &$duplicatedSourceIds,
        array &$duplicatedNodeIds,
        array &$duplications,
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

            // A failed save or placement must not leave a pending copy outside its session.
            $transaction = Craft::$app->getDb()->beginTransaction();
            try {
                $duplicate = $elementsService->duplicateElement(
                    $element,
                    $this->_duplicateAttributesForBuilder($element, $deferPublish),
                );

                if (!$duplicate instanceof NodeElement) {
                    throw new UserException('Could not duplicate node.');
                }

                if ($deferPublish) {
                    $duplicate->enabled = false;
                    $duplicate->setEnabledForSite(false);
                    if (!$elementsService->saveElement($duplicate)) {
                        throw new UserException('Could not save pending duplicate.');
                    }
                }

                $placed = $newParent
                    ? $structuresService->append($element->structureId, $duplicate, $newParent)
                    : $structuresService->moveAfter($element->structureId, $duplicate, $element);
                if (!$placed) {
                    throw new UserException('Could not place duplicate.');
                }
                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();
                $failCount++;
                continue;
            }

            $successCount++;
            $duplicatedSourceIds[$element->id] = true;
            $duplicatedNodeIds[] = (int)$duplicate->id;

            // Root selections only — deep children are placed under `$duplicate` below.
            if ($newParent === null) {
                $duplications[] = [
                    'sourceId' => (int)$element->id,
                    'duplicateId' => (int)$duplicate->id,
                ];
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
                    $duplications,
                    $duplicate,
                    true,
                );
            }
        }
    }

    private function _copyNodesToSiteQuery(
        ElementQueryInterface $query,
        int $targetSiteId,
        int &$successCount,
        int &$failCount,
        array &$copiedSourceToTargetIds,
        array &$copiedNodeIds,
        ?NodeElement $newParent = null,
        bool $deep = false,
        bool $remapLinkedElements = false,
        int &$remappedLinkedElementCount = 0,
        int &$skippedLinkedElementRemapCount = 0,
    ): void {
        $elementsService = Craft::$app->getElements();
        $structuresService = Craft::$app->getStructures();

        foreach (Db::each($query) as $element) {
            if (!$element instanceof NodeElement) {
                continue;
            }

            if (isset($copiedSourceToTargetIds[$element->id])) {
                continue;
            }

            $transaction = Craft::$app->getDb()->beginTransaction();
            $previousRemapped = $remappedLinkedElementCount;
            $previousSkipped = $skippedLinkedElementRemapCount;
            try {
                $duplicate = $elementsService->duplicateElement($element, ['siteId' => $targetSiteId]);
                if (!$duplicate instanceof NodeElement) {
                    throw new UserException('Could not copy node.');
                }

                $this->_copyNodeSiteSettings(
                    $element, $duplicate, $targetSiteId, $remapLinkedElements,
                    $remappedLinkedElementCount, $skippedLinkedElementRemapCount,
                );

                $nav = Navigation::$plugin->getMenus()->getMenuById($element->menuId);
                $structureId = (int)$nav->structureId;
                $structureParent = $newParent;
                if (!$structureParent) {
                    $structureParent = $copiedSourceToTargetIds[$element->getParentId()] ?? null;
                }

                // Remove the inherited position and count success only after target placement.
                if (!$structuresService->remove($structureId, $duplicate)) {
                    throw new UserException('Could not remove inherited copy position.');
                }
                $placed = $structureParent
                    ? $structuresService->append($structureId, $duplicate, $structureParent)
                    : $structuresService->appendToRoot($structureId, $duplicate);
                if (!$placed) {
                    throw new UserException('Could not place copied node.');
                }
                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();
                $remappedLinkedElementCount = $previousRemapped;
                $skippedLinkedElementRemapCount = $previousSkipped;
                $failCount++;
                continue;
            }

            $successCount++;
            $copiedSourceToTargetIds[$element->id] = $duplicate;
            $copiedNodeIds[] = (int)$duplicate->id;

            if ($deep) {
                $childQuery = NodeElement::find()
                    ->siteId($element->siteId)
                    ->menuId($element->menuId)
                    ->descendantOf($element->id)
                    ->descendantDist(1)
                    ->status(null);

                $this->_copyNodesToSiteQuery(
                    $childQuery,
                    $targetSiteId,
                    $successCount,
                    $failCount,
                    $copiedSourceToTargetIds,
                    $copiedNodeIds,
                    $duplicate,
                    true,
                    $remapLinkedElements,
                    $remappedLinkedElementCount,
                    $skippedLinkedElementRemapCount,
                );
            }
        }
    }

    private function _copyNodeSiteSettings(
        NodeElement $source,
        NodeElement $duplicate,
        int $targetSiteId,
        bool $remapLinkedElements,
        int &$remappedLinkedElementCount,
        int &$skippedLinkedElementRemapCount,
    ): void {
        $sourceSettings = Navigation::$plugin->getNodeSites()->getSettings($source->id, $source->siteId);

        if (!$sourceSettings) {
            return;
        }

        $linkedElementSiteId = $sourceSettings->linkedElementSiteId;

        if ($remapLinkedElements && $source->elementId && $source->isElement() && $linkedElementSiteId) {
            $nodeType = $source->nodeType();

            if ($nodeType instanceof ElementNodeType) {
                $targetElement = Craft::$app->getElements()->getElementById(
                    $source->elementId,
                    $nodeType::getElementType(),
                    $targetSiteId,
                );

                if ($targetElement) {
                    $linkedElementSiteId = $targetSiteId;
                    $remappedLinkedElementCount++;
                } else {
                    $skippedLinkedElementRemapCount++;
                }
            }
        }

        $targetSettings = new NodeSiteSettings([
            'nodeId' => $duplicate->id,
            'siteId' => $targetSiteId,
            'linkedElementSiteId' => $linkedElementSiteId,
            'url' => $sourceSettings->url,
            'urlSuffix' => $sourceSettings->urlSuffix,
        ]);

        if ($this->hasEventHandlers(self::EVENT_BEFORE_COPY_NODE_TO_SITE)) {
            $event = new CopyNodeToSiteEvent([
                'source' => $source,
                'duplicate' => $duplicate,
                'targetSiteId' => $targetSiteId,
                'targetSettings' => $targetSettings,
                'remapLinkedElements' => $remapLinkedElements,
            ]);

            $this->trigger(self::EVENT_BEFORE_COPY_NODE_TO_SITE, $event);

            if (!$event->isValid) {
                throw new UserException(Craft::t('navigation', 'Node copy cancelled.'));
            }

            $targetSettings = $event->targetSettings;
        }

        if (!Navigation::$plugin->getNodeSites()->saveSettings($targetSettings)) {
            throw new UserException('Could not save copied node site settings.');
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
