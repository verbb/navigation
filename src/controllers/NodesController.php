<?php
namespace verbb\navigation\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;

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

        $nodesPost = $this->request->getRequiredParam('nodes');

        foreach ($nodesPost as $key => $nodePost) {
            $node = $this->_setNodeFromPost("nodes.{$key}.");

            // Add this new node to the nav, to assist with validation
            $nodesService->setTempNodes([$node]);

            if (!Craft::$app->getElements()->saveElement($node, true)) {
                return $this->asModelFailure($node, Craft::t('navigation', 'Couldn’t add node.'), 'node');
            }
        }

        return $this->asSuccess(Craft::t('navigation', 'Node{plural} added.', ['plural' => count($nodesPost) > 1 ? 's' : '']));
    }

    public function actionGetParentOptions(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $nodesService = Navigation::$plugin->getNodes();
        $navId = $this->request->getRequiredParam('navId');
        $siteId = $this->request->getParam('siteId');

        $nodes = $nodesService->getNodesForNav($navId, $siteId);

        $options = [];

        if ($nodes) {
            $options = $nodesService->getParentOptions($nodes, $nodes[0]->nav);
        }

        return $this->asJson(['options' => $options]);
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
        $node->navId = $this->request->getParam("{$prefix}navId", $node->navId);
        $node->url = $this->request->getParam("{$prefix}url", $node->url);
        $node->type = $this->request->getParam("{$prefix}type", $node->type);
        $node->classes = $this->request->getParam("{$prefix}classes", $node->classes);
        $node->urlSuffix = $this->request->getParam("{$prefix}urlSuffix", $node->urlSuffix);
        $node->customAttributes = Json::decodeIfJson($this->request->getParam("{$prefix}customAttributes")) ?? $node->customAttributes;
        $node->data = Json::decodeIfJson($this->request->getParam("{$prefix}data")) ?? $node->data;
        $node->newWindow = (bool)$this->request->getParam("{$prefix}newWindow", $node->newWindow);

        $node->parentId = $this->request->getParam("{$prefix}parentId");

        // Set field values.
        $node->setFieldValuesFromRequest('fields');

        return $node;
    }

}
