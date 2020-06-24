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
        $propagateNodes = (bool)$node->nav->propagateNodes;

        // Check for max nodes - if this is a new node
        if ($node->nav->maxNodes && !$node->id) {
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
        $siteId = $request->getBodyParam('siteId');
        $nodeIds = $request->getRequiredBodyParam('nodeIds');

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

        $nodeId = $request->getRequiredBodyParam('nodeId');
        $siteId = $request->getRequiredBodyParam('siteId');
        $node = Navigation::$plugin->nodes->getNodeById($nodeId, $siteId);

        $view = Craft::$app->getView();

        $html = $view->renderTemplate('navigation/navs/_editor', ['node' => $node]);

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

        $nodeId = $request->getRequiredBodyParam('nodeId');
        $siteId = $request->getRequiredBodyParam('siteId');
        $node = Navigation::$plugin->nodes->getNodeById($nodeId, $siteId);

        // Override and reset
        $node->type = $request->getParam('type');
        $node->elementId = null;
        $node->setElement(null);

        $view = Craft::$app->getView();

        $html = $view->renderTemplate('navigation/navs/_editor', ['node' => $node]);

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
                throw new Exception(Craft::t('navigation', 'No node with the ID “{id}”', ['id' => $nodeId]));
            }
        } else {
            $node = new NodeElement();
        }

        $node->title = $request->getBodyParam('title', $node->title);
        $node->enabled = (bool)$request->getBodyParam('enabled', $node->enabled);
        $node->enabledForSite = (bool)$request->getBodyParam('enabledForSite', $node->enabledForSite);

        $elementId = $request->getBodyParam('elementId', $node->elementId);

        // Handle elementselect field
        if (is_array($elementId)) {
            $elementId = $elementId[0] ?? null;
        }

        $node->elementId = $elementId;
        $node->elementSiteId = $request->getBodyParam('elementSiteId', $node->elementSiteId);
        $node->siteId = $request->getBodyParam('siteId', $node->siteId);
        $node->navId = $request->getBodyParam('navId', $node->navId);
        $node->url = $request->getBodyParam('url', $node->url);
        $node->type = $request->getBodyParam('type', $node->type);
        $node->classes = $request->getBodyParam('classes', $node->classes);
        $node->urlSuffix = $request->getBodyParam('urlSuffix', $node->urlSuffix);
        $node->customAttributes = $request->getBodyParam('customAttributes', $node->customAttributes);
        $node->data = $request->getBodyParam('data', $node->data);
        $node->newWindow = (bool)$request->getBodyParam('newWindow', $node->newWindow);

        $node->newParentId = $request->getBodyParam('parentId', null);

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
