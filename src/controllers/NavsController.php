<?php
namespace verbb\navigation\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\Nav as NavModel;

use yii\web\Response;

class NavsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $navigations = Navigation::$plugin->navs->getAllNavs();

        $siteHandles = [];

        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
            $siteHandles[$site->id] = $site->handle;
        }

        return $this->renderTemplate('navigation/navs/index', [
            'navigations' => $navigations,
            'siteHandles' => $siteHandles,
        ]);
    }

    public function actionEditNav(int $navId = null, NavModel $nav = null)
    {
        if ($nav === null) {
            if ($navId !== null) {
                $nav = Navigation::$plugin->navs->getNavById($navId);

                if (!$nav) {
                    throw new NotFoundHttpException('Navigation not found');
                }
            } else {
                $nav = new NavModel();
            }
        }

        return $this->renderTemplate('navigation/navs/_edit', [
            'navId' => $navId,
            'nav' => $nav,
        ]);
    }

    public function actionBuildNav(int $navId = null, string $siteHandle = null)
    {
        if ($siteHandle === null) {
            $editableSites = Craft::$app->getSites()->getEditableSites();

            $siteHandle = $editableSites[0]->handle ?? Craft::$app->getSites()->getCurrentSite()->handle;
        }

        $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$site) {
            throw new NotFoundHttpException('Invalid site handle: ' . $siteHandle);
        }

        if ($navId !== null) {
            $nav = Navigation::$plugin->navs->getNavById($navId);

            if (!$nav) {
                throw new NotFoundHttpException('Navigation not found');
            }
        } else {
            $nav = new NavModel();
        }

        $nodes = Navigation::$plugin->nodes->getNodesForNav($nav->id, $site->id);

        $parentOptions = Navigation::$plugin->nodes->getParentOptions($nodes, $nav);

        Craft::$app->getSession()->authorize('editStructure:' . $nav->structureId);

        return $this->renderTemplate('navigation/navs/_build', [
            'navId' => $navId,
            'nav' => $nav,
            'nodes' => $nodes,
            'site' => $site,
            'parentOptions' => $parentOptions,
        ]);
    }

    public function actionSaveNav()
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();

        $nav = new NavModel();
        $nav->id = $request->getBodyParam('navId');
        $nav->name = $request->getBodyParam('name');
        $nav->handle = $request->getBodyParam('handle');
        $nav->maxLevels = $request->getBodyParam('maxLevels');
        $nav->propagateNodes = $request->getBodyParam('propagateNodes');

        $success = Navigation::$plugin->navs->saveNav($nav);

        if (!$success) {
            Craft::$app->getUrlManager()->setRouteParams([
                'nav' => $nav
            ]);

            return null;
        }

        $session->setNotice(Craft::t('navigation', 'Navigation saved.'));

        return $this->redirectToPostedUrl($nav);
    }

    public function actionReorderNav()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $navIds = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));
        Navigation::$plugin->navs->reorderNavs($navIds);

        return $this->asJson(['success' => true]);
    }

    public function actionDeleteNav()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $navId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        Navigation::$plugin->navs->deleteNavById($navId);

        return $this->asJson(['success' => true]);
    }

}
