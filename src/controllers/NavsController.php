<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\fieldlayoutelements\ClassesField;
use verbb\navigation\fieldlayoutelements\CustomAttributesField;
use verbb\navigation\fieldlayoutelements\NewWindowField;
use verbb\navigation\fieldlayoutelements\UrlSuffixField;
use verbb\navigation\models\Nav;
use verbb\navigation\models\Nav_SiteSettings;
use verbb\navigation\models\Settings;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\models\FieldLayoutTab;
use craft\web\Controller;

use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

use Throwable;

class NavsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();
        
        // Get the current site from the global query param
        $siteHandle = Craft::$app->getRequest()->getParam('site', Craft::$app->getSites()->getPrimarySite()->handle);
        $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        $navigations = Navigation::$plugin->getNavs()->getEditableNavsForSite($site);

        $editable = $settings->bypassProjectConfig || Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        return $this->renderTemplate('navigation/navs/index', [
            'navigations' => $navigations,
            'editable' => $editable,
        ]);
    }

    public function actionEditNav(int $navId = null, Nav $nav = null): Response
    {
        if ($nav === null) {
            if ($navId !== null) {
                $nav = Navigation::$plugin->getNavs()->getNavById($navId);

                if (!$nav) {
                    throw new NotFoundHttpException('Navigation not found');
                }
            } else {
                $nav = new Nav();

                // Populate the field layout
                $tab1 = new FieldLayoutTab(['name' => 'Node']);
                $tab1->setLayout($nav->fieldLayout);

                $tab1->setElements([
                    Craft::createObject([
                        'class' => NewWindowField::class,
                    ]),
                ]);

                $tab2 = new FieldLayoutTab(['name' => 'Advanced']);
                $tab2->setLayout($nav->fieldLayout);
                
                $tab2->setElements([
                    Craft::createObject([
                        'class' => UrlSuffixField::class,
                    ]),
                    Craft::createObject([
                        'class' => ClassesField::class,
                    ]),
                    Craft::createObject([
                        'class' => CustomAttributesField::class,
                    ]),
                ]);

                $nav->fieldLayout->setTabs([$tab1, $tab2]);
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

    public function actionBuildNav(int $navId = null): Response
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
            $nav = new Nav();
        }

        $siteHandle = $this->request->getParam('site');

        // If not requesting a specific site, use the primary one
        if (!$siteHandle) {
            $defaultSite = true;
            $siteHandle = Craft::$app->getSites()->getPrimarySite()->handle;

            // If they don't have access to the default site, pick the first enabled one
            $site = ArrayHelper::firstWhere($nav->getSites(), 'handle', $siteHandle);

            if (!$site) {
                $siteHandle = $nav->getSites()[0]->handle ?? '';
            }
        }

        // Ensure this is an enabled site, otherwise throw an error
        $site = ArrayHelper::firstWhere($nav->getSites(), 'handle', $siteHandle);

        if (!$site) {
            throw new NotFoundHttpException('Navigation not enabled for site: ' . $siteHandle);
        }

        $this->requirePermission('navigation-manageNav:' . $nav->uid);

        $nodes = Navigation::$plugin->getNodes()->getNodesForNav($nav->id, $site->id);

        $parentOptions = Navigation::$plugin->getNodes()->getParentOptions($nodes, $nav);

        Craft::$app->getSession()->authorize('editStructure:' . $nav->structureId);

        $editable = $settings->bypassProjectConfig || Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

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

        $navId = $this->request->getBodyParam('navId');

        if ($navId) {
            $nav = Navigation::$plugin->getNavs()->getNavById($navId);

            if (!$nav) {
                throw new BadRequestHttpException("Invalid navigation ID: $navId");
            }
        } else {
            $nav = new Nav();
        }

        $nav->name = $this->request->getBodyParam('name');
        $nav->handle = $this->request->getBodyParam('handle');
        $nav->instructions = $this->request->getBodyParam('instructions');
        $nav->propagationMethod = $this->request->getBodyParam('propagationMethod', Nav::PROPAGATION_METHOD_ALL);
        $nav->maxLevels = (int)$this->request->getBodyParam('maxLevels') ?: null;
        $nav->maxNodes = (int)$this->request->getBodyParam('maxNodes') ?: null;
        $nav->maxNodesSettings = $this->request->getBodyParam('maxNodesSettings') ?: [];
        $nav->permissions = $this->request->getBodyParam('permissions');
        $nav->defaultPlacement = $this->request->getBodyParam('defaultPlacement') ?? $nav->defaultPlacement;

        $allSiteSettings = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $postedSettings = $this->request->getBodyParam('sites.' . $site->handle);

            $siteSettings = new Nav_SiteSettings();
            $siteSettings->siteId = $site->id;

            // Enabled by default, particularly for non-multi-sites
            $siteSettings->enabled = $postedSettings['enabled'] ?? true;

            $allSiteSettings[$site->id] = $siteSettings;
        }

        $nav->setSiteSettings($allSiteSettings);

        // Set the nav field layout
        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = NodeElement::class;
        $nav->setFieldLayout($fieldLayout);

        if (!Navigation::$plugin->getNavs()->saveNav($nav)) {
            $this->setFailFlash(Craft::t('navigation', 'Unable to save navigation.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'nav' => $nav,
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('navigation', 'Navigation saved.'));

        return $this->redirectToPostedUrl($nav);
    }

    public function actionReorderNav(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $navIds = Json::decode(Craft::$app->getRequest()->getRequiredBodyParam('ids'));
        Navigation::$plugin->getNavs()->reorderNavs($navIds);

        return $this->asSuccess();
    }

    public function actionDeleteNav(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $navId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $nav = Navigation::$plugin->getNavs()->getNavById($navId);

        $this->requirePermission('navigation-deleteNav:' . $nav->uid);

        Navigation::$plugin->getNavs()->deleteNavById($navId);

        return $this->asSuccess();
    }

    public function actionDuplicateNav(): ?Response
    {
        $navId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $nav = Navigation::$plugin->getNavs()->getNavById($navId);

        $newNav = clone $nav;
        $newNav->id = null;
        $newNav->handle = $newNav->handle . rand();
        $newNav->structureId = null;
        $newNav->uid = null;
        $newNav->fieldLayoutId = null;
        $newNav->setSiteSettings($nav->getSiteSettings());

        if (!Navigation::$plugin->getNavs()->saveNav($newNav)) {
            $this->setFailFlash(Craft::t('navigation', 'Unable to duplicate navigation.'));

            return null;
        }

        $elements = NodeElement::find()
            ->navId($nav->id)
            ->level(1)
            ->status(null)
            ->all();;

        $newAttributes = ['navId' => $newNav->id];
        $this->_duplicateElements($elements, $newAttributes);

        return $this->asSuccess();
    }


    // Private Methods
    // =========================================================================

    private function _duplicateElements(array $elements, array $newAttributes = [], array &$duplicatedElementIds = [], ?ElementInterface $newParent = null): void
    {
        $elementsService = Craft::$app->getElements();
        $structuresService = Craft::$app->getStructures();

        foreach ($elements as $element) {
            // Make sure this element wasn't already duplicated, which could
            // happen if it's the descendant of a previously duplicated element
            if (isset($duplicatedElementIds[$element->id])) {
                continue;
            }

            try {
                $duplicate = $elementsService->duplicateElement($element, $newAttributes);
            } catch (Throwable) {
                // Validation error
                continue;
            }

            $duplicatedElementIds[$element->id] = true;

            if ($newParent) {
                // Append it to the duplicate of $element’s parent
                $structuresService->append($element->structureId, $duplicate, $newParent);
            } elseif ($element->structureId) {
                // Place it right next to the original element
                $structuresService->moveAfter($element->structureId, $duplicate, $element);
            }

            // Don't use $element->children() here in case its lft/rgt values have changed
            $children = $element::find()
                ->siteId($element->siteId)
                ->descendantOf($element->id)
                ->descendantDist(1)
                ->status(null)
                ->all();

            $this->_duplicateElements($children, $newAttributes, $duplicatedElementIds, $duplicate);
        }
    }

}
