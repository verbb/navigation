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
        // Carry projection preview flag from the originating query into DynamicSources.
        $dynamicSources = Navigation::$plugin->getDynamicSources();
        $previousPending = $dynamicSources->shouldIncludePendingProjections();
        $wantPending = $query->includePendingProjections || $this->_requestWantsPendingProjections();

        if ($wantPending) {
            $dynamicSources->includePendingProjections(true);
        }

        try {
            $wiredNodes = $this->assembleNodeHierarchy($sourceNodes, $includeLinkedElements, $projectChildren);
        } finally {
            $dynamicSources->includePendingProjections($previousPending);
        }

        if ($sourceNodes !== $nodes) {
            $wiredNodesByKey = [];

            foreach ($wiredNodes as $wiredNode) {
                if ($wiredNode instanceof NodeElement) {
                    $wiredNodesByKey[$this->_nodeSiteKey($wiredNode)] = $wiredNode;
                }
            }

            foreach ($nodes as $index => $node) {
                if ($node instanceof NodeElement && isset($wiredNodesByKey[$this->_nodeSiteKey($node)])) {
                    $nodes[$index] = $wiredNodesByKey[$this->_nodeSiteKey($node)];
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
        $childrenByParentKey = [];
        $childrenPlan = new EagerLoadPlan(['handle' => 'children', 'alias' => 'children']);
        $nodesByKey = [];

        foreach ($nodes as $node) {
            if ($node instanceof NodeElement) {
                $nodesByKey[$this->_nodeSiteKey($node)] = $node;
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

                if ((int)$parent->siteId === (int)$node->siteId) {
                    $node->setParent($parent);
                    $childrenByParentKey[$this->_nodeSiteKey($parent)][] = $node;
                } else {
                    $node->setParent(null);
                    $level = 1;
                }
            } else {
                $parent = $node->getParent();

                if (
                    $parent instanceof NodeElement
                    && (int)$parent->siteId === (int)$node->siteId
                    && isset($nodesByKey[$this->_nodeSiteKey($parent)])
                ) {
                    $node->setParent($parent);
                    $childrenByParentKey[$this->_nodeSiteKey($parent)][] = $node;
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

            $node->setEagerLoadedElements('children', $childrenByParentKey[$this->_nodeSiteKey($node)] ?? [], $childrenPlan);
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

        // Recurse so nested Dynamic parents inside a subtree also receive projections
        // (the previous flat jump skipped them).
        while ($index < $count) {
            $index = $this->_appendStoredNodeWithProjections($nodes, $index, $count, $output);
        }

        return $output;
    }

    /**
     * Appends one stored node, its stored descendants (recursively), then its projections.
     */
    private function _appendStoredNodeWithProjections(array $nodes, int $index, int $count, array &$output): int
    {
        $node = $nodes[$index];
        $output[] = $node;

        if (!$node instanceof NodeElement) {
            return $index + 1;
        }

        $level = (int)$node->level;
        $childIndex = $index + 1;

        while (
            $childIndex < $count
            && $nodes[$childIndex] instanceof NodeElement
            && (int)$nodes[$childIndex]->level > $level
        ) {
            if ((int)$nodes[$childIndex]->level === $level + 1) {
                $childIndex = $this->_appendStoredNodeWithProjections($nodes, $childIndex, $count, $output);
            } else {
                // Malformed flat order — keep moving to avoid an infinite loop.
                $childIndex++;
            }
        }

        $projectedChildren = $this->_projectedChildrenForNode($node);

        if ($projectedChildren !== []) {
            $node->rgt = (int)$node->lft + 1 + (count($projectedChildren) * 2);

            foreach ($projectedChildren as $childIdx => $projectedNode) {
                $projectedNode->lft = (int)$node->lft + 1 + ($childIdx * 2);
                $projectedNode->rgt = $projectedNode->lft + 1;
                $projectedNode->level = $level + 1;
                $output[] = $projectedNode;
            }
        }

        return $childIndex;
    }

    /**
     * Appends read-time projected children for Dynamic nodes (stored children first).
     *
     * Projections stay on Node::setProjectedChildren() — never in Craft's eager-loaded
     * ElementCollection — so Element::getEagerLoadedElements() does not run setNextPrev
     * across Node ↔ ProjectedNode (typed ElementInterface|false).
     */
    public function projectDynamicChildren(array $nodes, ?int $siteId = null): void
    {
        $siteId ??= Craft::$app->getSites()->getCurrentSite()->id;
        $dynamicSources = Navigation::$plugin->getDynamicSources();
        $restorePending = $dynamicSources->shouldIncludePendingProjections();

        if (!$restorePending && $this->_requestWantsPendingProjections()) {
            $dynamicSources->includePendingProjections(true);
        }

        try {
            foreach ($nodes as $node) {
                if (!$node instanceof NodeElement) {
                    continue;
                }

                $nodeType = $node->nodeType();

                if (!$nodeType instanceof ProjectingNodeType) {
                    continue;
                }

                // Prefer each node's own site when the list is multi-site.
                $nodeSiteId = (int)($node->siteId ?? $siteId);
                $node->setProjectedChildren($nodeType->getProjectedChildren($node, $nodeSiteId));
            }
        } finally {
            if (!$restorePending) {
                $dynamicSources->includePendingProjections(false);
            }
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
        return $node->getProjectedChildren();
    }

    private function _nodeSiteKey(NodeElement $node): string
    {
        return $node->id . ':' . (int)$node->siteId;
    }

    /**
     * Live Preview / tokenized preview may include pending projected sources.
     * Public front-end requests must not.
     */
    private function _requestWantsPendingProjections(): bool
    {
        $request = Craft::$app->getRequest();

        if ($request->getIsConsoleRequest() || $request->getIsCpRequest()) {
            return false;
        }

        return method_exists($request, 'getIsPreview') && $request->getIsPreview();
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
