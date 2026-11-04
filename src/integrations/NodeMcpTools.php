<?php
namespace verbb\navigation\integrations;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\MenuAuth;

use Craft;
use craft\mcp\McpActor;
use craft\mcp\serializers\ElementSerializer;

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Mcp\Schema\ToolAnnotations;

use yii\web\HttpException;

/**
 * Navigation tools for Craft's MCP server (Craft 5.12+). Nodes themselves are read and written through
 * the `elements.*` tools via NodeMcpAdapter.
 */
class NodeMcpTools
{
    public function __construct(
        private McpActor $actor,
        private ElementSerializer $elementSerializer,
    ) {
    }

    /**
     * @return array{menus: list<array<string, mixed>>}
     */
    #[McpTool(
        name: 'navigation.menus.list',
        description: 'Lists Navigation menus the user can manage, with the IDs and handles node tools take.',
        annotations: new ToolAnnotations(readOnlyHint: true),
    )]
    public function listMenus(): array
    {
        $user = $this->actor->user();
        $menus = [];

        foreach (Navigation::$plugin->getMenus()->getAllMenus() as $menu) {
            if (!MenuAuth::canManageMenu($user, $menu)) {
                continue;
            }

            $menus[] = [
                'id' => $menu->id,
                'handle' => $menu->handle,
                'name' => $menu->name,
                'siteIds' => $menu->getSiteIds(),
                'maxLevels' => $menu->maxLevels,
                'maxNodes' => $menu->maxNodes,
            ];
        }

        return ['menus' => $menus];
    }

    /**
     * @param  string  $menu  Menu handle, or menu ID as a string.
     * @param  int|null  $siteId  Site to read the menu in. Defaults to the primary site.
     * @param  bool  $withLinkedElements  Whether to include each linked element's data.
     * @return array{menuId: int, siteId: int, nodes: list<array<string, mixed>>}
     */
    #[McpTool(
        name: 'navigation.menus.tree',
        description: 'Returns a menu as a nested tree of nodes, including disabled nodes and dynamic nodes’ projected children, in one call.',
        annotations: new ToolAnnotations(readOnlyHint: true),
    )]
    public function menuTree(string $menu, ?int $siteId = null, bool $withLinkedElements = false): array
    {
        $menus = Navigation::$plugin->getMenus();
        $settings = ctype_digit($menu) ? $menus->getMenuById((int)$menu) : $menus->getMenuByHandle($menu);
        $siteId ??= Craft::$app->getSites()->getPrimarySite()->id;

        if (!$settings || !MenuAuth::canManageMenuSite($this->actor->user(), $settings, $siteId)) {
            throw new ToolCallException("No menu “{$menu}” you can manage exists on site {$siteId}.");
        }

        // The same tree craft.navigation.tree() returns to templates.
        $nodes = Node::find()->menuId($settings->id)->siteId($siteId)->status(null)->withNodeHierarchy(true)->all();

        return [
            'menuId' => $settings->id,
            'siteId' => $siteId,
            'nodes' => Navigation::$plugin->getNodeRead()->buildNodeTree($nodes, $withLinkedElements, true),
        ];
    }

    /**
     * @param  int  $nodeId  The node to move. Its descendants move with it.
     * @param  string  $operation  before/after place the node next to targetNodeId. prepend/append make it the first or last child of targetNodeId, or of the menu's top level when targetNodeId is null.
     * @param  int|null  $targetNodeId  Sibling for before/after, or parent for prepend/append. Must be in the same menu.
     * @param  int|null  $siteId  Site to load the nodes in. Defaults to the primary site.
     * @return array{moved: true, operation: string, node: array<string, mixed>}
     */
    #[McpTool(
        name: 'navigation.nodes.move',
        description: 'Moves a Navigation node within its menu: before or after a sibling, or to the start or end of a parent or the top level.',
        annotations: new ToolAnnotations(destructiveHint: true),
    )]
    public function moveNode(
        int $nodeId,
        #[Schema(enum: ['before', 'after', 'prepend', 'append'])]
        string $operation,
        ?int $targetNodeId = null,
        ?int $siteId = null,
    ): array {
        $node = $this->node($nodeId, $siteId);
        $target = $targetNodeId !== null ? $this->node($targetNodeId, (int)$node->siteId) : null;

        if (in_array($operation, ['before', 'after'], true) && !$target) {
            throw new ToolCallException("The {$operation} operation needs targetNodeId.");
        }

        if ($target && $target->menuId !== $node->menuId) {
            throw new ToolCallException('The target node is in a different menu.');
        }

        $menu = Navigation::$plugin->getMenus()->getMenuById($node->menuId);
        $siteId = (int)$node->siteId;

        if (!MenuAuth::canManageMenuSite($this->actor->user(), $menu, $siteId)) {
            throw new ToolCallException('You are not authorized to edit this menu.');
        }

        // The menu builder's live-structure save path: Navigation's own authorization, descendant
        // guard, level and node limits, and cache invalidation.
        $moves = [$this->structureMove($node, $operation, $target)];
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
            'node' => $this->elementSerializer->serialize($this->node($nodeId, $siteId)),
        ];
    }

    /**
     * Converts an operation into the `{elementId, parentId, prevId}` move the menu builder submits,
     * where the node goes after `prevId`, or first under `parentId` when `prevId` is null.
     *
     * @return array{elementId: int, parentId: int|null, prevId: int|null}
     */
    private function structureMove(Node $node, string $operation, ?Node $target): array
    {
        if (in_array($operation, ['before', 'after'], true)) {
            $parentId = $target->getParent()?->id;
            $siblings = $this->children($node, $parentId);
            $index = array_search($target->id, $siblings, true);
            $prevId = $operation === 'after' ? $target->id : ($siblings[$index - 1] ?? null);
        } else {
            $parentId = $target?->id;
            $siblings = $this->children($node, $parentId);
            $prevId = $operation === 'append' ? (end($siblings) ?: null) : null;
        }

        return ['elementId' => (int)$node->id, 'parentId' => $parentId, 'prevId' => $prevId];
    }

    /**
     * @return list<int> IDs of a parent's children in order, or the top level for a null parent, without the moving node.
     */
    private function children(Node $node, ?int $parentId): array
    {
        $query = Node::find()->menuId($node->menuId)->siteId($node->siteId)->status(null);
        $query = $parentId ? $query->descendantOf($parentId)->descendantDist(1) : $query->level(1);

        return array_values(array_filter(
            array_map('intval', $query->ids()),
            static fn(int $id) => $id !== (int)$node->id,
        ));
    }

    private function node(int $id, ?int $siteId): Node
    {
        $node = Node::find()->id($id)->siteId($siteId)->status(null)->one();

        if (!$node) {
            throw new ToolCallException("Node {$id} not found.");
        }

        return $node;
    }
}
