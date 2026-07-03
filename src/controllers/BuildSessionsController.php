<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

use Craft;
use craft\web\Controller;

use Throwable;

use yii\web\BadRequestHttpException;
use yii\web\Response;

class BuildSessionsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionPublish(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $buildSessions = Navigation::$plugin->getBuildSessions();

        if (!$buildSessions->isStagingEnabled()) {
            throw new BadRequestHttpException('Builder staging is disabled.');
        }

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');
        $applyStructure = (bool)$this->request->getBodyParam('applyStructure', false);
        $moves = $this->request->getBodyParam('moves', []);

        if (!is_array($moves)) {
            throw new BadRequestHttpException('Invalid moves payload.');
        }

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        $session = $buildSessions->getOrCreate($menuId, $siteId);
        $nodeChangeCount = count($session->addedNodeIds) + count($session->stagedDeletes);
        $hasStructurePayload = $applyStructure && $moves !== [];

        if ($hasStructurePayload) {
            $buildSessions->setStructureMoves($session, $moves);
            $session = $buildSessions->getSession($menuId, $siteId);
        }

        try {
            if ($nodeChangeCount > 0 || $hasStructurePayload) {
                $result = $buildSessions->publish($session, $applyStructure, $hasStructurePayload ? $moves : null);
            } else {
                $result = ['publishedCount' => 0, 'deletedCount' => 0];
                $buildSessions->deleteSession($session);
            }
        } catch (BadRequestHttpException $e) {
            return $this->asFailure($e->getMessage());
        } catch (Throwable $e) {
            Craft::error('Failed to save menu build session: ' . $e->getMessage(), __METHOD__);

            return $this->asFailure(Craft::t('navigation', 'Couldn’t save menu.'));
        }

        return $this->asSuccess(Craft::t('navigation', 'Menu saved.'), [
            'changeCount' => 0,
            'publishedCount' => $result['publishedCount'],
            'deletedCount' => $result['deletedCount'],
        ]);
    }

    public function actionDiscard(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $buildSessions = Navigation::$plugin->getBuildSessions();

        if (!$buildSessions->isStagingEnabled()) {
            throw new BadRequestHttpException('Builder staging is disabled.');
        }

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        $session = $buildSessions->getSession($menuId, $siteId);

        if (!$session) {
            return $this->asSuccess(Craft::t('navigation', 'Build session discarded.'), [
                'changeCount' => 0,
            ]);
        }

        try {
            $buildSessions->discard($session);
        } catch (Throwable $e) {
            Craft::error('Failed to discard menu build session: ' . $e->getMessage(), __METHOD__);

            return $this->asFailure(Craft::t('navigation', 'Couldn’t discard build session.'));
        }

        return $this->asSuccess(Craft::t('navigation', 'Build session discarded.'), [
            'changeCount' => 0,
        ]);
    }

    public function actionUnstageDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $buildSessions = Navigation::$plugin->getBuildSessions();

        if (!$buildSessions->isStagingEnabled()) {
            throw new BadRequestHttpException('Builder staging is disabled.');
        }

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');
        $nodeId = (int)$this->request->getRequiredBodyParam('nodeId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        $node = Node::find()
            ->id($nodeId)
            ->siteId($siteId)
            ->menuId($menuId)
            ->status(null)
            ->one();

        if (!$node || !$node->getIsPendingDelete()) {
            return $this->asFailure(Craft::t('navigation', 'Couldn’t restore node.'));
        }

        $session = $buildSessions->getOrCreate($menuId, $siteId);

        try {
            $buildSessions->unstageDelete($session, $node);
        } catch (Throwable $e) {
            Craft::error('Failed to restore staged node deletion: ' . $e->getMessage(), __METHOD__);

            return $this->asFailure(Craft::t('navigation', 'Couldn’t restore node.'));
        }

        $session = $buildSessions->getSession($menuId, $siteId);

        return $this->asSuccess(Craft::t('navigation', 'Node restored to menu.'), [
            'changeCount' => $session ? $session->getChangeCount(true) : 0,
            'nodes' => Navigation::$plugin->getBuilderState()->nodesToArray(
                Node::find()->menuId($menuId)->siteId($siteId)->status(null)->orderBy(['structureelements.lft' => SORT_ASC])->all(),
            ),
        ]);
    }
}
