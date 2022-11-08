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

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();

        $nodesPost = $request->getRequiredParam('nodes');

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

        $request = Craft::$app->getRequest();
        $nodesService = Navigation::$plugin->getNodes();
        $navId = $request->getRequiredParam('navId');
        $siteId = $request->getParam('siteId');

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
        $request = Craft::$app->getRequest();

        $node = new Node();
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
        $node->customAttributes = Json::decodeIfJson($request->getParam("{$prefix}customAttributes")) ?? $node->customAttributes;
        $node->data = Json::decodeIfJson($request->getParam("{$prefix}data")) ?? $node->data;
        $node->newWindow = (bool)$request->getParam("{$prefix}newWindow", $node->newWindow);

        $node->parentId = $request->getParam("{$prefix}parentId");

        // Set field values.
        $node->setFieldValuesFromRequest('fields');

        return $node;
    }

}
