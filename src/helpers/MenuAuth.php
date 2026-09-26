<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;
use verbb\navigation\base\ElementNodeType;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\nodetypes\Dynamic;

use Craft;
use craft\controllers\StructuresController;
use craft\db\Query;
use craft\elements\db\AssetQuery;
use craft\elements\User;
use craft\helpers\ElementHelper;
use craft\web\Controller;

use yii\base\ActionEvent;
use yii\caching\ArrayCache;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

/**
 * Central CP authorization for menu configuration and node authoring.
 *
 * Craft authenticates the request; this helper enforces Navigation menu grants
 * so services/elements stay usable from console while controllers share one policy.
 */
class MenuAuth
{
    // Static Methods
    // =========================================================================

    public static function canManageMenu(?User $user, MenuSettings|string|null $menuOrUid): bool
    {
        if (!$user || !$menuOrUid) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        $uid = is_string($menuOrUid) ? $menuOrUid : $menuOrUid->uid;

        return $uid ? $user->can('navigation-manageMenu:' . $uid) : false;
    }

    public static function canEditMenu(?User $user, MenuSettings|string|null $menuOrUid): bool
    {
        if (!$user || !$menuOrUid) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        $uid = is_string($menuOrUid) ? $menuOrUid : $menuOrUid->uid;

        return $uid ? $user->can('navigation-editMenu:' . $uid) : false;
    }

    public static function canCreateMenus(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->admin || $user->can('navigation-createMenus');
    }

    public static function canDeleteMenu(?User $user, MenuSettings|string|null $menuOrUid): bool
    {
        if (!$user || !$menuOrUid) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        $uid = is_string($menuOrUid) ? $menuOrUid : $menuOrUid->uid;

        return $uid ? $user->can('navigation-deleteMenu:' . $uid) : false;
    }

    public static function requireManageMenu(Controller $controller, ?MenuSettings $menu): MenuSettings
    {
        $menu = self::_requireMenu($menu);
        $controller->requirePermission('navigation-manageMenu:' . $menu->uid);

        return $menu;
    }

    public static function requireEditMenu(Controller $controller, ?MenuSettings $menu): MenuSettings
    {
        $menu = self::_requireMenu($menu);
        $controller->requirePermission('navigation-editMenu:' . $menu->uid);

        return $menu;
    }

    public static function requireDeleteMenu(Controller $controller, ?MenuSettings $menu): MenuSettings
    {
        $menu = self::_requireMenu($menu);
        $controller->requirePermission('navigation-deleteMenu:' . $menu->uid);

        return $menu;
    }

    public static function requireCreateMenus(Controller $controller): void
    {
        $controller->requirePermission('navigation-createMenus');
    }

    /**
     * Resolves a menu by id and requires manage permission. Rejects missing menus
     * before any write so posted foreign IDs cannot skip authorization.
     */
    public static function requireManageMenuById(Controller $controller, ?int $menuId): MenuSettings
    {
        if (!$menuId) {
            throw new BadRequestHttpException('Invalid menu ID.');
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($menuId);

        return self::requireManageMenu($controller, $menu);
    }

    /**
     * Ensures the current user may manage the menu and that the site is enabled
     * for that menu when a site id is supplied.
     */
    public static function requireManageMenuSite(Controller $controller, ?MenuSettings $menu, ?int $siteId = null): MenuSettings
    {
        $menu = self::requireManageMenu($controller, $menu);

        $siteId ??= Craft::$app->getSites()->getCurrentSite()->id;
        if (!self::canManageMenuSite(Craft::$app->getUser()->getIdentity(), $menu, $siteId)) {
            throw new ForbiddenHttpException('User is not authorized to access this menu site.');
        }

        return $menu;
    }

    public static function canManageMenuSite(?User $user, ?MenuSettings $menu, int $siteId): bool
    {
        $site = Craft::$app->getSites()->getSiteById($siteId);
        return $site !== null && self::canManageMenu($user, $menu)
            && in_array($siteId, $menu->getSiteIds(), false)
            && (!Craft::$app->getIsMultiSite() || $user->admin || $user->can('editSite:' . $site->uid));
    }

    public static function currentUserCanManage(?MenuSettings $menu): bool
    {
        return self::canManageMenu(Craft::$app->getUser()->getIdentity(), $menu);
    }

    public static function canAuthorNode(?User $user, Node $node): bool
    {
        $menu = Navigation::$plugin->getMenus()->getMenuById($node->menuId);
        if (!self::canManageMenuSite($user, $menu, (int)$node->siteId)) {
            return false;
        }
        if (!Navigation::$plugin->getBuildSessions()->canAuthorPendingNode($node, (int)$user->id)) {
            return false;
        }
        $type = $node->nodeType();
        if (!$type || !MenuPermissions::isTypeEnabled($menu->permissions ?? [], $type::class, $type->getPermissionEnabledDefault())) {
            return false;
        }
        if ($parentId = $node->getParentId()) {
            $parent = Node::find()->id($parentId)->menuId($menu->id)->siteId($node->siteId)->status(null)->one();
            if (!$parent || !Navigation::$plugin->getBuildSessions()->canAuthorPendingNode($parent, (int)$user->id)) {
                return false;
            }
        }
        if ($type instanceof Dynamic && !self::canAuthorDynamicSource($node, $user)) {
            return false;
        }
        if (!$type instanceof ElementNodeType || !$node->elementId) {
            return true; // Missing required element IDs are reported by model validation.
        }
        $element = $node->getElement();
        if (!$element || !$element->canView($user)) {
            return false;
        }
        $elementType = $type::getElementType();
        $sources = ElementPickerHelper::filterSourcesForUser($elementType, MenuPermissions::getTypeSources($menu->permissions ?? [], $type::class));
        $picker = ElementPickerHelper::getPickerConfig($menu->permissions ?? [], $type::class, $elementType);
        foreach ($sources as $key) {
            $source = ElementHelper::findSource($elementType, $key, 'modal');
            if (!$source) {
                continue;
            }
            // Both constraints must match independently: picker settings can use
            // the same criterion (e.g. sectionId) as the allowed source.
            $matches = true;
            foreach ([$source, $picker] as $restriction) {
                $query = $elementType::find()->siteId($element->siteId)->status(null);
                Craft::configure($query, $restriction['criteria'] ?? []);

                // Asset volume sources identify their root folder, but Craft's picker
                // exposes that source as the complete navigable folder tree.
                if ($query instanceof AssetQuery && isset($restriction['criteria']['folderId'])) {
                    $query->includeSubfolders();
                }

                if ($condition = $restriction['condition'] ?? null) {
                    Craft::$app->getConditions()->createCondition($condition)->modifyQuery($query);
                }
                if (!$query->andWhere(['elements.id' => $element->id, 'elements_sites.siteId' => $element->siteId])->exists()) {
                    $matches = false;
                    break;
                }
            }
            if ($matches) {
                return true;
            }
        }
        return false;
    }

    /** Structure edits require menu/site access and pending-work ownership, not node authoring access. */
    public static function canMoveNode(?User $user, Node $node): bool
    {
        if (!$user) {
            return false;
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($node->menuId);

        return self::canManageMenuSite($user, $menu, (int)$node->siteId)
            && Navigation::$plugin->getBuildSessions()->canAuthorPendingNode($node, (int)$user->id);
    }

    public static function canAuthorDynamicSource(Node $node, User $user): bool
    {
        $provider = Navigation::$plugin->getDynamicSources()->getProviderClassForNode($node);

        return $provider !== null && (!method_exists($provider, 'canAuthorNode') || $provider::canAuthorNode($node, $user));
    }

    /** Authorize every source before a traversal creates any copies. */
    public static function requireDuplicatableNodes(array $nodes, bool $deep = false, ?int $targetSiteId = null): void
    {
        $user = Craft::$app->getUser()->getIdentity();
        $elements = Craft::$app->getElements();
        foreach ($nodes as $node) {
            $sources = $deep ? array_merge([$node], $node->getDescendants()->status(null)->all()) : [$node];
            foreach ($sources as $source) {
                if (!$user || !$elements->canDuplicate($source, $user) || !self::canAuthorNode($user, $source)) {
                    throw new ForbiddenHttpException('User is not authorized to duplicate this node.');
                }
                if ($targetSiteId !== null) {
                    // Cross-site copies have no destination build-session ownership.
                    if ($source->getIsPendingPublish() || $source->getIsPendingDelete()) {
                        throw new BadRequestHttpException('Save or discard pending changes before copying this node.');
                    }
                    $target = clone $source;
                    $target->id = null;
                    $target->siteId = $targetSiteId;
                    $target->setParentId(null);
                    if (!self::canAuthorNode($user, $target)) {
                        throw new ForbiddenHttpException('User is not authorized to copy this node to the target site.');
                    }
                }
            }
        }
    }


    /** Supplement Craft's coarse structure capability only on its native request boundary. */
    public static function requireNativeStructureAction(ActionEvent $event): void
    {
        $controller = $event->action->controller;
        if (!$controller instanceof StructuresController || $event->action->id !== 'move-element') {
            return;
        }

        $request = Craft::$app->getRequest();
        $rawElementId = $request->getBodyParam('elementId');
        // Element identity is global: a missing site variant must never bypass a Node guard.
        if (!is_scalar($rawElementId) || !is_numeric($rawElementId)
            || !is_a(Craft::$app->getElements()->getElementTypeById((int)$rawElementId) ?? '', Node::class, true)) {
            return; // Craft validates identifiers and permissions for other element types.
        }
        $elementId = self::_moveId($rawElementId);
        $siteId = self::_moveId($request->getBodyParam('siteId'));
        $node = Node::find()->id($elementId)
            ->siteId($siteId)->status(null)
            ->drafts(null)->provisionalDrafts(null)->one();
        if (!$node) {
            throw new BadRequestHttpException('Node is not available on this site.');
        }

        $controller->requirePostRequest();
        $menu = self::requireManageMenuSite($controller,
            Navigation::$plugin->getMenus()->getMenuById($node->menuId), (int)$node->siteId);
        if (self::_moveId($request->getBodyParam('structureId')) !== (int)$menu->structureId) {
            throw new BadRequestHttpException('Invalid menu structure.');
        }

        $user = Craft::$app->getUser()->getIdentity();
        $nodes = array_merge([$node], $node->getDescendants()->status(null)->all());
        foreach (['parentId', 'prevId'] as $param) {
            if ($id = self::_moveId($request->getBodyParam($param), true)) {
                $target = Node::find()->id($id)->menuId($menu->id)->siteId($node->siteId)->status(null)->one();
                if (!$target) {
                    throw new BadRequestHttpException('Invalid move target.');
                }
                $nodes[] = $target;
            }
        }
        foreach ($nodes as $candidate) {
            if (!self::canMoveNode($user, $candidate)) {
                throw new ForbiddenHttpException('User is not authorized to move this node.');
            }
        }
    }

    /** Simulate the submitted order before any write, authorizing only effective moves. */
    public static function requireStructureMoves(MenuSettings $menu, int $siteId, array $moves, array $skipIds = []): void
    {
        $db = Craft::$app->getDb();
        $previousCache = $db->queryCache;
        $previousEnabled = $db->enableQueryCache;
        // This preflight performs no writes. Deduplicate its repeated permission reads
        // in memory only; never reuse authorization data in another call or request.
        $db->queryCache = new ArrayCache();
        $db->enableQueryCache = true;
        try {
            $db->cache(fn() => self::_requireStructureMoves($menu, $siteId, $moves, $skipIds));
        } finally {
            $db->queryCache = $previousCache;
            $db->enableQueryCache = $previousEnabled;
        }
    }

    private static function _requireStructureMoves(MenuSettings $menu, int $siteId, array $moves, array $skipIds): void
    {
        $nodes = Node::find()->menuId($menu->id)->siteId($siteId)->status(null)
            ->drafts(null)->provisionalDrafts(null)->withNodeHierarchy(false)->withProjectedChildren(false)->indexBy('id')->all();
        $rows = (new Query())->select(['elementId', 'lft', 'rgt'])
            ->from('{{%structureelements}}')->where(['structureId' => $menu->structureId])
            ->orderBy(['lft' => SORT_ASC])->all();
        $parents = [];
        $children = [];
        $stack = [];
        foreach ($rows as $row) {
            if (!$row['elementId']) {
                continue;
            }
            while ($stack && end($stack)['rgt'] < $row['lft']) {
                array_pop($stack);
            }
            $id = (int)$row['elementId'];
            $parent = $stack ? (int)end($stack)['elementId'] : 0;
            // The complete physical tree already supplies parent IDs; avoid lazy ancestor reads.
            if (isset($nodes[$id])) {
                $nodes[$id]->setParentId($parent ?: null);
            }
            $parents[$id] = $parent;
            $children[$parent][] = $id;
            $stack[] = $row;
        }

        $user = Craft::$app->getUser()->getIdentity();
        foreach ($moves as $move) {
            if (!is_array($move)) {
                throw new BadRequestHttpException('Invalid move payload.');
            }
            $id = self::_moveId($move['elementId'] ?? null);
            if (in_array($id, $skipIds, true)) {
                continue;
            }
            $parent = self::_moveId($move['parentId'] ?? null, true) ?? 0;
            $prev = self::_moveId($move['prevId'] ?? null, true);
            foreach (array_filter([$id, $parent, $prev]) as $targetId) {
                if (!isset($nodes[$targetId], $parents[$targetId])) {
                    throw new BadRequestHttpException('Invalid move node or target.');
                }
            }
            $destination = $prev ? $parents[$prev] : $parent;
            if ($prev === $id || $destination === $id) {
                throw new BadRequestHttpException('Invalid move target.');
            }
            $oldParent = $parents[$id];
            $oldIndex = array_search($id, $children[$oldParent], true);
            $oldPrev = $oldIndex ? $children[$oldParent][$oldIndex - 1] : null;
            if ($oldParent === $destination && $oldPrev === $prev) {
                continue;
            }

            // A parent move carries descendants, including nodes absent from the posted tree.
            $affected = [$id];
            for ($i = 0; $i < count($affected); $i++) {
                array_push($affected, ...($children[$affected[$i]] ?? []));
            }
            if (in_array($destination, $affected, true)) {
                throw new BadRequestHttpException('Cannot move a node beneath its descendant.');
            }
            foreach (array_unique(array_merge($affected, array_filter([$parent, $prev]))) as $candidateId) {
                if (!isset($nodes[$candidateId]) || !self::canMoveNode($user, $nodes[$candidateId])) {
                    throw new ForbiddenHttpException('User is not authorized to move this node.');
                }
            }

            array_splice($children[$oldParent], $oldIndex, 1);
            $children[$destination] ??= [];
            $index = $prev ? array_search($prev, $children[$destination], true) + 1 : 0;
            array_splice($children[$destination], $index, 0, [$id]);
            $parents[$id] = $destination;
        }
    }

    /** Move endpoints accept identifiers, not ElementQuery selectors or coerced numbers. */
    private static function _moveId(mixed $value, bool $optional = false): ?int
    {
        if ($optional && in_array($value, [null, '', 0, '0'], true)) {
            return null;
        }
        if ((!is_int($value) && !is_string($value))
            || !preg_match('/^[1-9][0-9]*$/D', (string)$value)
            || filter_var($value, FILTER_VALIDATE_INT) === false) {
            throw new BadRequestHttpException('Invalid move identifier.');
        }
        return (int)$value;
    }

    private static function _requireMenu(?MenuSettings $menu): MenuSettings
    {
        if (!$menu || !$menu->uid) {
            throw new BadRequestHttpException('Invalid menu.');
        }

        return $menu;
    }
}
