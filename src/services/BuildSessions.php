<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\helpers\StructureLimits;
use verbb\navigation\models\BuildSession as BuildSessionModel;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\records\BuildSession as BuildSessionRecord;

use Craft;
use craft\base\Component;
use craft\elements\User;
use craft\helpers\Json;

use yii\base\UserException;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;

use Throwable;

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
        $userId = $this->_resolveUserId($userId);

        return Craft::$app->getDb()->transaction(function() use ($menuId, $siteId, $userId) {
            $this->_lockMenu($menuId);
            // A locking read sees the latest committed row even inside a repeatable-read transaction.
            $record = BuildSessionRecord::findBySql(
                'SELECT * FROM {{%navigation_build_sessions}} WHERE [[menuId]] = :menu AND [[siteId]] = :site AND [[userId]] = :user FOR UPDATE',
                [':menu' => $menuId, ':site' => $siteId, ':user' => $userId],
            )->one();

            if ($record) {
                return $this->_recordToModel($record);
            }

            $session = new BuildSessionModel([
                'menuId' => $menuId,
                'siteId' => $siteId,
                'userId' => $userId,
            ]);
            $this->saveSession($session);

            return $session;
        });
    }

    public function saveSession(BuildSessionModel $session): bool
    {
        return $this->_withSession($session, fn($record) => $this->_saveSession($session, $record));
    }

    /** Pending flags are shared, but only their owning menu/site session may author them. */
    public function canAuthorPendingNode(NodeElement $node, int $userId): bool
    {
        if (!$node->id || !$this->isStagingEnabled()) {
            return true;
        }

        $nodeId = (int)($node->canonicalId ?: $node->id);
        foreach (BuildSessionRecord::find()->where(['menuId' => $node->menuId])->all() as $record) {
            $session = $this->_recordToModel($record);
            if ((in_array($nodeId, $session->addedNodeIds, true)
                || $this->_findStagedDelete($session, $nodeId))
                && ((int)$session->userId !== $userId || (int)$session->siteId !== (int)$node->siteId)) {
                return false;
            }
        }

        return true;
    }

    /** A native manual publish must stop belonging to the pending-add session. */
    public function releasePublishedAddition(NodeElement $node): void
    {
        foreach (BuildSessionRecord::find()->where(['menuId' => $node->menuId])->all() as $record) {
            $session = $this->_recordToModel($record);
            if (!in_array((int)$node->id, $session->addedNodeIds, true)) {
                continue;
            }

            $session->addedNodeIds = array_values(array_filter($session->addedNodeIds,
                static fn(int $id) => $id !== (int)$node->id));
            $this->saveSession($session);
        }
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
        $nodeId = (int)$nodeId;

        if (!in_array($nodeId, $session->addedNodeIds, true)) {
            $session->addedNodeIds[] = $nodeId;
        }

        $this->saveSession($session);
    }

    public function stageAddedNodes(BuildSessionModel $session, array $nodeIds): void
    {
        foreach ($nodeIds as $nodeId) {
            $nodeId = (int)$nodeId;

            if (!in_array($nodeId, $session->addedNodeIds, true)) {
                $session->addedNodeIds[] = $nodeId;
            }
        }

        if (!$this->saveSession($session)) {
            throw new UserException('Could not save pending node session.');
        }
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
     * Persist a full structure-move payload against Craft’s structure tables.
     *
     * Used by Save/publish (staging) and by live structure mode (`builderLiveStructure`).
     * `$skipElementIds` skips nodes that are about to be hard-deleted in the same publish.
     *
     */
    public function applyStructureMoves(MenuSettings $nav, int $siteId, array $structureMoves, array $skipElementIds = []): void
    {
        StructureLimits::batch($nav, $siteId, function() use ($nav, $siteId, $structureMoves, $skipElementIds) {
            $this->_applyStructureMoves($nav, $siteId, $structureMoves, $skipElementIds);
        }, $skipElementIds);
    }

    /**
     * Stage a node for deletion on publish. Newly-added session nodes are removed immediately.
     */
    public function stageDelete(BuildSessionModel $session, NodeElement $node, bool $withDescendants = false): void
    {
        $this->_withSession($session, fn() => $this->_stageDelete($session, $node, $withDescendants));
    }

    /**
     * Undo a staged deletion for a single node.
     */
    public function unstageDelete(BuildSessionModel $session, NodeElement $node): void
    {
        $this->_withSession($session, fn() => $this->_unstageDelete($session, $node));
    }

    public function publish(BuildSessionModel $session, bool $applyStructure = false, ?array $moves = null): array
    {
        return $this->_withSession($session, fn() => $this->_publish($session, $applyStructure, $moves));
    }

    public function discard(BuildSessionModel $session): void
    {
        $this->_withSession($session, fn() => $this->_discard($session));
    }

    public function deleteSession(BuildSessionModel $session): void
    {
        $this->_withSession($session, fn() => $this->_deleteSession($session));
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
        ?string $structureRevision = null,
    ): void {
        if ($structureMoves !== null) {
            // Persist the editor baseline, never the current server revision of an older draft.
            $session->structureRevision = $structureRevision;
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

    /** Hold the shared menu lock until both session bookkeeping and node writes complete. */
    private function _withSession(BuildSessionModel $session, callable $callback): mixed
    {
        return Craft::$app->getDb()->transaction(function() use ($session, $callback) {
            $this->_lockMenu((int)$session->menuId);
            $record = BuildSessionRecord::findBySql(
                'SELECT * FROM {{%navigation_build_sessions}} WHERE [[menuId]] = :menu AND [[siteId]] = :site AND [[userId]] = :user FOR UPDATE',
                [':menu' => $session->menuId, ':site' => $session->siteId, ':user' => $session->userId],
            )->one();

            if ($session->id
                ? (!$record || (int)$record->id !== $session->id || $session->getStorageRevision() !== $this->_storageRevision($record))
                : $record !== null) {
                throw new ConflictHttpException(Craft::t('navigation', 'This build session changed in another request. Reload the menu before trying again.'));
            }

            return $callback($record);
        });
    }

    private function _lockMenu(int $menuId): void
    {
        Craft::$app->getDb()->createCommand(
            'SELECT [[id]] FROM {{%navigation_menus}} WHERE [[id]] = :id FOR UPDATE', [':id' => $menuId],
        )->queryScalar();
    }

    /** Include the row identity so a discarded session cannot overwrite its replacement. */
    private function _storageRevision(BuildSessionRecord $record): string
    {
        return hash('sha256', Json::encode($record->getAttributes()));
    }

    private function _saveSession(BuildSessionModel $session, ?BuildSessionRecord $record): bool
    {
        // Reuse the checked current row, not an older transaction snapshot.
        $record ??= new BuildSessionRecord();

        $record->menuId = (int)$session->menuId;
        $record->siteId = (int)$session->siteId;
        $record->userId = (int)$session->userId;
        $record->structureMoves = $session->structureMoves !== [] ? Json::encode($session->structureMoves) : null;
        $record->structureRevision = $session->structureRevision;
        $record->addedNodeIds = $session->addedNodeIds !== [] ? Json::encode($session->addedNodeIds) : null;
        $record->stagedDeletes = $session->stagedDeletes !== [] ? Json::encode($session->stagedDeletes) : null;
        $record->menuDraftId = $session->menuDraftId;
        $record->menuContentDraft = $session->menuContentDraft !== [] ? Json::encode($session->menuContentDraft) : null;
        $record->nodeDraftMap = $session->nodeDraftMap !== [] ? Json::encode($session->nodeDraftMap) : null;

        if (!$record->save()) {
            throw new UserException(Craft::t('navigation', 'Couldn’t save build session.'));
        }

        $session->id = (int)$record->id;
        $session->uid = $record->uid;
        // A no-op save need not create a version visible to a repeatable-read snapshot.
        $record = BuildSessionRecord::findBySql(
            'SELECT * FROM {{%navigation_build_sessions}} WHERE [[id]] = :id FOR UPDATE', [':id' => $record->id],
        )->one();
        $session->setStorageRevision($this->_storageRevision($record));

        return true;
    }

    private function _stageDelete(BuildSessionModel $session, NodeElement $node, bool $withDescendants = false): void
    {
        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
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
                $this->_requireNodeOwnership($session, $nodeToStage);
            }

            foreach ($nodesToStage as $nodeToStage) {
                $nodeId = (int)$nodeToStage->id;

                // Session adds (and any orphaned pending-publish rows) are not live yet — remove
                // immediately instead of staging a delete that would fight the pending-add flag.
                $isSessionAdd = in_array($nodeId, $session->addedNodeIds, true)
                    || $nodeToStage->getIsPendingPublish();

                if ($isSessionAdd) {
                    $session->addedNodeIds = array_values(array_filter(
                        $session->addedNodeIds,
                        fn(int $id) => $id !== $nodeId,
                    ));

                    if (!$elementsService->deleteElement($nodeToStage, true)) {
                        throw new UserException(Craft::t('navigation', 'Couldn’t stage node for deletion.'));
                    }

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
                    'restoreEnabledState' => false,
                ];

                $nodeToStage->setPendingDeleteRestoreState($enabled, $enabledForSite, false);
                $nodeToStage->setPendingDelete(true);
                // Keep live enabled state — public readers must not see deletes until publish.
                // Builder overlays pending-delete styling from the flag / session.

                if (!$elementsService->saveElement($nodeToStage)) {
                    throw new UserException(Craft::t('navigation', 'Couldn’t stage node for deletion.'));
                }
            }

            $this->saveSession($session);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function _unstageDelete(BuildSessionModel $session, NodeElement $node): void
    {
        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            $this->_requireNodeOwnership($session, $node);
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
            // Current staged deletes leave statuses live. Preserve intervening source
            // updates; only older rows that disabled nodes need their saved status back.
            if ($stagedDelete['restoreEnabledState'] ?? true) {
                $node->enabled = (bool)$stagedDelete['enabled'];
                $node->setEnabledForSite((bool)$stagedDelete['enabledForSite']);
            }

            if (!Craft::$app->getElements()->saveElement($node)) {
                throw new UserException(Craft::t('navigation', 'Couldn’t restore node.'));
            }

            if ($session->getChangeCount(true) === 0) {
                $this->deleteSession($session);
            } else {
                $this->saveSession($session);
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function _publish(BuildSessionModel $session, bool $applyStructure = false, ?array $moves = null): array
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

        $elementsService = Craft::$app->getElements();
        $publishedCount = 0;
        $deletedCount = 0;
        // Skip structure moves for nodes we are about to hard-delete — otherwise a
        // post-delete move payload still referencing them throws "Invalid node ID".
        $stagedDeleteIds = [];

        foreach ($session->stagedDeletes as $stagedDelete) {
            $stagedDeleteIds[(int)$stagedDelete['nodeId']] = true;
        }

        // Publication is one tree edit: validate after child promotion and all deletes.
        StructureLimits::batch($nav, $siteId, function() use ($session, $elementsService, $siteId, $menuId, $nav, $applyStructure, $structureMoves, $stagedDeleteIds, &$publishedCount, &$deletedCount) {
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

                if (!$node->getIsPendingPublish()) {
                    continue;
                }

                $node->publishPendingAdd();

                if (!$elementsService->saveElement($node)) {
                    throw new UserException(Craft::t('navigation', 'Couldn’t save menu.'));
                }

                $publishedCount++;
            }

            // Structure first, then deletes: moves may still reference staged-delete
            // nodes as prev/parent anchors, and Craft promotes remaining children only
            // when the parent is removed.
            if ($applyStructure && $structureMoves !== []) {
                $this->applyStructureMoves($nav, $siteId, $structureMoves, $stagedDeleteIds);
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

            $this->deleteSession($session);
        });

        Navigation::$plugin->getNavigationCache()->invalidateMenuSite($nav->uid, $siteId);

        return [
            'publishedCount' => $publishedCount,
            'deletedCount' => $deletedCount,
        ];
    }

    private function _discard(BuildSessionModel $session): void
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

                if ($node && $node->getIsPendingPublish()) {
                    if (!$elementsService->deleteElement($node, true)) {
                        throw new UserException(Craft::t('navigation', 'Couldn’t discard build session.'));
                    }
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
                if ($stagedDelete['restoreEnabledState'] ?? true) {
                    $node->enabled = (bool)$stagedDelete['enabled'];
                    $node->setEnabledForSite((bool)$stagedDelete['enabledForSite']);
                }

                if (!$elementsService->saveElement($node)) {
                    throw new UserException(Craft::t('navigation', 'Couldn’t discard build session.'));
                }
            }

            $this->deleteSession($session);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        if ($nav) {
            Navigation::$plugin->getNavigationCache()->invalidateMenuSite($nav->uid, $siteId);
        }
    }

    private function _deleteSession(BuildSessionModel $session): void
    {
        if (!$session->id) {
            return;
        }

        BuildSessionRecord::deleteAll(['id' => $session->id]);
        $session->id = null;
    }

    private function _applyStructureMoves(MenuSettings $nav, int $siteId, array $structureMoves, array $skipElementIds = []): void
    {
        if ($structureMoves === []) {
            throw new BadRequestHttpException('Invalid moves payload.');
        }

        $menuId = (int)$nav->id;
        $structuresService = Craft::$app->getStructures();
        $elementsService = Craft::$app->getElements();
        $levels = [];

        foreach ($structureMoves as $move) {
            $elementId = (int)($move['elementId'] ?? 0);
            $parentId = isset($move['parentId']) && $move['parentId'] !== '' ? (int)$move['parentId'] : null;
            $prevId = isset($move['prevId']) && $move['prevId'] !== '' ? (int)$move['prevId'] : null;

            if (!$elementId) {
                throw new BadRequestHttpException('Invalid move payload.');
            }

            if (isset($skipElementIds[$elementId])) {
                continue;
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

    private function _recordToModel(BuildSessionRecord $record): BuildSessionModel
    {
        $session = new BuildSessionModel([
            'id' => (int)$record->id,
            'menuId' => (int)$record->menuId,
            'siteId' => (int)$record->siteId,
            'userId' => (int)$record->userId,
            'structureMoves' => Json::decodeIfJson($record->structureMoves) ?? [],
            'structureRevision' => $record->structureRevision,
            // JSON may rehydrate ids as strings — keep strict in_array() checks reliable.
            'addedNodeIds' => array_values(array_map('intval', Json::decodeIfJson($record->addedNodeIds) ?? [])),
            'stagedDeletes' => Json::decodeIfJson($record->stagedDeletes) ?? [],
            'menuDraftId' => $record->menuDraftId ? (int)$record->menuDraftId : null,
            'menuContentDraft' => Json::decodeIfJson($record->menuContentDraft) ?? [],
            'nodeDraftMap' => Json::decodeIfJson($record->nodeDraftMap) ?? [],
            'uid' => $record->uid,
        ]);
        $session->setStorageRevision($this->_storageRevision($record));

        return $session;
    }

    /** Canonical pending flags are shared across sites; never adopt another session's work. */
    private function _requireNodeOwnership(BuildSessionModel $session, NodeElement $node): void
    {
        if ((int)$node->menuId !== (int)$session->menuId || (int)$node->siteId !== (int)$session->siteId) {
            throw new BadRequestHttpException('Node does not belong to this menu/site session.');
        }
        foreach (BuildSessionRecord::find()->where(['menuId' => $session->menuId])->all() as $record) {
            if ((int)$record->id === (int)$session->id) {
                continue;
            }
            $other = $this->_recordToModel($record);
            if (($node->getIsPendingPublish() && in_array((int)$node->id, $other->addedNodeIds, true)) || $this->_findStagedDelete($other, (int)$node->id)) {
                throw new UserException(Craft::t('navigation', 'This node has pending changes in another build session.'));
            }
        }
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

        $admin = User::find()->admin()->status(null)->one();

        if ($admin) {
            return (int)$admin->id;
        }

        if ($required) {
            throw new UserException(Craft::t('navigation', 'Couldn’t resolve build session user.'));
        }

        return 0;
    }
}
