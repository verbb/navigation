<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\Nav as NavModel;
use verbb\navigation\models\Settings;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;

use yii\web\NotFoundHttpException;
use yii\web\Response;

class NavsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        $navigations = Navigation::$plugin->getNavs()->getAllEditableNavs();

        $siteHandles = [];

        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
            $siteHandles[$site->id] = $site->handle;
        }

        $editable = Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        return $this->renderTemplate('navigation/navs/index', [
            'navigations' => $navigations,
            'siteHandles' => $siteHandles,
            'editable' => $editable,
        ]);
    }

    public function actionEditNav(int $navId = null, NavModel $nav = null): Response
    {
        if ($nav === null) {
            if ($navId !== null) {
                $nav = Navigation::$plugin->getNavs()->getNavById($navId);

                if (!$nav) {
                    throw new NotFoundHttpException('Navigation not found');
                }
            } else {
                $nav = new NavModel();
            }
        }

        if ($nav->id) {
            $this->requirePermission('navigation-editNav:' . $nav->uid);
        } else {
            $this->requirePermission('navigation-createNavs');
        }

        return $this->renderTemplate('navigation/navs/_edit', [
            'navId' => $navId,
            'nav' => $nav,
        ]);
    }

    public function actionBuildNav(int $navId = null, string $siteHandle = null): Response
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();
        $defaultSite = false;

        if ($navId !== null) {
            $nav = Navigation::$plugin->getNavs()->getNavById($navId);

            if (!$nav) {
                throw new NotFoundHttpException('Navigation not found');
            }
        } else {
            $nav = new NavModel();
        }

        // If not requesting a specific site, use the primary one
        if ($siteHandle === null) {
            $defaultSite = true;
            $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;

            // If they don't have access to the default site, pick the first enabled one
            $site = ArrayHelper::firstWhere($nav->getEditableSites(), 'handle', $siteHandle);

            if (!$site) {
                $siteHandle = $nav->getEditableSites()[0]->handle ?? '';
            }
        }

        // Ensure this is an enabled site, otherwise throw an error
        $site = ArrayHelper::firstWhere($nav->getEditableSites(), 'handle', $siteHandle);

        if (!$site) {
            throw new NotFoundHttpException('Navigation not enabled for site: ' . $siteHandle);
        }

        $this->requirePermission('navigation-manageNav:' . $nav->uid);

        $nodes = Navigation::$plugin->getNodes()->getNodesForNav($nav->id, $site->id);

        $parentOptions = Navigation::$plugin->getNodes()->getParentOptions($nodes, $nav);

        Craft::$app->getSession()->authorize('editStructure:' . $nav->structureId);

        $editable = Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        return $this->renderTemplate('navigation/navs/_build', [
            'navId' => $navId,
            'nav' => $nav,
            'nodes' => $nodes,
            'site' => $site,
            'defaultSite' => $defaultSite,
            'parentOptions' => $parentOptions,
            'settings' => $settings,
            'editable' => $editable,
        ]);
    }

    public function actionSaveNav(): ?Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();

        $navId = $request->getBodyParam('navId');

        if ($navId) {
            $nav = Navigation::$plugin->getNavs()->getNavById($navId);
        } else {
            $nav = new NavModel();
            $nav->id = $navId;
        }

        $nav->name = $request->getBodyParam('name');
        $nav->handle = $request->getBodyParam('handle');
        $nav->instructions = $request->getBodyParam('instructions');
        $nav->maxLevels = !empty($request->getBodyParam('maxLevels')) ? (int)$request->getBodyParam('maxLevels') : null;
        $nav->propagateNodes = (bool)$request->getBodyParam('propagateNodes');
        $nav->maxNodes = !empty($request->getBodyParam('maxNodes')) ? (int)$request->getBodyParam('maxNodes') : null;
        $nav->permissions = $request->getBodyParam('permissions');

        $allSiteSettings = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $postedSettings = $request->getBodyParam('siteSettings.' . $site->uid);

            // Skip disabled sites if this is a multi-site install
            if (Craft::$app->getIsMultiSite() && empty($postedSettings['enabled'])) {
                continue;
            }

            $allSiteSettings[$site->uid] = $postedSettings;
        }

        $nav->siteSettings = $allSiteSettings;

        // Set the nav field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = NodeElement::class;
        $nav->setFieldLayout($fieldLayout);

        $success = Navigation::$plugin->getNavs()->saveNav($nav);

        if (!$success) {
            $session->setError(Craft::t('navigation', 'Unable to save navigation.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'nav' => $nav,
            ]);

            return null;
        }

        $session->setNotice(Craft::t('navigation', 'Navigation saved.'));

        return $this->redirectToPostedUrl($nav);
    }

    public function actionReorderNav(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $navIds = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));
        Navigation::$plugin->getNavs()->reorderNavs($navIds);

        return $this->asJson(['success' => true]);
    }

    public function actionDeleteNav(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $navId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $nav = Navigation::$plugin->getNavs()->getNavById($navId);

        $this->requirePermission('navigation-deleteNav:' . $nav->uid);

        Navigation::$plugin->getNavs()->deleteNavById($navId);

        return $this->asJson(['success' => true]);
    }

}
