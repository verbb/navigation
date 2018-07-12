<?php
namespace verbb\navigation\controllers;

use Craft;
use craft\web\Controller;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\Node as NodeModel;

class NodesController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSaveNode()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();

        $node = $this->_setNodeFromPost();

        if (!Craft::$app->getElements()->saveElement($node, true, false)) {
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
            'parentOptions' => $parentOptions,
        ]);
    }

    public function actionDelete() {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();

        $nodeIds = $request->getRequiredBodyParam('nodeIds');
        $node = Navigation::$plugin->nodes->getNodeById($nodeIds[0]);

        // We need to go against `deleteElement()` which will kick up any child elements in the structure
        // to be attached to the parent - not what we want in this case, it'd be pandemonium.
        foreach ($nodeIds as $nodeId) {
            if (!Craft::$app->getElements()->deleteElementById($nodeId)) {
                return $this->asJson(['success' => false]);
            }
        }

        $nodes = $nodesService->getNodesForNav($node->nav->id, $node->siteId);
        $parentOptions = $nodesService->getParentOptions($nodes, $node->nav);

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

        $nodeId = $request->getRequiredBodyParam('nodeId');
        $siteId = $request->getRequiredBodyParam('siteId');
        $node = Navigation::$plugin->nodes->getNodeById($nodeId, $siteId);

        $html = Craft::$app->view->renderTemplate('navigation/navs/_editor', ['node' => $node]);

        return $this->asJson([
            'success' => true,
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

        $siteId = $request->getRequiredBodyParam('siteId');
        $navId = $request->getRequiredBodyParam('navId');

        $nav = $navsService->getNavById($navId, $siteId);

        $nodes = $nodesService->getNodesForNav($navId, $siteId);

        $parentOptions = $nodesService->getParentOptions($nodes, $nav);

        return $this->asJson([
            'success' => true,
            'parentOptions' => $parentOptions,
        ]);
    }


    // Private Methods
    // =========================================================================

    private function _setNodeFromPost(): NodeElement
    {
        $request = Craft::$app->getRequest();
        $nodeId = $request->getBodyParam('nodeId');
        $siteId = $request->getBodyParam('siteId');

        if ($nodeId) {
            $node = Navigation::$plugin->nodes->getNodeById($nodeId, $siteId);

            if (!$node) {
                throw new Exception(Craft::t('commerce', 'No node with the ID “{id}”', ['id' => $nodeId]));
            }
        } else {
            $node = new NodeElement();
        }

        $node->title = $request->getBodyParam('title', $node->title);
        $node->enabled = (bool)$request->getBodyParam('enabled', $node->enabled);

        $node->elementId = $request->getBodyParam('elementId', $node->elementId);
        $node->siteId = $request->getBodyParam('siteId', $node->siteId);
        $node->navId = $request->getBodyParam('navId', $node->navId);
        $node->url = $request->getBodyParam('url', $node->url);
        $node->type = $request->getBodyParam('type', $node->type);
        $node->classes = $request->getBodyParam('classes', $node->classes);
        $node->newWindow = (bool)$request->getBodyParam('newWindow', $node->newWindow);

        $node->newParentId = $request->getBodyParam('parentId', null);

        // Don't store the URL if its an element (not an issue, just save confusion)
        if ($node->type) {
            $node->url = null;
        }

        return $node;
    }

}