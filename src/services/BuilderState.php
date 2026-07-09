<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\base\NodeType;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\helpers\BuilderSchemaHelper;
use verbb\navigation\helpers\MenuContentFieldLayout;
use verbb\navigation\helpers\NodeTypeHelper;
use verbb\navigation\models\BuildSession as BuildSessionModel;
use verbb\navigation\models\MenuSettings;

use Craft;
use craft\base\Component;
use craft\helpers\Markdown;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;

use Throwable;

use yii\web\NotFoundHttpException;

class BuilderState extends Component
{
    // Public Methods
    // =========================================================================

    public function getState(int $menuId, int $siteId): array
    {
        $nav = Navigation::$plugin->getMenus()->getMenuById($menuId);

        if (!$nav) {
            throw new NotFoundHttpException("Invalid menu ID: $menuId");
        }

        $site = Craft::$app->getSites()->getSiteById($siteId);

        if (!$site) {
            throw new NotFoundHttpException("Invalid site ID: $siteId");
        }

        $buildSessions = Navigation::$plugin->getBuildSessions();
        $stagingEnabled = $buildSessions->isStagingEnabled();
        $session = $stagingEnabled ? $buildSessions->getOrCreate($menuId, $siteId) : null;

        $nodesService = Navigation::$plugin->getNodes();
        $nodes = NodeElement::find()
            ->menuId($menuId)
            ->siteId($siteId)
            ->status(null)
            ->orderBy(['structureelements.lft' => SORT_ASC])
            ->all();

        $parentOptions = $nodesService->getParentOptions($nodes, $nav);
        $menuElement = Menu::find()->id($menuId)->siteId($siteId)->status(null)->one();
        $menuFieldLayout = $menuElement ? MenuContentFieldLayout::withoutTitle($menuElement->getFieldLayout()) : null;
        $copyToSiteTargets = $this->getCopyToSiteTargets($nav, $siteId);

        return [
            'menu' => [
                'id' => $nav->id,
                'uid' => $nav->uid,
                'name' => $nav->name,
                'handle' => $nav->handle,
                'instructions' => $nav->instructions,
                'instructionsHtml' => $nav->instructions ? Markdown::process($nav->instructions) : null,
                'maxLevels' => $nav->maxLevels ? (int)$nav->maxLevels : null,
                'structureId' => $nav->structureId,
                'showSiteMenu' => (bool)$nav->showSiteMenu,
                'propagationMethod' => $nav->propagationMethod,
                'hasMultiSiteNodes' => $nav->getHasMultiSiteNodes(),
                'defaultEnabledForPropagatedSites' => (bool)$nav->defaultEnabledForPropagatedSites,
            ],
            'site' => [
                'id' => $site->id,
                'handle' => $site->handle,
                'name' => $site->name,
            ],
            'canCopyToSite' => $copyToSiteTargets !== [],
            'copyToSiteTargets' => $copyToSiteTargets,
            'stagingEnabled' => $stagingEnabled,
            'session' => $session ? $this->sessionToArray($session) : null,
            'nodes' => $this->nodesToArray($nodes),
            'parentOptions' => $parentOptions,
            'builderTabs' => $this->getBuilderTabsForApi($nav, $parentOptions),
            'menuContent' => [
                'hasFields' => $menuFieldLayout && MenuContentFieldLayout::hasCustomFields($menuFieldLayout),
            ],
            'permissions' => [
                'canManage' => Craft::$app->getUser()->checkPermission('navigation-manageMenu:' . $nav->uid),
                'canEditSettings' => Craft::$app->getUser()->checkPermission('navigation-editMenu:' . $nav->uid),
            ],
            'settingsUrl' => UrlHelper::cpUrl('navigation/menus/edit/' . $nav->id),
            'elementType' => NodeElement::class,
        ];
    }

    public function sessionToArray(BuildSessionModel $session): array
    {
        return [
            'uid' => $session->uid,
            'structureMoves' => $session->structureMoves,
            'addedNodeIds' => $session->addedNodeIds,
            'stagedDeletes' => $session->stagedDeletes,
            'menuContentDraft' => $session->menuContentDraft,
            'changeCount' => $session->getChangeCount(true),
            'hasStructureMoves' => $session->hasStructureMoves(),
            'hasMenuContentDraft' => $session->menuContentDraft !== [],
        ];
    }

    /**
     * Copy-to-site is only offered when nodes are not auto-propagated (propagation "none").
     */
    public function getCopyToSiteTargets(MenuSettings $nav, int $currentSiteId): array
    {
        if (
            !Craft::$app->getIsMultiSite()
            || $nav->propagationMethod !== MenuSettings::PROPAGATION_METHOD_NONE
        ) {
            return [];
        }

        $menuEnabledSiteIds = array_flip($nav->getSiteIds());
        $targets = [];

        foreach (Craft::$app->getSites()->getEditableSites() as $editableSite) {
            $editableSiteId = (int)$editableSite->id;

            if ($editableSiteId === $currentSiteId || !isset($menuEnabledSiteIds[$editableSiteId])) {
                continue;
            }

            $targets[] = [
                'id' => $editableSiteId,
                'handle' => $editableSite->handle,
                'name' => $editableSite->name,
            ];
        }

        return $targets;
    }

    public function nodesToArray(array $nodes): array
    {
        $parentIds = [];

        foreach ($nodes as $node) {
            $parent = $node->getParent();

            if ($parent) {
                $parentIds[(int)$parent->id] = true;
            }
        }

        return array_map(
            fn(NodeElement $node) => $this->nodeToArray($node, isset($parentIds[(int)$node->id])),
            $nodes,
        );
    }

    public function nodeToArray(NodeElement $node, bool $hasDescendants = false): array
    {
        $parent = $node->getParent();

        $typeColorHex = $node->getTypeColorHex();
        $classNameParts = explode('\\', (string)$node->type);
        $className = array_pop($classNameParts);

        return [
            'id' => (int)$node->id,
            'title' => (string)$node->title,
            'type' => (string)$node->type,
            'typeLabel' => (string)$node->getTypeLabel(),
            'typeClass' => 'node-type-' . StringHelper::toKebabCase($className),
            'typeColorRgb' => NodeTypeHelper::rgbTriplet($typeColorHex),
            'typeTextColorRgb' => NodeTypeHelper::accessibleTextColorRgb($typeColorHex, 0.1),
            'url' => $node->url,
            'level' => (int)$node->level,
            'parentId' => $parent ? (int)$parent->id : null,
            'status' => $node->getStatus(),
            'newWindow' => (bool)$node->newWindow,
            'classes' => $node->classes ?: null,
            'pendingAdd' => $node->getIsPendingPublish(),
            'pendingDelete' => $node->getIsPendingDelete(),
            'pendingEdit' => $node->getIsPendingEdit(),
            'enabled' => (bool)$node->enabled,
            'enabledForSite' => (bool)$node->getEnabledForSite(),
            'hasDescendants' => $hasDescendants,
            'isElementLinked' => (bool)($node->elementId && $node->isElement()),
        ];
    }

    public function getBuilderTabsForApi(MenuSettings $nav, array $parentOptions): array
    {
        $tabs = [];
        $registeredTabs = Navigation::$plugin->getMenus()->getBuilderTabs($nav);
        $showParent = !$nav->maxLevels || (int)$nav->maxLevels > 1;

        foreach ($registeredTabs as $tabId => $tab) {
            $typeClass = is_string($tab['type']) ? $tab['type'] : $tab['type']::class;
            $entry = [
                'id' => $tabId,
                'label' => $tab['label'],
                'button' => $tab['button'],
                'category' => $tab['category'],
                'type' => $typeClass,
            ];

            if ($tab['category'] === 'element') {
                $entry['elementType'] = $tab['elementType'] ?? $tab['type'];
                $entry['sources'] = $tab['sources'] ?? null;
                $entry['pickerConfig'] = $tab['pickerConfig'] ?? null;
            } elseif ($tab['category'] === 'nodeType' && isset($tab['nodeType'])) {
                $nodeType = $tab['nodeType'];
                $entry['hasTitle'] = $nodeType::hasTitle();
                $entry['hasUrl'] = $nodeType::hasUrl();
                $entry['hasNewWindow'] = $nodeType::hasNewWindow();
            }

            $entry['schemaIndex'] = BuilderSchemaHelper::compileAddNodeSchema($entry, $showParent, $parentOptions, $nav);
            $entry['defaultValues'] = [
                'parentId' => null,
                'newWindow' => false,
                'title' => '',
                'url' => '',
                'enabledForPropagatedSites' => (bool)$nav->defaultEnabledForPropagatedSites,
                'data' => is_subclass_of($typeClass, NodeType::class)
                    ? $typeClass::getAddNodeDefaultData()
                    : [],
            ];

            $tabs[] = $entry;
        }

        return $tabs;
    }

    /**
     * Apply session menu content draft values onto a menu element before rendering fields.
     */
    public function applyMenuContentDraft(Menu $menuElement, array $draft): void
    {
        foreach ($draft as $handle => $value) {
            try {
                $menuElement->setFieldValue($handle, $value);
            } catch (Throwable) {
                // Skip unknown handles from stale drafts.
            }
        }
    }
}
