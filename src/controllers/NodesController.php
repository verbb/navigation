<?php
namespace verbb\navigation\controllers;

use verbb\navigation\elements\Node;
use verbb\navigation\helpers\MenuAuth;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;

use Throwable;

use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class NodesController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionAddNodes(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $nodesService = Navigation::$plugin->getNodes();
        $buildSessions = Navigation::$plugin->getBuildSessions();
        $deferPublish = $buildSessions->isStagingEnabled();

        $nodesPost = $this->request->getRequiredParam('nodes');

        if (!is_array($nodesPost) || $nodesPost === []) {
            throw new BadRequestHttpException('No nodes to add.');
        }

        // Authorize the whole batch before any write so a mixed-menu payload cannot
        // create nodes on an unauthorized menu after a permitted first item saves.
        $nodes = [];
        $menuId = null;
        $siteId = null;

        foreach ($nodesPost as $key => $nodePost) {
            $node = $this->_setNodeFromPost("nodes.{$key}.");
            $nodeMenuId = (int)$node->menuId;
            $nodeSiteId = (int)$node->siteId;

            if (!$nodeMenuId) {
                throw new BadRequestHttpException('Invalid menu ID.');
            }

            if ($menuId === null) {
                MenuAuth::requireManageMenuSite(
                    $this,
                    Navigation::$plugin->getMenus()->getMenuById($nodeMenuId),
                    $nodeSiteId ?: null,
                );
                $menuId = $nodeMenuId;
                $siteId = $nodeSiteId ?: null;
            } elseif ($nodeMenuId !== $menuId) {
                throw new ForbiddenHttpException('All nodes in a batch must belong to the same menu.');
            } elseif ($siteId !== null && $nodeSiteId && $nodeSiteId !== $siteId) {
                throw new ForbiddenHttpException('All nodes in a batch must belong to the same site.');
            }

            $nodes[] = $node;
        }

        $addedNodeIds = [];

        foreach ($nodes as $node) {
            // Add this new node to the nav, to assist with validation
            $nodesService->setTempNodes([$node]);

            if ($deferPublish) {
                // Match duplicate staging: disabled until Save publishes. afterSave clears
                // `_pendingPublish` whenever enabled+enabledForSite, so new adds must start disabled.
                $node->setPendingPublish(true);
                $node->enabled = false;
                $node->setEnabledForSite(false);
            }

            if (!Craft::$app->getElements()->saveElement($node, true)) {
                return $this->asModelFailure($node, Craft::t('navigation', 'Couldn’t add node.'), 'node');
            }

            $siteId ??= (int)$node->siteId;
            $addedNodeIds[] = (int)$node->id;
        }

        if ($deferPublish && $menuId && $siteId) {
            $session = $buildSessions->getOrCreate($menuId, $siteId);

            foreach ($addedNodeIds as $nodeId) {
                if (!in_array($nodeId, $session->addedNodeIds, true)) {
                    $session->addedNodeIds[] = $nodeId;
                }
            }

            $buildSessions->saveSession($session);
        }

        $message = $deferPublish
            ? Craft::t('navigation', 'Node{plural} added. Save menu to apply.', ['plural' => count($nodesPost) > 1 ? 's' : ''])
            : Craft::t('navigation', 'Node{plural} added.', ['plural' => count($nodesPost) > 1 ? 's' : '']);

        return $this->asSuccess($message, [
            'changeCount' => ($deferPublish && $menuId && $siteId)
                ? $buildSessions->getChangeCount($menuId, $siteId)
                : 0,
        ]);
    }

    public function actionGetParentOptions(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $nodesService = Navigation::$plugin->getNodes();
        $buildSessions = Navigation::$plugin->getBuildSessions();
        $menuId = (int)$this->request->getRequiredParam('menuId');
        $siteId = $this->request->getParam('siteId');
        $siteId = $siteId ? (int)$siteId : null;

        MenuAuth::requireManageMenuSite(
            $this,
            Navigation::$plugin->getMenus()->getMenuById($menuId),
            $siteId,
        );

        $nodes = $nodesService->getNodesForNav($menuId, $siteId);

        $options = [];

        if ($nodes) {
            $options = $nodesService->getParentOptions($nodes, Navigation::$plugin->getMenus()->getMenuById($nodes[0]->menuId));
        }

        return $this->asJson([
            'options' => $options,
            'changeCount' => $buildSessions->isStagingEnabled()
                ? $buildSessions->getChangeCount($menuId, $siteId)
                : 0,
        ]);
    }

    public function actionCopyToSite(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $targetSiteId = (int)$this->request->getRequiredBodyParam('siteId');
        $deep = (bool)$this->request->getBodyParam('deep', false);
        $remapLinkedElements = (bool)$this->request->getBodyParam('remapLinkedElements', false);
        $nodeIds = $this->_normalizeCopyNodeIds();

        $firstNode = Node::find()->id($nodeIds[0])->status(null)->site('*')->unique()->one();

        if (!$firstNode) {
            return $this->asFailure(Craft::t('navigation', 'Node not found.'));
        }

        $menuId = (int)($this->request->getBodyParam('menuId') ?? $firstNode->menuId);
        $sourceSiteId = (int)($this->request->getBodyParam('sourceSiteId') ?? $firstNode->siteId);

        $nav = MenuAuth::requireManageMenu($this, Navigation::$plugin->getMenus()->getMenuById($menuId));

        if ($nav->propagationMethod !== MenuSettings::PROPAGATION_METHOD_NONE) {
            return $this->asFailure(Craft::t('navigation', 'Nodes in this menu are propagated automatically. Switch sites to edit them instead of copying.'));
        }

        if ($deep && (int)$nav->maxLevels === 1) {
            throw new BadRequestHttpException('This menu does not support nested nodes.');
        }

        $result = Navigation::$plugin->getNodes()->copyNodesToSite(
            $menuId,
            $sourceSiteId,
            $nodeIds,
            $targetSiteId,
            $deep,
            $remapLinkedElements,
        );

        if ($result['successCount'] === 0) {
            return $this->asFailure(Craft::t('navigation', 'Couldn’t copy node to site.'));
        }

        $message = $result['successCount'] === 1
            ? Craft::t('navigation', 'Node copied to site.')
            : Craft::t('navigation', '{count} nodes copied to site.', ['count' => $result['successCount']]);

        if ($result['skippedLinkedElementRemapCount'] > 0) {
            $message .= ' ' . Craft::t(
                'navigation',
                '{count, plural, =1{1 element-linked node kept its original link because the element isn’t available on the target site.} other{# element-linked nodes kept their original links because the elements aren’t available on the target site.}}',
                ['count' => $result['skippedLinkedElementRemapCount']],
            );
        }

        return $this->asSuccess($message, [
            'nodeId' => $result['copiedNodeIds'][0] ?? null,
            'copiedNodeIds' => $result['copiedNodeIds'],
            'remappedLinkedElementCount' => $result['remappedLinkedElementCount'],
            'skippedLinkedElementRemapCount' => $result['skippedLinkedElementRemapCount'],
        ]);
    }

    public function actionSaveStructure(): Response
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

        MenuAuth::requireManageMenuSite(
            $this,
            Navigation::$plugin->getMenus()->getMenuById($menuId),
            $siteId,
        );

        $session = $buildSessions->getOrCreate($menuId, $siteId);
        $changeCount = $session->getChangeCount(false);

        if ($applyStructure && $moves === []) {
            throw new BadRequestHttpException('Invalid moves payload.');
        }

        if ($applyStructure && $moves !== []) {
            $buildSessions->setStructureMoves($session, $moves);
        }

        try {
            $result = $buildSessions->publish($session, $applyStructure, $applyStructure ? $moves : null);
        } catch (BadRequestHttpException $e) {
            return $this->asFailure($e->getMessage());
        } catch (Throwable $e) {
            Craft::error('Failed to save menu: ' . $e->getMessage(), __METHOD__);

            return $this->asFailure(Craft::t('navigation', 'Couldn’t save menu.'));
        }

        return $this->asSuccess(Craft::t('navigation', 'Menu saved.'), [
            'changeCount' => 0,
            'publishedCount' => $result['publishedCount'],
            'deletedCount' => $result['deletedCount'],
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _setNodeFromPost($prefix = ''): Node
    {
        // Because adding multiple nodes and saving a single node use this same function, we have to jump
        // through some hoops to get the correct post params properties.
        $node = new Node();
        $node->title = $this->request->getParam("{$prefix}title", $node->title);
        $node->enabled = (bool)$this->request->getParam("{$prefix}enabled", $node->enabled);
        $node->enabledForSite = (bool)$this->request->getParam("{$prefix}enabledForSite", $node->enabledForSite);

        $elementId = $this->request->getParam("{$prefix}elementId", $node->elementId);

        // Handle elementselect field
        if (is_array($elementId)) {
            $elementId = $elementId[0] ?? null;
        }

        $node->elementId = $elementId;
        $node->elementSiteId = $this->request->getParam("{$prefix}elementSiteId", $node->elementSiteId);
        $node->siteId = $this->request->getParam("{$prefix}siteId", $node->siteId);
        $node->menuId = $this->request->getParam("{$prefix}menuId", $node->menuId);
        $node->url = $this->request->getParam("{$prefix}url", $node->url);
        $node->type = $this->request->getParam("{$prefix}type", $node->type);
        $node->classes = $this->request->getParam("{$prefix}classes", $node->classes);
        $node->urlSuffix = $this->request->getParam("{$prefix}urlSuffix", $node->urlSuffix);
        $node->customAttributes = Json::decodeIfJson($this->request->getParam("{$prefix}customAttributes")) ?? $node->customAttributes;
        $node->data = Json::decodeIfJson($this->request->getParam("{$prefix}data")) ?? $node->data;
        $node->newWindow = (bool)$this->request->getParam("{$prefix}newWindow", $node->newWindow);

        $enabledForPropagatedSites = $this->request->getParam("{$prefix}enabledForPropagatedSites");

        if ($enabledForPropagatedSites !== null) {
            $node->setEnabledForPropagatedSitesPreference((bool)$enabledForPropagatedSites);
        }

        $node->parentId = $this->request->getParam("{$prefix}parentId");

        // Set field values.
        $node->setFieldValuesFromRequest('fields');

        // If no title, and an element-based node, get the element's title. Can't be done client side due to UI Label settings
        if (!$node->title && $element = $node->getElement()) {
            $node->title = $element->title;
        }

        return $node;
    }

    private function _normalizeCopyNodeIds(): array
    {
        $nodeIds = $this->request->getBodyParam('nodeIds');

        if ($nodeIds === null) {
            return [(int)$this->request->getRequiredBodyParam('nodeId')];
        }

        if (!is_array($nodeIds)) {
            throw new BadRequestHttpException('Invalid node IDs payload.');
        }

        $nodeIds = array_values(array_unique(array_map('intval', $nodeIds)));

        if ($nodeIds === []) {
            throw new BadRequestHttpException('No nodes selected.');
        }

        return $nodeIds;
    }

}
