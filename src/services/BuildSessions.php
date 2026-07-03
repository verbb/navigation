<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\BuildSession as BuildSessionModel;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\records\BuildSession as BuildSessionRecord;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\helpers\Json;

use yii\base\UserException;
use yii\web\BadRequestHttpException;

class BuildSessions extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Whether the builder uses persisted staging (default). Deprecated `builderLiveStructure` opts out.
     */
    public function isStagingEnabled(): bool
    {
        return !Navigation::$plugin->getSettings()->builderLiveStructure;
    }

    public function getSession(int $menuId, int $siteId, ?int $userId = null): ?BuildSessionModel
    {
        $userId = $this->_resolveUserId($userId, false);

        if (!$userId) {
            return null;
        }

        $record = BuildSessionRecord::findOne([
            'menuId' => $menuId,
            'siteId' => $siteId,
            'userId' => $userId,
        ]);

        if (!$record) {
            return null;
        }

        $session = $this->_recordToModel($record);

        return $session;
    }

    public function getOrCreate(int $menuId, int $siteId, ?int $userId = null): BuildSessionModel
    {
        $session = $this->getSession($menuId, $siteId, $userId);

        if ($session) {
            return $session;
        }

        $userId = $this->_resolveUserId($userId);

        $session = new BuildSessionModel([
            'menuId' => $menuId,
            'siteId' => $siteId,
            'userId' => $userId,
        ]);

        $this->saveSession($session);

        return $session;
    }

    public function saveSession(BuildSessionModel $session): bool
    {
        if ($session->id) {
            $record = BuildSessionRecord::findOne($session->id);
        } else {
            $record = new BuildSessionRecord();
        }

        if (!$record) {
            return false;
        }

        $record->menuId = (int)$session->menuId;
        $record->siteId = (int)$session->siteId;
        $record->userId = (int)$session->userId;
        $record->structureMoves = $session->structureMoves !== [] ? Json::encode($session->structureMoves) : null;
        $record->addedNodeIds = $session->addedNodeIds !== [] ? Json::encode($session->addedNodeIds) : null;
        $record->stagedDeletes = $session->stagedDeletes !== [] ? Json::encode($session->stagedDeletes) : null;
        $record->menuDraftId = $session->menuDraftId;
        $record->menuContentDraft = $session->menuContentDraft !== [] ? Json::encode($session->menuContentDraft) : null;
        $record->nodeDraftMap = $session->nodeDraftMap !== [] ? Json::encode($session->nodeDraftMap) : null;

        if (!$record->save()) {
            return false;
        }

        $session->id = (int)$record->id;
        $session->uid = $record->uid;

        return true;
    }

    public function getChangeCount(int $menuId, int $siteId, ?int $userId = null, bool $includeStructure = true): int
    {
        if (!$this->isStagingEnabled()) {
            return 0;
        }

        $session = $this->getSession($menuId, $siteId, $userId);

        return $session?->getChangeCount($includeStructure) ?? 0;
    }

    public function addAddedNode(BuildSessionModel $session, int $nodeId): void
    {
        if (!in_array($nodeId, $session->addedNodeIds, true)) {
            $session->addedNodeIds[] = $nodeId;
        }

        $this->saveSession($session);
    }

    public function stageAddedNodes(BuildSessionModel $session, array $nodeIds): void
    {
        foreach ($nodeIds as $nodeId) {
            if (!in_array($nodeId, $session->addedNodeIds, true)) {
                $session->addedNodeIds[] = $nodeId;
            }
        }

        $this->saveSession($session);
    }

    public function setStructureMoves(BuildSessionModel $session, array $moves): void
    {
        $session->structureMoves = $moves;
        $this->saveSession($session);
    }

    public function clearStructureMoves(BuildSessionModel $session): void
    {
        $session->structureMoves = [];
        $this->saveSession($session);
    }

    /**
     * Stage a node for deletion on publish. Newly-added session nodes are removed immediately.
     */
    public function stageDelete(BuildSessionModel $session, NodeElement $node, bool $withDescendants = false): void
    {
        $elementsService = Craft::$app->getElements();
        $nodesToStage = [$node];

        if ($withDescendants) {
            foreach ($node->getDescendants()->status(null)->all() as $descendant) {
                if ($descendant instanceof NodeElement) {
                    $nodesToStage[] = $descendant;
                }
            }
        }

        foreach ($nodesToStage as $nodeToStage) {
            $nodeId = (int)$nodeToStage->id;

            if (in_array($nodeId, $session->addedNodeIds, true)) {
                $session->addedNodeIds = array_values(array_filter(
                    $session->addedNodeIds,
                    fn(int $id) => $id !== $nodeId,
                ));

                $elementsService->deleteElement($nodeToStage, true);

                continue;
            }

            if ($this->_findStagedDelete($session, $nodeId)) {
                continue;
            }

            $enabled = (bool)$nodeToStage->enabled;
            $enabledForSite = (bool)$nodeToStage->getEnabledForSite();

            $session->stagedDeletes[] = [
                'nodeId' => $nodeId,
                'enabled' => $enabled,
                'enabledForSite' => $enabledForSite,
            ];

            $nodeToStage->setPendingDeleteRestoreState($enabled, $enabledForSite);
            $nodeToStage->setPendingDelete(true);
            $nodeToStage->enabled = false;
            $nodeToStage->setEnabledForSite(false);

            if (!$elementsService->saveElement($nodeToStage)) {
                throw new UserException(Craft::t('navigation', 'Couldn’t stage node for deletion.'));
            }
        }

        $this->saveSession($session);
    }

    /**
     * Undo a staged deletion for a single node.
     */
    public function unstageDelete(BuildSessionModel $session, NodeElement $node): void
    {
        $nodeId = (int)$node->id;
        $stagedDelete = $this->_findStagedDelete($session, $nodeId);

        if (!$stagedDelete) {
            if (!$node->getIsPendingDelete()) {
                throw new UserException(Craft::t('navigation', 'Couldn’t restore node.'));
            }

            $stagedDelete = $node->getPendingDeleteRestoreState();
            $stagedDelete['nodeId'] = $nodeId;
        }

        $session->stagedDeletes = array_values(array_filter(
            $session->stagedDeletes,
            fn(array $entry) => (int)$entry['nodeId'] !== $nodeId,
        ));

        $node->clearPendingDelete();
        $node->enabled = (bool)$stagedDelete['enabled'];
        $node->setEnabledForSite((bool)$stagedDelete['enabledForSite']);

        if (!Craft::$app->getElements()->saveElement($node)) {
            throw new UserException(Craft::t('navigation', 'Couldn’t restore node.'));
        }

        if ($session->getChangeCount(true) === 0) {
            $this->deleteSession($session);
        } else {
            $this->saveSession($session);
        }
    }

    public function publish(BuildSessionModel $session, bool $applyStructure = false, ?array $moves = null): array
    {
        $menuId = (int)$session->menuId;
        $siteId = (int)$session->siteId;
        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        if ($moves !== null) {
            $session->structureMoves = $moves;
        }

        $structureMoves = $applyStructure ? $session->structureMoves : [];
        $pendingCount = count($session->addedNodeIds) + count($session->stagedDeletes);

        if (!$applyStructure && $structureMoves === [] && $pendingCount === 0) {
            $this->deleteSession($session);

            return ['publishedCount' => 0, 'deletedCount' => 0];
        }

        if ($applyStructure && $structureMoves === []) {
            throw new BadRequestHttpException('Invalid moves payload.');
        }

        $nodesService = Navigation::$plugin->getNodes();
        $structuresService = Craft::$app->getStructures();
        $elementsService = Craft::$app->getElements();
        $levels = [];
        $publishedCount = 0;
        $deletedCount = 0;

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            foreach ($session->addedNodeIds as $nodeId) {
                /* @var NodeElement|null $node */
                $node = NodeElement::find()
                    ->id($nodeId)
                    ->siteId($siteId)
                    ->menuId($menuId)
                    ->status(null)
                    ->one();

                if (!$node) {
                    continue;
                }

                $node->enabled = true;
                $node->setEnabledForSite(true);
                $node->clearPendingPublish();

                if (!$elementsService->saveElement($node)) {
                    throw new UserException(Craft::t('navigation', 'Couldn’t save menu.'));
                }

                $publishedCount++;
            }

            foreach ($session->stagedDeletes as $stagedDelete) {
                $nodeId = (int)$stagedDelete['nodeId'];

                /* @var NodeElement|null $node */
                $node = NodeElement::find()
                    ->id($nodeId)
                    ->siteId($siteId)
                    ->menuId($menuId)
                    ->status(null)
                    ->one();

                if (!$node) {
                    continue;
                }

                $node->clearPendingDelete();

                if (!$elementsService->deleteElement($node, true)) {
                    throw new UserException(Craft::t('navigation', 'Couldn’t save menu.'));
                }

                $deletedCount++;
            }

            if ($applyStructure && $structureMoves !== []) {
                foreach ($structureMoves as $move) {
                    $elementId = (int)($move['elementId'] ?? 0);
                    $parentId = isset($move['parentId']) && $move['parentId'] !== '' ? (int)$move['parentId'] : null;
                    $prevId = isset($move['prevId']) && $move['prevId'] !== '' ? (int)$move['prevId'] : null;

                    if (!$elementId) {
                        throw new BadRequestHttpException('Invalid move payload.');
                    }

                    /* @var NodeElement|null $element */
                    $element = NodeElement::find()
                        ->id($elementId)
                        ->siteId($siteId)
                        ->menuId($menuId)
                        ->status(null)
                        ->structureId($nav->structureId)
                        ->one();

                    if (!$element) {
                        throw new BadRequestHttpException("Invalid node ID: $elementId");
                    }

                    $parentLevel = $parentId ? ($levels[$parentId] ?? 0) : 0;
                    $level = $parentLevel + 1;
                    $levels[$elementId] = $level;

                    if ($nav->maxLevels && $level > $nav->maxLevels) {
                        throw new BadRequestHttpException(Craft::t('navigation', 'Maximum menu depth exceeded.'));
                    }

                    if ($prevId) {
                        $prevElement = $elementsService->getElementById($prevId, NodeElement::class, $siteId);

                        if (!$prevElement || $prevElement->menuId !== $menuId) {
                            throw new BadRequestHttpException("Invalid previous node ID: $prevId");
                        }

                        if (!$structuresService->moveAfter($nav->structureId, $element, $prevElement)) {
                            throw new BadRequestHttpException(Craft::t('navigation', 'Couldn’t save menu structure.'));
                        }
                    } elseif ($parentId) {
                        $parentElement = $elementsService->getElementById($parentId, NodeElement::class, $siteId);

                        if (!$parentElement || $parentElement->menuId !== $menuId) {
                            throw new BadRequestHttpException("Invalid parent node ID: $parentId");
                        }

                        if (!$structuresService->prepend($nav->structureId, $element, $parentElement)) {
                            throw new BadRequestHttpException(Craft::t('navigation', 'Couldn’t save menu structure.'));
                        }
                    } elseif (!$structuresService->prependToRoot($nav->structureId, $element)) {
                        throw new BadRequestHttpException(Craft::t('navigation', 'Couldn’t save menu structure.'));
                    }
                }
            }

            $this->deleteSession($session);
            $transaction->commit();
        } catch (BadRequestHttpException $e) {
            $transaction->rollBack();

            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        Navigation::$plugin->getNavigationCache()->invalidateMenuSite($nav->uid, $siteId);

        return [
            'publishedCount' => $publishedCount,
            'deletedCount' => $deletedCount,
        ];
    }

    public function discard(BuildSessionModel $session): void
    {
        $menuId = (int)$session->menuId;
        $siteId = (int)$session->siteId;

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);
        $elementsService = Craft::$app->getElements();

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            foreach ($session->addedNodeIds as $nodeId) {
                /* @var NodeElement|null $node */
                $node = NodeElement::find()
                    ->id($nodeId)
                    ->siteId($siteId)
                    ->menuId($menuId)
                    ->status(null)
                    ->one();

                if ($node) {
                    $elementsService->deleteElement($node, true);
                }
            }

            foreach ($session->stagedDeletes as $stagedDelete) {
                $nodeId = (int)$stagedDelete['nodeId'];

                /* @var NodeElement|null $node */
                $node = NodeElement::find()
                    ->id($nodeId)
                    ->siteId($siteId)
                    ->menuId($menuId)
                    ->status(null)
                    ->one();

                if (!$node) {
                    continue;
                }

                $node->clearPendingDelete();
                $node->enabled = (bool)$stagedDelete['enabled'];
                $node->setEnabledForSite((bool)$stagedDelete['enabledForSite']);

                if (!$elementsService->saveElement($node)) {
                    throw new UserException(Craft::t('navigation', 'Couldn’t discard build session.'));
                }
            }

            $this->deleteSession($session);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        if ($nav) {
            Navigation::$plugin->getNavigationCache()->invalidateMenuSite($nav->uid, $siteId);
        }
    }

    public function deleteSession(BuildSessionModel $session): void
    {
        if (!$session->id) {
            return;
        }

        BuildSessionRecord::deleteAll(['id' => $session->id]);
        $session->id = null;
    }

    public function setMenuContentDraft(BuildSessionModel $session, array $draft): void
    {
        $session->menuContentDraft = $draft;
        $this->saveSession($session);
    }

    public function clearMenuContentDraft(BuildSessionModel $session): void
    {
        $session->menuContentDraft = [];
        $this->saveSession($session);
    }

    public function saveDraft(
        BuildSessionModel $session,
        ?array $structureMoves = null,
        ?array $menuContentDraft = null,
    ): void {
        if ($structureMoves !== null) {
            $session->structureMoves = $structureMoves;
        }

        if ($menuContentDraft !== null) {
            $session->menuContentDraft = $menuContentDraft;
        }

        $this->saveSession($session);
    }

    public function sessionToArray(BuildSessionModel $session): array
    {
        return Navigation::$plugin->getBuilderState()->sessionToArray($session);
    }


    // Private Methods
    // =========================================================================

    private function _recordToModel(BuildSessionRecord $record): BuildSessionModel
    {
        return new BuildSessionModel([
            'id' => (int)$record->id,
            'menuId' => (int)$record->menuId,
            'siteId' => (int)$record->siteId,
            'userId' => (int)$record->userId,
            'structureMoves' => Json::decodeIfJson($record->structureMoves) ?? [],
            'addedNodeIds' => Json::decodeIfJson($record->addedNodeIds) ?? [],
            'stagedDeletes' => Json::decodeIfJson($record->stagedDeletes) ?? [],
            'menuDraftId' => $record->menuDraftId ? (int)$record->menuDraftId : null,
            'menuContentDraft' => Json::decodeIfJson($record->menuContentDraft) ?? [],
            'nodeDraftMap' => Json::decodeIfJson($record->nodeDraftMap) ?? [],
            'uid' => $record->uid,
        ]);
    }

    private function _findStagedDelete(BuildSessionModel $session, int $nodeId): ?array
    {
        foreach ($session->stagedDeletes as $stagedDelete) {
            if ((int)$stagedDelete['nodeId'] === $nodeId) {
                return $stagedDelete;
            }
        }

        return null;
    }

    private function _resolveUserId(?int $userId, bool $required = true): int
    {
        $userId ??= (int)Craft::$app->getUser()->getId();

        if ($userId) {
            return $userId;
        }

        $admin = \craft\elements\User::find()->admin()->status(null)->one();

        if ($admin) {
            return (int)$admin->id;
        }

        if ($required) {
            throw new UserException(Craft::t('navigation', 'Couldn’t resolve build session user.'));
        }

        return 0;
    }
}
