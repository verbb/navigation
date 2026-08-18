<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\base\ProjectingNodeType;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\ProjectedNode;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\db\EagerLoadPlan;
use craft\helpers\ArrayHelper;

class NodeRead extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Returns cached nav nodes when available, otherwise executes the query and stores the result.
     */
    public function fetchCachedAll(NodeQuery $query): array
    {
        $cache = Navigation::$plugin->getNavigationCache();
        $cachedNodes = $cache->getCachedNodes($query);

        if ($cachedNodes !== null) {
            if ($query->shouldWireNodeHierarchy()) {
                $cachedNodes = $this->assembleNodeHierarchyForQueryResult(
                    $query,
                    $cachedNodes,
                    false,
                    $query->shouldProjectChildren(),
                );
            }

            Navigation::$plugin->getActiveMatcher()->resolve($cachedNodes);

            if ($query->shouldProjectChildren()) {
                Navigation::$plugin->getActiveMatcher()->resolveProjections($cachedNodes);
            }

            if ($query->withMenu) {
                $this->eagerLoadMenus($cachedNodes);
            }

            return $cachedNodes;
        }

        $query->bypassReadCache = true;
        $query->skipPostCacheProcessing = true;
        $nodes = $query->all();

        $cache->setCachedNodes($query, $nodes);
        Navigation::$plugin->getActiveMatcher()->resolve($nodes);

        if ($query->shouldProjectChildren()) {
            Navigation::$plugin->getActiveMatcher()->resolveProjections($nodes);
        }

        if ($query->withMenu) {
            $this->eagerLoadMenus($nodes);
        }

        return $nodes;
    }

    public function eagerLoadLinkedElements(array $nodes): array
    {
        $nodeGroups = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement || !$node->elementId || !$node->isElement()) {
                continue;
            }

            $nodeType = $node->nodeType();

            if (!$nodeType instanceof \verbb\navigation\base\ElementNodeType) {
                continue;
            }

            $elementType = $nodeType::getElementType();

            $siteId = $node->getElementSiteId();
            $groupKey = $elementType . ':' . ($siteId ?? '*');
            $nodeGroups[$groupKey]['type'] = $elementType;
            $nodeGroups[$groupKey]['siteId'] = $siteId;
            $nodeGroups[$groupKey]['elementIds'][$node->elementId] = $node->elementId;
            $nodeGroups[$groupKey]['nodes'][] = $node;
        }

        foreach ($nodeGroups as $nodeGroup) {
            $elementType = $nodeGroup['type'];
            $elements = Craft::$app->getElements()
                ->createElementQuery($elementType)
                ->siteId($nodeGroup['siteId'])
                ->status(null)
                ->drafts(null)
                ->provisionalDrafts(null)
                ->revisions(null)
                ->id(array_values($nodeGroup['elementIds']))
                ->all();

            $elementsById = [];

            foreach ($elements as $element) {
                $elementsById[$element->id] = $element;
            }

            foreach ($nodeGroup['nodes'] as $node) {
                $node->setElement($elementsById[$node->elementId] ?? null);
            }
        }

        return $nodes;
    }

    /**
     * Batch-hydrates parent Menu elements (including custom fields) for a node list.
     */
    public function eagerLoadMenus(array $nodes): array
    {
        $groups = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement || !$node->menuId) {
                continue;
            }

            $siteId = $node->siteId ?? Craft::$app->getSites()->getCurrentSite()->id;
            $key = $node->menuId . ':' . $siteId;

            $groups[$key]['menuId'] = (int)$node->menuId;
            $groups[$key]['siteId'] = (int)$siteId;
            $groups[$key]['nodes'][] = $node;
        }

        foreach ($groups as $group) {
            $menu = Menu::find()
                ->id($group['menuId'])
                ->siteId($group['siteId'])
                ->status(null)
                ->one();

            foreach ($group['nodes'] as $node) {
                $node->setEagerLoadedMenu($menu);
            }
        }

        return $nodes;
    }

    /**
     * Applies hierarchy wiring for a query result, loading the full nav tree when needed.
     *
     * Level- or ID-scoped queries only return a subset of nodes, but child relationships
     * are derived from the complete structure-ordered nav list.
     */
    public function assembleNodeHierarchyForQueryResult(NodeQuery $query, array $nodes, bool $includeLinkedElements = true, bool $projectChildren = true): array
    {
        if ($nodes === []) {
            return $nodes;
        }

        if (!$query->shouldWireNodeHierarchy()) {
            if ($includeLinkedElements) {
                $this->eagerLoadLinkedElements($nodes);
            }

            return $nodes;
        }

        $sourceNodes = $this->_resolveHierarchySourceNodes($query, $nodes);
        $wiredNodes = $this->assembleNodeHierarchy($sourceNodes, $includeLinkedElements, $projectChildren);

        if ($sourceNodes !== $nodes) {
            $wiredNodesById = ArrayHelper::index(
                array_filter($wiredNodes, static fn(mixed $node): bool => $node instanceof NodeElement),
                'id',
            );

            foreach ($nodes as $index => $node) {
                if (isset($wiredNodesById[$node->id])) {
                    $nodes[$index] = $wiredNodesById[$node->id];
                }
            }

            return $nodes;
        }

        return $wiredNodes;
    }

    /**
     * Wires parent/child relationships in memory after a single flat node query.
     *
     * Used by render() so Twig can read node.children without per-node structure queries.
     */
    public function assembleNodeHierarchy(array $nodes, bool $includeLinkedElements = true, bool $projectChildren = true): array
    {
        if ($includeLinkedElements) {
            $this->eagerLoadLinkedElements($nodes);
        }

        $nodeStack = [];
        $childrenByParentId = [];
        $childrenPlan = new EagerLoadPlan(['handle' => 'children', 'alias' => 'children']);
        $nodesById = [];

        foreach ($nodes as $node) {
            if ($node instanceof NodeElement) {
                $nodesById[$node->id] = $node;
            }
        }

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $level = max(1, (int)$node->level);

            if ($level === 1) {
                $node->setParent(null);
            } else if (isset($nodeStack[$level - 1])) {
                $parent = $nodeStack[$level - 1];
                $node->setParent($parent);
                $childrenByParentId[$parent->id][] = $node;
            } else {
                $parent = $node->getParent();

                if (
                    $parent instanceof NodeElement
                    && (int)$parent->siteId === (int)$node->siteId
                    && isset($nodesById[$parent->id])
                ) {
                    $node->setParent($parent);
                    $childrenByParentId[$parent->id][] = $node;
                    $level = (int)$parent->level + 1;
                } else {
                    $node->setParent(null);
                    $level = 1;
                }
            }

            $nodeStack[$level] = $node;

            foreach (array_keys($nodeStack) as $stackLevel) {
                if ($stackLevel > $level) {
                    unset($nodeStack[$stackLevel]);
                }
            }
        }

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $node->setEagerLoadedElements('children', $childrenByParentId[$node->id] ?? [], $childrenPlan);
        }

        if (!$projectChildren) {
            return $nodes;
        }

        $this->projectDynamicChildren($nodes);

        return $this->injectProjectedNodesIntoFlatList($nodes);
    }

    /**
     * Inserts projected nodes into the flat query list so Craft's {% nav %} tag can render them.
     *
     * Stored descendants remain in structure order; projected nodes are appended after each parent subtree.
     */
    public function injectProjectedNodesIntoFlatList(array $nodes): array
    {
        if ($nodes === []) {
            return $nodes;
        }

        $output = [];
        $count = count($nodes);
        $index = 0;

        while ($index < $count) {
            $node = $nodes[$index];
            $output[] = $node;

            if (!$node instanceof NodeElement) {
                $index++;
                continue;
            }

            $nextIndex = $index + 1;

            while (
                $nextIndex < $count
                && $nodes[$nextIndex] instanceof NodeElement
                && (int)$nodes[$nextIndex]->level > (int)$node->level
            ) {
                $output[] = $nodes[$nextIndex];
                $nextIndex++;
            }

            $projectedChildren = $this->_projectedChildrenForNode($node);

            if ($projectedChildren !== []) {
                $node->rgt = (int)$node->lft + 1 + (count($projectedChildren) * 2);

                foreach ($projectedChildren as $childIndex => $projectedNode) {
                    $projectedNode->lft = (int)$node->lft + 1 + ($childIndex * 2);
                    $projectedNode->rgt = $projectedNode->lft + 1;
                    $projectedNode->level = (int)$node->level + 1;
                    $output[] = $projectedNode;
                }
            }

            $index = $nextIndex;
        }

        return $output;
    }

    /**
     * Appends read-time projected children for Dynamic nodes (stored children first).
     */
    public function projectDynamicChildren(array $nodes, ?int $siteId = null): void
    {
        $siteId ??= Craft::$app->getSites()->getCurrentSite()->id;

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $nodeType = $node->nodeType();

            if (!$nodeType instanceof ProjectingNodeType) {
                continue;
            }

            $storedChildren = $node->getChildren()->all();
            $projectedChildren = $nodeType->getProjectedChildren($node, $siteId);
            $merged = array_merge($storedChildren, $projectedChildren);

            $childrenPlan = new EagerLoadPlan(['handle' => 'children', 'alias' => 'children']);
            $node->setEagerLoadedElements('children', $merged, $childrenPlan);
        }
    }

    public function buildNodeTree(array $nodes, bool $includeLinkedElements = false, bool $fromHierarchy = false): array
    {
        if ($includeLinkedElements) {
            $this->eagerLoadLinkedElements($nodes);
        }

        if ($fromHierarchy) {
            $nodeTree = [];

            foreach ($nodes as $node) {
                if ($node instanceof NodeElement && max(1, (int)$node->level) === 1) {
                    $nodeTree[] = $this->_nodeToTreeArray($node, $includeLinkedElements);
                }
            }

            return $nodeTree;
        }

        $nodeTree = [];
        $nodeStack = [];
        $treeStack = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $level = max(1, (int)$node->level);

            if ($level === 1) {
                $node->setParent(null);
                $nodeTree[] = $this->_nodeToTreeArray($node, $includeLinkedElements);
                $index = array_key_last($nodeTree);
                $treeStack = [1 => &$nodeTree[$index]];
            } else if (isset($nodeStack[$level - 1], $treeStack[$level - 1])) {
                $node->setParent($nodeStack[$level - 1]);
                $treeStack[$level - 1]['children'][] = $this->_nodeToTreeArray($node, $includeLinkedElements);
                $index = array_key_last($treeStack[$level - 1]['children']);
                $treeStack[$level] = &$treeStack[$level - 1]['children'][$index];
            } else {
                $node->setParent(null);
                $nodeTree[] = $this->_nodeToTreeArray($node, $includeLinkedElements);
                $index = array_key_last($nodeTree);
                $treeStack = [1 => &$nodeTree[$index]];
                $level = 1;
            }

            $nodeStack[$level] = $node;

            foreach (array_keys($nodeStack) as $stackLevel) {
                if ($stackLevel > $level) {
                    unset($nodeStack[$stackLevel], $treeStack[$stackLevel]);
                }
            }
        }

        return $nodeTree;
    }


    // Private Methods
    // =========================================================================

    private function _projectedChildrenForNode(NodeElement $node): array
    {
        $children = $node->getEagerLoadedElements('children');

        if ($children === null) {
            return [];
        }

        $items = is_array($children) ? $children : $children->all();

        return array_values(array_filter(
            $items,
            static fn(mixed $child): bool => $child instanceof ProjectedNode,
        ));
    }

    private function _nodeToTreeArray(NodeElement $node, bool $includeLinkedElements = false): array
    {
        if ($includeLinkedElements && $node->elementId && $node->isElement() && !$node->getElement()) {
            $this->eagerLoadLinkedElements([$node]);
        }

        $nodeArray = $node->toArray();
        $nodeArray['active'] = $node->getActive();
        $nodeArray['current'] = $node->getCurrent();
        $nodeArray['hasActiveChild'] = $node->hasActiveChild();
        $nodeArray['target'] = $node->getTarget();
        $nodeArray['isProjected'] = false;
        // Keep tree() aligned with nodes(): linked elements are omitted unless the caller opted in.
        $nodeArray['element'] = ($includeLinkedElements && $node->getElement())
            ? $node->getElement()->toArray()
            : null;

        $children = $node->getChildren()->all();

        if ($children) {
            $nodeArray['children'] = array_map(function($child) use ($includeLinkedElements) {
                if ($child instanceof ProjectedNode) {
                    return $this->_projectedNodeToTreeArray($child);
                }

                if ($child instanceof NodeElement) {
                    return $this->_nodeToTreeArray($child, $includeLinkedElements);
                }

                return $child;
            }, $children);
        }

        return $nodeArray;
    }

    private function _projectedNodeToTreeArray(ProjectedNode $node): array
    {
        return [
            'id' => $node->id,
            'title' => $node->title,
            'url' => $node->getUrl(),
            'level' => $node->level,
            'isProjected' => true,
            'active' => $node->getActive(),
            'current' => $node->getCurrent(),
            'hasActiveChild' => $node->hasActiveChild(),
            'element' => $node->getElement() ? $node->getElement()->toArray() : null,
            'children' => array_map(
                fn(ProjectedNode $child): array => $this->_projectedNodeToTreeArray($child),
                $node->getChildren(),
            ),
        ];
    }

    private function _resolveHierarchySourceNodes(NodeQuery $query, array $resultNodes): array
    {
        if (!$query->menuId && !$query->handle) {
            return $resultNodes;
        }

        if ($this->_queryNeedsFullNavHierarchy($query)) {
            return $this->_fetchNavNodesForHierarchy($query);
        }

        return $resultNodes;
    }

    private function _queryNeedsFullNavHierarchy(NodeQuery $query): bool
    {
        return $query->level !== null
            || $query->id !== null
            || $query->limit !== null
            || ($query->offset ?? 0) > 0;
    }

    private function _fetchNavNodesForHierarchy(NodeQuery $query): array
    {
        $navQuery = NodeElement::find();
        $navQuery->internalReadFetch = true;
        $navQuery->bypassReadCache = true;
        $navQuery->skipPostCacheProcessing = true;
        $navQuery->withNodeHierarchy(false);

        if (isset($query->status)) {
            $navQuery->status($query->status);
        }

        if ($query->siteId !== null) {
            $navQuery->siteId($query->siteId);
        }

        if ($query->menuId) {
            $navQuery->menuId($query->menuId);
        }

        if ($query->handle) {
            $navQuery->menuHandle($query->handle);
        }

        return $navQuery->all();
    }
}
