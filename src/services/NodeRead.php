<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\base\ElementNodeType;
use verbb\navigation\base\ProjectingNodeType;
use verbb\navigation\elements\db\NodeQuery;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\helpers\DynamicProjectionSettings;
use verbb\navigation\helpers\NodeHierarchy;
use verbb\navigation\models\ProjectedNode;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\db\QueryAbortedException;
use craft\elements\db\EagerLoadPlan;

use WeakMap;

class NodeRead extends Component
{
    // Properties
    // =========================================================================

    private ?WeakMap $_deferredLinkedElements = null;
    private ?WeakMap $_loadingLinkedElementBatches = null;


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

        $fetchQuery = clone $query;
        $fetchQuery->bypassReadCache = true;
        $fetchQuery->skipPostCacheProcessing = true;
        $nodes = $fetchQuery->all();

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

    /**
     * Shares a lazy batch without retaining query results or serializing request state with nodes.
     * URL generation still runs on real elements so instance behaviors, events and previews apply.
     */
    public function deferLinkedElements(array $nodes): void
    {
        $this->_deferredLinkedElements ??= new WeakMap();
        $batch = new WeakMap();

        foreach ($nodes as $node) {
            if ($node instanceof NodeElement && $node->elementId && !$node->hasResolvedElement()) {
                $batch[$node] = true;
                $this->_deferredLinkedElements[$node] = $batch;
            }
        }
    }

    public function loadDeferredLinkedElements(NodeElement $node): bool
    {
        $batch = $this->_deferredLinkedElements[$node] ?? null;

        if ($batch === null || isset($this->_loadingLinkedElementBatches[$batch])) {
            return false;
        }

        $this->_loadingLinkedElementBatches ??= new WeakMap();
        $this->_loadingLinkedElementBatches[$batch] = true;
        $nodes = [];

        foreach ($batch as $member => $unused) {
            // Explicitly assigned elements and nodes moved into another batch keep their state.
            if (($this->_deferredLinkedElements[$member] ?? null) === $batch) {
                $nodes[] = $member;
            }
        }

        try {
            // Keep ownership until assignment so callbacks can cancel or replace pending work.
            // Reentrant reads use the individual lookup instead of starting this batch again.
            $this->_loadLinkedElements($nodes, $batch);
        } finally {
            foreach ($batch as $member => $unused) {
                if (($this->_deferredLinkedElements[$member] ?? null) === $batch) {
                    unset($this->_deferredLinkedElements[$member]);
                }
            }

            unset($this->_loadingLinkedElementBatches[$batch]);
        }

        return true;
    }

    public function forgetDeferredLinkedElement(NodeElement $node): void
    {
        if (isset($this->_deferredLinkedElements[$node])) {
            unset($this->_deferredLinkedElements[$node]);
        }
    }

    public function eagerLoadLinkedElements(array $nodes): array
    {
        return $this->_loadLinkedElements($nodes);
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

        $childrenByParentKey = NodeHierarchy::childrenByParent($nodes);
        $childrenPlan = new EagerLoadPlan(['handle' => 'children', 'alias' => 'children']);

        foreach ($nodes as $node) {
            // An omitted ancestor remains a stored parent, not a requested move to root.
            if ($node instanceof NodeElement && (int)$node->level === 1) {
                $node->setParent(null);
            }
        }

        foreach ($nodes as $node) {
            if ($node instanceof NodeElement) {
                foreach ($childrenByParentKey[$this->_nodeSiteKey($node)] ?? [] as $child) {
                    $child->setParent($node);
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
     * Stored descendants remain in structure order; each Dynamic node controls whether
     * its projected children appear before or after its stored child subtrees.
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
     * Appends read-time projected children for Dynamic nodes in their configured position.
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
        if (!$fromHierarchy) {
            $nodes = $this->assembleNodeHierarchy($nodes, $includeLinkedElements, false);
        } elseif ($includeLinkedElements) {
            $this->eagerLoadLinkedElements($nodes);
        }

        $nestedKeys = [];

        foreach (NodeHierarchy::childrenByParent($nodes) as $children) {
            foreach ($children as $child) {
                $nestedKeys[$this->_nodeSiteKey($child)] = true;
            }
        }

        $nodeTree = [];

        foreach ($nodes as $node) {
            // Filtered results form a forest without changing stored levels or parents.
            if ($node instanceof NodeElement && !isset($nestedKeys[$this->_nodeSiteKey($node)])) {
                $nodeTree[] = $this->_nodeToTreeArray($node, $includeLinkedElements);
            }
        }

        return $nodeTree;
    }


    // Private Methods
    // =========================================================================

    private function _loadLinkedElements(array $nodes, ?WeakMap $batch = null): array
    {
        $nodeGroups = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement || !$node->elementId || !$node->isElement()) {
                continue;
            }

            $nodeType = $node->nodeType();

            if (!$nodeType instanceof ElementNodeType) {
                continue;
            }

            $elementType = $nodeType::getElementType();

            $siteId = $node->getElementSiteId();
            $identity = [$node->type, $node->elementId, $siteId];

            if (!$this->_canApplyLinkedElement($node, $identity, $batch)) {
                continue;
            }

            // Match getElementById() when an optional element provider is unavailable.
            if (!class_exists($elementType)) {
                $node->setElement(null);
                continue;
            }

            $groupKey = $elementType . ':' . ($siteId ?? '*');
            $nodeGroups[$groupKey]['type'] = $elementType;
            $nodeGroups[$groupKey]['siteId'] = $siteId;
            $nodeGroups[$groupKey]['elementIds'][$node->elementId] = $node->elementId;
            $nodeGroups[$groupKey]['nodes'][] = [$node, $identity];
        }

        foreach ($nodeGroups as $nodeGroup) {
            $elementType = $nodeGroup['type'];
            $query = Craft::$app->getElements()
                ->createElementQuery($elementType)
                ->siteId($nodeGroup['siteId'])
                ->status(null)
                ->drafts(null)
                ->provisionalDrafts(null)
                ->revisions(null)
                ->id(array_values($nodeGroup['elementIds']));

            // Cached provider results are already hydrated and must retain their instances.
            if ($batch !== null && $query->getCachedResult() === null) {
                // Share database rows, not mutable element instances. Re-populating each row
                // preserves initialization/population handlers, behaviors, fields and placeholders;
                // cloning elements would strip their instance events and behaviors.
                $rowsById = [];
                $rows = [];

                if (!$query->emulateExecution) {
                    try {
                        // Prepare normally, preserving indexes set by query hooks. Fetch raw
                        // rows without invoking indexing until real elements are populated.
                        $rows = $query->createCommand()->queryAll();
                    } catch (QueryAbortedException) {
                        // Match Craft's all()/one() behavior for cancelled or impossible queries.
                    }
                }

                foreach ($rows as $row) {
                    $rowsById[$row['id']] = $row;
                }

                foreach ($nodeGroup['nodes'] as [$node, $identity]) {
                    if (!$this->_canApplyLinkedElement($node, $identity, $batch)) {
                        continue;
                    }

                    $row = $rowsById[$identity[1]] ?? null;
                    $elements = $query->populate($row ? [$row] : []);

                    // Hydration callbacks may explicitly assign an element or change the link.
                    if ($this->_canApplyLinkedElement($node, $identity, $batch)) {
                        $node->setElement(reset($elements) ?: null);
                    }
                }

                continue;
            }

            $elements = $query->all();

            $elementsById = [];
            $cachedElementsByCanonicalId = [];

            foreach ($elements as $element) {
                $elementsById[$element->id] = $element;

                if ($batch !== null) {
                    // A provider may supply a draft in place of the selected canonical element.
                    $cachedElementsByCanonicalId[$element->getCanonicalId()] ??= $element;
                }
            }

            foreach ($nodeGroup['nodes'] as [$node, $identity]) {
                if ($this->_canApplyLinkedElement($node, $identity, $batch)) {
                    $node->setElement($elementsById[$node->elementId] ?? $cachedElementsByCanonicalId[$node->elementId] ?? null);
                }
            }
        }

        return $nodes;
    }

    private function _canApplyLinkedElement(NodeElement $node, array $identity, ?WeakMap $batch): bool
    {
        return $batch === null || (
            ($this->_deferredLinkedElements[$node] ?? null) === $batch
            && $identity === [$node->type, $node->elementId, $node->getElementSiteId()]
        );
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
        $projectedChildren = $this->_projectedChildrenForNode($node);
        $projectedChildrenFirst = DynamicProjectionSettings::projectedChildrenFirst($node);

        if ($projectedChildrenFirst) {
            $this->_appendProjectedChildren($node, $projectedChildren, $level, $output);
        }

        while (
            $childIndex < $count
            && $nodes[$childIndex] instanceof NodeElement
            && $nodes[$childIndex]->structureId === $node->structureId
            && $nodes[$childIndex]->root === $node->root
            && $nodes[$childIndex]->siteId === $node->siteId
            && $nodes[$childIndex]->lft > $node->lft
            && $nodes[$childIndex]->rgt < $node->rgt
        ) {
            // Caller order can interleave branches or omit intermediate ancestors.
            $childIndex = $this->_appendStoredNodeWithProjections($nodes, $childIndex, $count, $output);
        }

        if (!$projectedChildrenFirst) {
            $this->_appendProjectedChildren($node, $projectedChildren, $level, $output);
        }

        return $childIndex;
    }

    private function _appendProjectedChildren(NodeElement $node, array $children, int $level, array &$output): void
    {
        // Craft's nav tag checks parent bounds, but projections have no stored positions.
        // Keep them inside the parent; list order and level determine their render order.
        foreach ($children as $projectedNode) {
            $projectedNode->lft = (int)$node->lft + 1;
            $projectedNode->rgt = (int)$node->rgt;
            $projectedNode->level = $level + 1;
            $output[] = $projectedNode;
        }
    }

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

        return method_exists($request, 'getIsPreview')
            && $request->getIsPreview()
            && $request->getHadToken()
            && $request->getToken() !== null;
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
                    return $this->_projectedNodeToTreeArray($child, $includeLinkedElements);
                }

                if ($child instanceof NodeElement) {
                    return $this->_nodeToTreeArray($child, $includeLinkedElements);
                }

                return $child;
            }, $children);
        }

        return $nodeArray;
    }

    private function _projectedNodeToTreeArray(ProjectedNode $node, bool $includeLinkedElements = false): array
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
            'element' => $includeLinkedElements && $node->getElement() ? $node->getElement()->toArray() : null,
            'children' => array_map(
                fn(ProjectedNode $child): array => $this->_projectedNodeToTreeArray($child, $includeLinkedElements),
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
        // Cache the canonical flat menu read so repeated subset queries can rebuild
        // their hierarchy without querying the complete menu again.
        $navQuery->withNodeHierarchy(false);
        $navQuery->withProjectedChildren(false);
        $navQuery->withNavigationCache($query->useNavigationCache);

        $navQuery->status($query->status);

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
