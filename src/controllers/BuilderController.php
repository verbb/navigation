<?php
namespace verbb\navigation\controllers;

use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\MenuContentFieldLayout;
use verbb\navigation\Navigation;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Html;
use craft\web\Controller;
use craft\web\Response as CraftResponse;

use Throwable;

use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class BuilderController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionGetState(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        return $this->asSuccess(data: Navigation::$plugin->getBuilderState()->getState($menuId, $siteId));
    }

    public function actionSaveDraft(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $buildSessions = Navigation::$plugin->getBuildSessions();

        if (!$buildSessions->isStagingEnabled()) {
            throw new BadRequestHttpException('Builder staging is disabled.');
        }

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        $structureMoves = $this->request->getBodyParam('structureMoves');
        $menuContentDraft = $this->request->getBodyParam('menuContentDraft');

        if ($structureMoves !== null && !is_array($structureMoves)) {
            throw new BadRequestHttpException('Invalid structure moves payload.');
        }

        if ($menuContentDraft !== null && !is_array($menuContentDraft)) {
            throw new BadRequestHttpException('Invalid menu content draft payload.');
        }

        $session = $buildSessions->getOrCreate($menuId, $siteId);
        $buildSessions->saveDraft($session, $structureMoves, $menuContentDraft);

        $session = $buildSessions->getSession($menuId, $siteId);

        return $this->asSuccess(Craft::t('navigation', 'Draft saved.'), [
            'session' => $session ? $buildSessions->sessionToArray($session) : null,
        ]);
    }

    public function actionStageDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $buildSessions = Navigation::$plugin->getBuildSessions();

        if (!$buildSessions->isStagingEnabled()) {
            throw new BadRequestHttpException('Builder staging is disabled.');
        }

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');
        $nodeId = (int)$this->request->getRequiredBodyParam('nodeId');
        $withDescendants = (bool)$this->request->getBodyParam('withDescendants', false);

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        $node = Node::find()
            ->id($nodeId)
            ->siteId($siteId)
            ->menuId($menuId)
            ->status(null)
            ->one();

        if (!$node || $node->getIsPendingDelete()) {
            return $this->asFailure(Craft::t('navigation', 'Couldn’t stage node for deletion.'));
        }

        $session = $buildSessions->getOrCreate($menuId, $siteId);

        try {
            $buildSessions->stageDelete($session, $node, $withDescendants);
        } catch (Throwable $e) {
            Craft::error('Failed to stage node deletion: ' . $e->getMessage(), __METHOD__);

            return $this->asFailure(Craft::t('navigation', 'Couldn’t stage node for deletion.'));
        }

        $session = $buildSessions->getSession($menuId, $siteId);

        return $this->asSuccess(Craft::t('navigation', 'Node staged for deletion. Save menu to apply.'), [
            'session' => $session ? $buildSessions->sessionToArray($session) : null,
            'nodes' => Navigation::$plugin->getBuilderState()->nodesToArray(
                Node::find()->menuId($menuId)->siteId($siteId)->status(null)->orderBy(['structureelements.lft' => SORT_ASC])->all(),
            ),
        ]);
    }

    public function actionMenuContentSlideout(): Response
    {
        $this->requireCpRequest();

        $menuId = (int)$this->request->getParam('menuId');
        $siteId = (int)$this->request->getParam('siteId');

        [$menuElement, $fieldLayout] = $this->_getMenuContentContext($menuId, $siteId);

        return $this->asCpScreen()
            ->docTitle(Craft::t('navigation', 'Menu content'))
            ->title(Craft::t('navigation', 'Menu content'))
            ->action('navigation/builder/menu-content-slideout-save')
            ->submitButtonLabel(Craft::t('app', 'Save'))
            ->prepareScreen(function(CraftResponse $response) use ($menuElement, $fieldLayout, $menuId, $siteId) {
                $formContent = $fieldLayout->createForm($menuElement, false, [
                    'registerDeltas' => true,
                ]);

                $components = [
                    Html::hiddenInput('menuId', (string)$menuId),
                    Html::hiddenInput('siteId', (string)$siteId),
                    Html::hiddenInput('fieldsLocation', 'fields'),
                    $formContent->render(),
                ];

                $response
                    ->tabs($formContent->getTabMenu())
                    ->contentHtml(implode("\n", $components));
            });
    }

    public function actionMenuContentSlideoutSave(): Response
    {
        $this->requirePostRequest();

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        if (!Navigation::$plugin->getMenus()->saveMenuContentFromRequest($menuId, $siteId)) {
            return $this->asFailure(Craft::t('navigation', 'Couldn’t save menu content.'));
        }

        $buildSessions = Navigation::$plugin->getBuildSessions();
        $session = $buildSessions->getSession($menuId, $siteId);

        if ($session && $session->menuContentDraft !== []) {
            $buildSessions->clearMenuContentDraft($session);
        }

        return $this->asSuccess(Craft::t('navigation', 'Menu content saved.'));
    }

    public function actionMenuContentForm(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        $site = Craft::$app->getSites()->getSiteById($siteId);

        if (!$site || !ArrayHelper::firstWhere($nav->getSites(), 'id', $siteId)) {
            throw new BadRequestHttpException("Invalid site ID: $siteId");
        }

        $menuContentTabs = Navigation::$plugin->getMenus()->getMenuContentTabsForBuilder($menuId, $siteId);

        return $this->asSuccess(data: [
            'tabs' => array_map(static fn(array $tab) => [
                'id' => $tab['id'],
                'label' => $tab['label'],
                'html' => $tab['html'],
            ], $menuContentTabs),
        ]);
    }

    public function actionSaveMenuContent(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        if (!Navigation::$plugin->getMenus()->saveMenuContentFromRequest($menuId, $siteId)) {
            return $this->asFailure(Craft::t('navigation', 'Couldn’t save menu content.'));
        }

        $buildSessions = Navigation::$plugin->getBuildSessions();
        $session = $buildSessions->getSession($menuId, $siteId);

        if ($session && $session->menuContentDraft !== []) {
            $buildSessions->clearMenuContentDraft($session);
        }

        return $this->asSuccess(Craft::t('navigation', 'Menu content saved.'));
    }

    public function actionSetNodeStatus(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        [$menuId, $siteId, $nav] = $this->_requireBuilderMenuContext();
        $nodeIds = $this->_normalizeNodeIds($this->request->getBodyParam('nodeIds', []));
        $status = (string)$this->request->getRequiredBodyParam('status');

        if (!in_array($status, ['enabled', 'disabled'], true)) {
            throw new BadRequestHttpException('Invalid status.');
        }

        if ($nodeIds === []) {
            throw new BadRequestHttpException('No nodes selected.');
        }

        $action = Craft::createObject([
            'class' => \craft\elements\actions\SetStatus::class,
            'status' => $status,
        ]);
        $action->setElementType(Node::class);

        $query = Node::find()
            ->id($nodeIds)
            ->menuId($menuId)
            ->siteId($siteId)
            ->status(null);

        if (!$action->performAction($query)) {
            return $this->asFailure($action->getMessage() ?? Craft::t('app', 'Could not update status due to a validation error.'));
        }

        return $this->_builderNodesSuccess(
            $menuId,
            $siteId,
            $action->getMessage() ?? Craft::t('app', 'Status updated.'),
        );
    }

    public function actionDuplicateNodes(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        [$menuId, $siteId, $nav] = $this->_requireBuilderMenuContext();
        $nodeIds = $this->_normalizeNodeIds($this->request->getBodyParam('nodeIds', []));
        $deep = (bool)$this->request->getBodyParam('deep', false);

        if ($nodeIds === []) {
            throw new BadRequestHttpException('No nodes selected.');
        }

        if ($deep && (int)$nav->maxLevels === 1) {
            throw new BadRequestHttpException('This menu does not support nested nodes.');
        }

        $buildSessions = Navigation::$plugin->getBuildSessions();
        $deferPublish = $buildSessions->isStagingEnabled();

        if ($deferPublish) {
            $result = Navigation::$plugin->getNodes()->duplicateNodesForBuilder(
                $menuId,
                $siteId,
                $nodeIds,
                $deep,
                true,
            );

            if ($result['successCount'] === 0) {
                return $this->asFailure(Craft::t('app', 'Could not duplicate elements due to validation errors.'));
            }

            if ($result['duplicatedNodeIds'] !== []) {
                $session = $buildSessions->getOrCreate($menuId, $siteId);
                $buildSessions->stageAddedNodes($session, $result['duplicatedNodeIds']);
            }

            if ($result['failCount'] !== 0) {
                $message = Craft::t('app', 'Could not duplicate all elements due to validation errors.');
            } else {
                $message = Craft::t('navigation', 'Node{plural} duplicated. Save menu to apply.', [
                    'plural' => $result['successCount'] > 1 ? 's' : '',
                ]);
            }

            return $this->_builderNodesSuccess($menuId, $siteId, $message);
        }

        $action = Craft::createObject([
            'class' => \craft\elements\actions\Duplicate::class,
            'deep' => $deep,
        ]);
        $action->setElementType(Node::class);

        $query = Node::find()
            ->id($nodeIds)
            ->menuId($menuId)
            ->siteId($siteId)
            ->status(null);

        if ($deep) {
            $query->orderBy(['structureelements.lft' => SORT_ASC]);
        }

        if (!$action->performAction($query)) {
            return $this->asFailure($action->getMessage() ?? Craft::t('app', 'Could not duplicate elements due to validation errors.'));
        }

        return $this->_builderNodesSuccess(
            $menuId,
            $siteId,
            $action->getMessage() ?? Craft::t('app', 'Elements duplicated.'),
        );
    }


    // Private Methods
    // =========================================================================

    private function _requireBuilderMenuContext(): array
    {
        $menuId = (int)$this->request->getRequiredBodyParam('menuId');
        $siteId = (int)$this->request->getRequiredBodyParam('siteId');

        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        return [$menuId, $siteId, $nav];
    }

    private function _normalizeNodeIds(mixed $nodeIds): array
    {
        if (!is_array($nodeIds)) {
            throw new BadRequestHttpException('Invalid node IDs payload.');
        }

        return array_values(array_unique(array_map('intval', $nodeIds)));
    }

    private function _builderNodesSuccess(int $menuId, int $siteId, string $message): Response
    {
        $buildSessions = Navigation::$plugin->getBuildSessions();
        $session = $buildSessions->getSession($menuId, $siteId);

        return $this->asSuccess($message, [
            'session' => $session ? $buildSessions->sessionToArray($session) : null,
            'nodes' => Navigation::$plugin->getBuilderState()->nodesToArray(
                Node::find()->menuId($menuId)->siteId($siteId)->status(null)->orderBy(['structureelements.lft' => SORT_ASC])->all(),
            ),
        ]);
    }

    private function _getMenuContentContext(int $menuId, int $siteId): array
    {
        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new BadRequestHttpException("Invalid menu ID: $menuId");
        }

        $this->requirePermission('navigation-manageMenu:' . $nav->uid);

        $site = Craft::$app->getSites()->getSiteById($siteId);

        if (!$site || !ArrayHelper::firstWhere($nav->getSites(), 'id', $siteId)) {
            throw new BadRequestHttpException("Invalid site ID: $siteId");
        }

        $menuElement = Menu::find()->id($menuId)->siteId($siteId)->status(null)->one();

        if (!$menuElement) {
            throw new NotFoundHttpException('Menu element not found.');
        }

        $fieldLayout = MenuContentFieldLayout::withoutTitle($menuElement->getFieldLayout());

        if (!$fieldLayout || !MenuContentFieldLayout::hasCustomFields($fieldLayout)) {
            throw new NotFoundHttpException('Menu content fields not found.');
        }

        return [$menuElement, $fieldLayout];
    }
}
