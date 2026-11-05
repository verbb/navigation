<?php
namespace verbb\navigation\integrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\MenuAuth;
use verbb\navigation\helpers\NodeHierarchy;
use verbb\navigation\models\ProjectedNode;

use Craft;
use craft\mcp\Authorization;
use craft\mcp\McpActor;
use craft\mcp\serializers\ElementSerializer;

use yii\web\HttpException;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Mcp\Schema\ToolAnnotations;

/**
 * Navigation tools for Craft's MCP server (Craft 5.12+). Nodes themselves are read and written through
 * the `elements.*` tools via NodeMcpAdapter.
 */
class NodeMcpTools
{
    // Public Methods
    // =========================================================================

    public function __construct(
        private McpActor $_actor,
        private ElementSerializer $_elementSerializer,
    ) {
    }

    #[McpTool(
        name: 'navigation.menus.list',
        description: 'Lists Navigation menus the user can manage, with the IDs and handles node tools take.',
        annotations: new ToolAnnotations(readOnlyHint: true),
    )]
    public function listMenus(): array
    {
        $user = $this->_actor->user();
        $menus = [];

        foreach (Navigation::$plugin->getMenus()->getAllMenus() as $menu) {
            if (!MenuAuth::canManageMenu($user, $menu)) {
                continue;
            }

            $siteIds = array_values(array_filter($menu->getSiteIds(), fn($siteId) => MenuAuth::canEditSite($user, (int)$siteId)));

            if (!$siteIds) {
                continue;
            }

            $menus[] = [
                'id' => $menu->id,
                'handle' => $menu->handle,
                'name' => $menu->name,
                'siteIds' => $siteIds,
                'maxLevels' => $menu->maxLevels,
                'maxNodes' => $menu->maxNodes,
            ];
        }

        return ['menus' => $menus];
    }

    #[McpTool(
        name: 'navigation.menus.tree',
        description: 'Returns a menu as a nested tree of nodes, including disabled nodes and dynamic nodes’ projected children, in one call.',
        annotations: new ToolAnnotations(readOnlyHint: true),
    )]
    public function menuTree(
        #[Schema(description: 'Menu handle, or menu ID as a string.')]
        string $menu,
        #[Schema(description: 'Site to read. Defaults to the primary site.')]
        ?int $siteId = null,
        #[Schema(description: 'Include linked elements the acting user can view.')]
        bool $withLinkedElements = false,
    ): array {
        $menus = Navigation::$plugin->getMenus();
        $settings = ctype_digit($menu) ? $menus->getMenuById((int)$menu) : $menus->getMenuByHandle($menu);
        $siteId ??= Craft::$app->getSites()->getPrimarySite()->id;

        if (!$settings || !MenuAuth::canManageMenuSite($this->_actor->user(), $settings, $siteId)) {
            throw new ToolCallException("No menu “{$menu}” you can manage exists on site {$siteId}.");
        }

        // Reuse Navigation’s hierarchy, but serialize for the MCP actor rather than a trusted template.
        $nodes = Node::find()->menuId($settings->id)->siteId($siteId)->status(null)->withNodeHierarchy(true)->all();

        $nested = [];

        foreach (NodeHierarchy::childrenByParent($nodes) as $children) {
            foreach ($children as $child) {
                $nested[$child->id] = true;
            }
        }

        $roots = array_filter($nodes, fn($node) => $node instanceof Node && !isset($nested[$node->id]));

        return [
            'menuId' => $settings->id,
            'siteId' => $siteId,
            'nodes' => array_values(array_map(fn(Node $node) => $this->_treeNode($node, $withLinkedElements), $roots)),
        ];
    }

    #[McpTool(
        name: 'navigation.nodes.move',
        description: 'Moves a Navigation node within its menu: before or after a sibling, or to the start or end of a parent or the top level.',
        annotations: new ToolAnnotations(destructiveHint: true),
    )]
    public function moveNode(
        #[Schema(description: 'Node to move, together with its descendants.')]
        int $nodeId,
        #[Schema(enum: ['before', 'after', 'prepend', 'append'], description: 'before/after use a sibling target; prepend/append use a parent, or null for the menu root.')]
        string $operation,
        #[Schema(description: 'Sibling or parent in the same menu.')]
        ?int $targetNodeId = null,
        #[Schema(description: 'Site to load. Defaults to the primary site.')]
        ?int $siteId = null,
    ): array {
        $siteId ??= Craft::$app->getSites()->getPrimarySite()->id;

        if (!in_array($operation, ['before', 'after', 'prepend', 'append'], true)) {
            throw new ToolCallException('Invalid move operation.');
        }

        if ($nodeId === $targetNodeId) {
            throw new ToolCallException('A node cannot be moved relative to itself.');
        }

        $node = $this->_node($nodeId, $siteId);
        $target = $targetNodeId !== null ? $this->_node($targetNodeId, (int)$node->siteId) : null;

        if (in_array($operation, ['before', 'after'], true) && !$target) {
            throw new ToolCallException("The {$operation} operation needs targetNodeId.");
        }

        if ($target && $target->menuId !== $node->menuId) {
            throw new ToolCallException('The target node is in a different menu.');
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($node->menuId);
        $siteId = (int)$node->siteId;

        if (!MenuAuth::canManageMenuSite($this->_actor->user(), $menu, $siteId)) {
            throw new ToolCallException('You are not authorized to edit this menu.');
        }

        // The menu builder's live-structure save path: Navigation's own authorization, descendant
        // guard, level and node limits, and cache invalidation.
        $moves = [$this->_structureMove($node, $operation, $target)];
        $cache = Navigation::$plugin->getNavigationCache();

        try {
            $cache->batchInvalidations(function() use ($menu, $siteId, $moves, $cache) {
                Craft::$app->getDb()->transaction(function() use ($menu, $siteId, $moves) {
                    MenuAuth::requireStructureMoves($menu, $siteId, $moves);
                    Navigation::$plugin->getBuildSessions()->applyStructureMoves($menu, $siteId, $moves);
                });

                $cache->invalidateMenuSite($menu->uid, $siteId);
            });
        } catch (HttpException $e) {
            throw new ToolCallException($e->getMessage());
        }

        return [
            'moved' => true,
            'operation' => $operation,
            'node' => $this->_elementSerializer->serialize($this->_node($nodeId, $siteId)),
        ];
    }


    // Private Methods
    // =========================================================================

    private function _treeNode(Node|ProjectedNode $node, bool $withLinkedElements): array
    {
        $projected = $node instanceof ProjectedNode;
        $data = $projected ? [
            'id' => $node->id,
            'title' => $node->title,
            'level' => $node->level,
            'url' => $node->getUrl(),
        ] : $this->_elementSerializer->serialize($node, data: [
            'type' => $node::class,
            ...$node->toArray($node->attributes()),
            'url' => $node->getUrl(),
        ], filterNulls: false);
        $data['isProjected'] = $projected;
        $data['element'] = null;

        // Managing a menu does not grant access to the content it links to.
        if ($withLinkedElements && ($linked = $node->getElement()) && Authorization::allows($this->_actor->user(), 'view', $linked)) {
            $data['element'] = $this->_elementSerializer->serialize($linked);
        }

        $children = $projected ? $node->getChildren() : $node->getChildren()->all();
        $data['children'] = array_map(fn(Node|ProjectedNode $child) => $this->_treeNode($child, $withLinkedElements), $children);

        return $data;
    }

    /**
     * Converts an operation into the `{elementId, parentId, prevId}` move the menu builder submits,
     * where the node goes after `prevId`, or first under `parentId` when `prevId` is null.
     */
    private function _structureMove(Node $node, string $operation, ?Node $target): array
    {
        if (in_array($operation, ['before', 'after'], true)) {
            $parentId = $target->getParent()?->id;
            $siblings = $this->_children($node, $parentId);
            $index = array_search($target->id, $siblings, true);
            $prevId = $operation === 'after' ? $target->id : ($siblings[$index - 1] ?? null);
        } else {
            $parentId = $target?->id;
            $siblings = $this->_children($node, $parentId);
            $prevId = $operation === 'append' ? (end($siblings) ?: null) : null;
        }

        return ['elementId' => (int)$node->id, 'parentId' => $parentId, 'prevId' => $prevId];
    }

    private function _children(Node $node, ?int $parentId): array
    {
        $query = Node::find()->menuId($node->menuId)->siteId($node->siteId)->status(null);
        $query = $parentId ? $query->descendantOf($parentId)->descendantDist(1) : $query->level(1);

        return array_values(array_filter(
            array_map('intval', $query->ids()),
            static fn(int $id) => $id !== (int)$node->id,
        ));
    }

    private function _node(int $id, ?int $siteId): Node
    {
        $node = Node::find()->id($id)->siteId($siteId)->status(null)->one();

        if (!$node) {
            throw new ToolCallException("Node {$id} not found.");
        }

        return $node;
    }
}
