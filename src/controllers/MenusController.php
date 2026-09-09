<?php
namespace verbb\navigation\controllers;

use verbb\navigation\deprecations\DeprecationHelper;
use verbb\navigation\deprecations\MenusControllerDeprecations;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\fieldlayoutelements\ClassesField;
use verbb\navigation\fieldlayoutelements\CustomAttributesField;
use verbb\navigation\fieldlayoutelements\NewWindowField;
use verbb\navigation\fieldlayoutelements\UrlSuffixField;
use verbb\navigation\helpers\MenuAuth;
use verbb\navigation\helpers\MenuContentFieldLayout;
use verbb\navigation\helpers\MenuPermissions;
use verbb\navigation\helpers\Plugin as NavigationPluginHelper;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\MenuSiteSettings;
use verbb\navigation\models\Settings;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\models\FieldLayoutTab;
use craft\web\Controller;

use Throwable;

use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MenusController extends Controller
{
    // Traits
    // =========================================================================

    use MenusControllerDeprecations;


    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        if ($response = $this->_redirectLegacyNavsUrl('navigation/menus')) {
            return $response;
        }

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        // Get the current site from the global query param
        $siteHandle = $this->request->getParam('site', Craft::$app->getSites()->getPrimarySite()->handle);
        $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        $navigations = Navigation::$plugin->getMenus()->getEditableMenusForSite($site);

        $editable = $settings->bypassProjectConfig || Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        return $this->renderTemplate('navigation/menus/index', [
            'navigations' => $navigations,
            'editable' => $editable,
        ]);
    }

    public function actionEditMenu(int $menuId = null, ?int $navId = null, MenuSettings $nav = null): Response
    {
        $menuId = DeprecationHelper::resolveMenuIdParam($menuId, $navId, 'navigation.cp.editMenu.navId');

        if ($response = $this->_redirectLegacyNavsUrl($menuId ? "navigation/menus/edit/$menuId" : 'navigation/menus/new')) {
            return $response;
        }

        if ($nav === null) {
            if ($menuId !== null) {
                $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

                if (!$nav) {
                    throw new NotFoundHttpException('Menu not found');
                }
            } else {
                $nav = new MenuSettings();

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
            $this->requirePermission('navigation-editMenu:' . $nav->uid);
        } else {
            $this->requirePermission('navigation-createMenus');
        }

        $site = Cp::requestedSite($nav->id ? $nav->getSites() : null) ?? Craft::$app->getSites()->getPrimarySite();

        return $this->renderTemplate('navigation/menus/_edit', [
            'menuId' => $menuId,
            'nav' => $nav,
            'site' => $site,
        ]);
    }

    public function actionBuildMenu(int $menuId = null, ?int $navId = null): Response
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();
        $defaultSite = false;

        $menuId = DeprecationHelper::resolveMenuIdParam($menuId, $navId, 'navigation.cp.buildMenu.navId');

        if ($menuId !== null && ($response = $this->_redirectLegacyNavsUrl("navigation/menus/build/$menuId"))) {
            return $response;
        }

        if ($menuId !== null) {
            $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

            if (!$nav) {
                throw new NotFoundHttpException('Menu not found');
            }
        } else {
            $nav = new MenuSettings();
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
            throw new NotFoundHttpException('Menu not enabled for site: ' . $siteHandle);
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        if ($settings->builderLiveStructure) {
            Craft::$app->getSession()->authorize('editStructure:' . $nav->structureId);
        }

        $editable = $settings->bypassProjectConfig || Craft::$app->getConfig()->getGeneral()->allowAdminChanges;

        NavigationPluginHelper::registerCpBuilderAssets();

        $menuElement = Menu::find()->id($nav->id)->siteId($site->id)->status(null)->one();

        return $this->renderTemplate('navigation/menus/_build', [
            'menuId' => $menuId,
            'nav' => $nav,
            'menu' => $menuElement,
            'site' => $site,
            'defaultSite' => $defaultSite,
            'settings' => $settings,
            'editable' => $editable,
        ]);
    }

    public function actionSaveMenu(): ?Response
    {
        $this->requirePostRequest();

        $menuId = $this->request->getBodyParam('menuId');

        if ($menuId) {
            $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

            if (!$nav) {
                throw new BadRequestHttpException("Invalid menu ID: $menuId");
            }

            MenuAuth::requireEditMenu($this, $nav);
        } else {
            MenuAuth::requireCreateMenus($this);
            $nav = new MenuSettings();
        }

        $nav->name = $this->request->getBodyParam('name');
        $nav->handle = $this->request->getBodyParam('handle');
        $nav->instructions = $this->request->getBodyParam('instructions');
        $nav->propagationMethod = $this->request->getBodyParam('propagationMethod', MenuSettings::PROPAGATION_METHOD_ALL);
        $nav->titleTranslationMethod = $this->request->getBodyParam('titleTranslationMethod', Field::TRANSLATION_METHOD_SITE);
        $nav->titleTranslationKeyFormat = $this->request->getBodyParam('titleTranslationKeyFormat') ?: null;
        $nav->defaultEnabledForPropagatedSites = (bool)$this->request->getBodyParam('defaultEnabledForPropagatedSites', true);
        $nav->maxLevels = (int)$this->request->getBodyParam('maxLevels') ?: null;
        $nav->maxNodes = (int)$this->request->getBodyParam('maxNodes') ?: null;
        $nav->maxNodesSettings = $this->request->getBodyParam('maxNodesSettings') ?: [];
        $nav->permissions = MenuPermissions::normalize($this->request->getBodyParam('permissions') ?? []);
        $nav->defaultPlacement = $this->request->getBodyParam('defaultPlacement') ?? $nav->defaultPlacement;
        $nav->showSiteMenu = $this->request->getBodyParam('showSiteMenu');

        $allSiteSettings = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $postedSettings = $this->request->getBodyParam('sites.' . $site->handle);

            $siteSettings = new MenuSiteSettings();
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

        $menuFieldLayout = MenuContentFieldLayout::withoutTitle(
            Craft::$app->getFields()->assembleLayoutFromPost('menuFieldLayout'),
        );
        $menuFieldLayout->type = Menu::class;
        $nav->setMenuFieldLayout($menuFieldLayout);

        if (!Navigation::$plugin->getMenus()->saveMenu($nav)) {
            $this->setFailFlash(Craft::t('navigation', 'Unable to save menu.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'nav' => $nav,
            ]);

            return null;
        }

        if ($nav->id && !$this->_saveMenuContent($nav)) {
            $this->setFailFlash(Craft::t('navigation', 'Menu saved, but menu content could not be saved.'));

            return $this->redirectToPostedUrl($nav);
        }

        $this->setSuccessFlash(Craft::t('navigation', 'Menu saved.'));

        return $this->redirectToPostedUrl($nav);
    }

    public function actionReorderMenu(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Index UI only offers reorder to users who can create menus.
        MenuAuth::requireCreateMenus($this);

        $navIds = Json::decode($this->request->getRequiredBodyParam('ids'));
        Navigation::$plugin->getMenus()->reorderMenus($navIds);

        return $this->asSuccess();
    }

    public function actionDeleteMenu(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $menuId = $this->request->getRequiredBodyParam('id');
        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        MenuAuth::requireDeleteMenu($this, $nav);

        Navigation::$plugin->getMenus()->deleteMenuById($menuId);

        return $this->asSuccess();
    }

    public function actionDuplicateMenu(): ?Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $menuId = $this->request->getRequiredBodyParam('id');
        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        // Duplicate reads the source menu and creates a new one.
        MenuAuth::requireManageMenu($this, $nav);
        MenuAuth::requireCreateMenus($this);

        $newNav = clone $nav;
        $newNav->id = null;
        $newNav->handle = $newNav->handle . rand();
        $newNav->structureId = null;
        $newNav->uid = null;
        $newNav->fieldLayoutId = null;
        $newNav->setSiteSettings($nav->getSiteSettings());

        if (!Navigation::$plugin->getMenus()->saveMenu($newNav)) {
            $this->setFailFlash(Craft::t('navigation', 'Unable to duplicate menu.'));

            return null;
        }

        $elements = NodeElement::find()
            ->menuId($nav->id)
            ->level(1)
            ->status(null)
            ->all();

        $newAttributes = ['menuId' => $newNav->id];
        $this->_duplicateElements($elements, $newAttributes);

        return $this->asSuccess();
    }


    // Private Methods
    // =========================================================================

    private function _saveMenuContent(MenuSettings $nav): bool
    {
        return Navigation::$plugin->getMenus()->saveMenuContentFromRequest((int)$nav->id);
    }

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
