<?php
namespace verbb\navigation\controllers;

use Craft;
use craft\web\Controller;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\Node as NodeModel;

use Exception;

class NodesController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionAddNodes()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();

        $nodesPost = $request->getRequiredParam('nodes');

        $savedNodes = [];

        foreach ($nodesPost as $key => $nodePost) {
            $node = $this->_setNodeFromPost("nodes.{$key}.");
            $propagateNodes = (bool)$node->nav->propagateNodes;

            // Check for max nodes
            if ($node->nav->maxNodes) {
                $nodes = $nodesService->getNodesForNav($node->nav->id, $node->siteId);

                $totalNodes = count($nodes) + 1;

                if ($totalNodes > $node->nav->maxNodes) {
                    return $this->asJson([
                        'success' => false,
                        'message' => Craft::t('navigation', 'Exceeded maximum allowed nodes ({number}) for this nav.', ['number' => $node->nav->maxNodes]),
                    ]);
                }
            }

            if (!Craft::$app->getElements()->saveElement($node, true, $propagateNodes)) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $node->getErrors(),
                ]);
            }

            $savedNodes[] = $node;
        }

        // Fetch all the nodes, fresh. Just pick the first node to get stuff
        $firstNode = $savedNodes[0] ?? null;

        if (!$firstNode) {
            return $this->asJson([
                'success' => false,
                'errors' => Craft::t('navigation', 'Unable to complete saving nodes.'),
            ]);
        }

        $nodes = $nodesService->getNodesForNav($firstNode->nav->id, $firstNode->siteId);
        $parentOptions = $nodesService->getParentOptions($nodes, $firstNode->nav);

        return $this->asJson([
            'success' => true,
            'nodes' => $savedNodes,
            'level' => $firstNode->level,
            'parentOptions' => $parentOptions,
        ]);
    }

    public function actionSaveNode()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();

        $node = $this->_setNodeFromPost();
        $propagateNodes = (bool)$node->nav->propagateNodes;

        if (!Craft::$app->getElements()->saveElement($node, true, $propagateNodes)) {
            return $this->asJson([
                'success' => false,
                'errors' => $node->getErrors(),
            ]);
        }

        $nodes = $nodesService->getNodesForNav($node->nav->id, $node->siteId);
        $parentOptions = $nodesService->getParentOptions($nodes, $node->nav);

        return $this->asJson([
            'success' => true,
            'node' => $node,
            'level' => $node->level,
            'parentOptions' => $parentOptions,
        ]);
    }

    public function actionDelete()
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();

        $parentOptions = [];
        $siteId = $request->getParam('siteId');
        $nodeIds = $request->getRequiredParam('nodeIds');

        $node = Navigation::$plugin->nodes->getNodeById($nodeIds[0], $siteId);

        // We need to go against `deleteElement()` which will kick up any child elements in the structure
        // to be attached to the parent - not what we want in this case, it'd be pandemonium.
        foreach ($nodeIds as $nodeId) {
            if (!Craft::$app->getElements()->deleteElementById($nodeId)) {
                return $this->asJson(['success' => false]);
            }
        }

        if ($node) {
            $nav = $node->nav;

            $nodes = $nodesService->getNodesForNav($nav->id, $siteId);
            $parentOptions = $nodesService->getParentOptions($nodes, $nav);
        }

        return $this->asJson([
            'success' => true,
            'parentOptions' => $parentOptions,
        ]);
    }

    public function actionEditor()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $nodeId = $request->getRequiredParam('nodeId');
        $siteId = $request->getRequiredParam('siteId');
        $node = Navigation::$plugin->nodes->getNodeById($nodeId, $siteId);

        $view = Craft::$app->getView();

        $html = $view->renderTemplate('navigation/nodes/_modal', ['node' => $node]);

        return $this->asJson([
            'success' => true,
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml(),
            'html' => $html,
        ]);
    }

    public function actionChangeNodeType()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        $nodeId = $request->getRequiredParam('nodeId');
        $siteId = $request->getRequiredParam('siteId');
        $node = Navigation::$plugin->nodes->getNodeById($nodeId, $siteId);

        // Override and reset
        $node->type = $request->getParam('type');
        $node->elementId = null;
        $node->setElement(null);

        $view = Craft::$app->getView();

        $html = $view->renderTemplate('navigation/nodes/_modal', ['node' => $node]);

        return $this->asJson([
            'success' => true,
            'headHtml' => $view->getHeadHtml(),
            'footHtml' => $view->getBodyHtml(),
            'html' => $html,
        ]);
    }

    public function actionMove()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();
        $navsService = Navigation::$plugin->getNavs();

        $siteId = $request->getRequiredParam('siteId');
        $navId = $request->getRequiredParam('navId');

        $nav = $navsService->getNavById($navId, $siteId);
        Craft::$app->getSession()->authorize('editStructure:' . $nav->structureId);

        $nodes = $nodesService->getNodesForNav($navId, $siteId);

        $parentOptions = $nodesService->getParentOptions($nodes, $nav);

        return $this->asJson([
            'success' => true,
            'parentOptions' => $parentOptions,
        ]);
    }

    public function actionClear()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();

        $payload = $request->getRequiredParam('payload');
        $payload = explode(':', $payload);

        $navId = $payload[0] ?? null;
        $siteId = $payload[1] ?? null;

        if (!$navId || !$siteId) {
            $session->setError(Craft::t('navigation', 'Unable to resolve navigation to clear.'));

            return null;
        }

        $nodes = Navigation::$plugin->getNodes()->getNodesForNav($navId, $siteId);

        $errors = [];

        foreach ($nodes as $key => $node) {
            if (!Craft::$app->getElements()->deleteElementById($node->id, NodeElement::class, $siteId)) {
                $errors[] = $node->getErrors();
            }
        }

        if ($errors) {
            $session->setError(Craft::t('navigation', 'Unable to clear navigation.'));

            return null;
        }

        $session->setNotice(Craft::t('navigation', 'Navigation cleared.'));

        return $this->redirectToPostedUrl();
    }


    // Private Methods
    // =========================================================================

    private function _setNodeFromPost($prefix = ''): NodeElement
    {
        // Because adding multiple nodes and saving a single node use this same function, we have to jump
        // through some hoops to get the correct post params properties.
        $request = Craft::$app->getRequest();
        $nodeId = $request->getParam("{$prefix}nodeId");
        $siteId = $request->getParam("{$prefix}siteId");

        if ($nodeId) {
            $node = Navigation::$plugin->nodes->getNodeById($nodeId, $siteId);

            if (!$node) {
                throw new Exception(Craft::t('navigation', 'No node with the ID “{id}”', ['id' => $nodeId]));
            }
        } else {
            $node = new NodeElement();
        }

        $node->title = $request->getParam("{$prefix}title", $node->title);
        $node->enabled = (bool)$request->getParam("{$prefix}enabled", $node->enabled);
        $node->enabledForSite = (bool)$request->getParam("{$prefix}enabledForSite", $node->enabledForSite);

        $elementId = $request->getParam("{$prefix}elementId", $node->elementId);

        // Handle elementselect field
        if (is_array($elementId)) {
            $elementId = $elementId[0] ?? null;
        }

        $node->elementId = $elementId;
        $node->elementSiteId = $request->getParam("{$prefix}elementSiteId", $node->elementSiteId);
        $node->siteId = $request->getParam("{$prefix}siteId", $node->siteId);
        $node->navId = $request->getParam("{$prefix}navId", $node->navId);
        $node->url = $request->getParam("{$prefix}url", $node->url);
        $node->type = $request->getParam("{$prefix}type", $node->type);
        $node->classes = $request->getParam("{$prefix}classes", $node->classes);
        $node->urlSuffix = $request->getParam("{$prefix}urlSuffix", $node->urlSuffix);
        $node->customAttributes = $request->getParam("{$prefix}customAttributes", $node->customAttributes);
        $node->data = $request->getParam("{$prefix}data", $node->data);
        $node->newWindow = (bool)$request->getParam("{$prefix}newWindow", $node->newWindow);

        $node->newParentId = $request->getParam("{$prefix}parentId", null);

        // Handle custom URL - remove the elementId. Particularly if we're swapping
        if ($node->isManual()) {
            $node->elementId = null;
            $node->elementSiteId = null;
        }

        // Set field values.
        $node->setFieldValuesFromRequest('fields');

        return $node;
    }

}
