<?php
namespace verbb\navigation\services;

use verbb\navigation\deprecations\ActiveMatcherDeprecations;
use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\NodeActiveState;
use verbb\navigation\models\ProjectedNode;

use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;

class ActiveMatcher extends Component
{
    // Constants
    // =========================================================================

    private const MATCH_NONE = 0;
    private const MATCH_PATH = 1;
    private const MATCH_EXACT = 2;


    // Traits
    // =========================================================================

    use ActiveMatcherDeprecations;


    // Public Methods
    // =========================================================================

    /**
     * Resolves current/active/hasActiveChild flags for all nodes in one pass.
     */
    public function resolve(array $nodes): void
    {
        if ($this->_shouldSkipActiveResolution()) {
            foreach ($nodes as $node) {
                if ($node instanceof NodeElement) {
                    $node->clearActiveState();
                }
            }

            return;
        }

        $childrenByParentKey = $this->_buildChildrenMap($nodes);
        $statesByKey = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $statesByKey[$this->_nodeSiteKey($node)] = new NodeActiveState([
                'isCurrent' => $this->_matchType($node) === self::MATCH_EXACT,
            ]);
        }

        $levels = array_unique(array_map(
            static fn(NodeElement $node): int => max(1, (int)$node->level),
            array_filter($nodes, static fn($node): bool => $node instanceof NodeElement),
        ));
        rsort($levels);

        foreach ($levels as $level) {
            foreach ($nodes as $node) {
                if (!$node instanceof NodeElement || (int)$node->level !== $level) {
                    continue;
                }

                $key = $this->_nodeSiteKey($node);
                $state = $statesByKey[$key];
                $children = $childrenByParentKey[$key] ?? [];

                foreach ($children as $child) {
                    $childState = $statesByKey[$this->_nodeSiteKey($child)] ?? null;

                    if ($childState && ($childState->isCurrent || $childState->hasActiveChild)) {
                        $state->hasActiveChild = true;
                        break;
                    }
                }

                $matchType = $this->_matchType($node);
                $state->isActive = $state->isCurrent
                    || $state->hasActiveChild
                    || $matchType === self::MATCH_PATH;
            }
        }

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $node->setActiveState($statesByKey[$this->_nodeSiteKey($node)] ?? new NodeActiveState());
        }
    }

    /**
     * Returns the deepest exact-match (current) node, which may be a stored node or projected child.
     */
    public function findActiveNode(array $nodes, bool $includeChildren = false): NodeElement|ProjectedNode|null
    {
        if ($includeChildren) {
            foreach ($this->findCurrentNodes($nodes) as $node) {
                return $node;
            }

            $pathMatches = [];

            foreach ($nodes as $node) {
                if ($node instanceof NodeElement && $this->_matchType($node) === self::MATCH_PATH) {
                    $pathMatches[] = $node;
                } elseif ($node instanceof ProjectedNode && $this->_matchProjectedType($node) === self::MATCH_PATH) {
                    $pathMatches[] = $node;
                }
            }

            return $this->_sortByLevelDesc($pathMatches)[0] ?? null;
        }

        return $this->findCurrentNodes($nodes)[0] ?? null;
    }

    /**
     * Returns all exact-match (current) nodes, deepest first.
     */
    public function findCurrentNodes(array $nodes): array
    {
        $currentNodes = [];

        foreach ($nodes as $node) {
            if ($node instanceof NodeElement && $this->isCurrent($node)) {
                $currentNodes[] = $node;
            } elseif ($node instanceof ProjectedNode && $this->isProjectedCurrent($node)) {
                $currentNodes[] = $node;
            }
        }

        return $this->_sortByLevelDesc($currentNodes);
    }

    /**
     * Returns all branch-active nodes (current, path, or has active descendant).
     */
    public function findActiveNodes(array $nodes): array
    {
        $activeNodes = [];

        foreach ($nodes as $node) {
            if ($node instanceof NodeElement && $this->isActive($node)) {
                $activeNodes[] = $node;
            } elseif ($node instanceof ProjectedNode && $this->isProjectedActive($node)) {
                $activeNodes[] = $node;
            }
        }

        return $activeNodes;
    }

    public function isCurrent(NodeElement $node): bool
    {
        if ($node->hasResolvedActiveState()) {
            return $node->getActiveState()->isCurrent;
        }

        return $this->_matchType($node) === self::MATCH_EXACT;
    }

    public function isActive(NodeElement $node): bool
    {
        if ($node->hasResolvedActiveState()) {
            return $node->getActiveState()->isActive;
        }

        return $this->_matchType($node) !== self::MATCH_NONE;
    }

    public function hasActiveChild(NodeElement $node): bool
    {
        if ($node->hasResolvedActiveState()) {
            return $node->getActiveState()->hasActiveChild;
        }

        return false;
    }

    public function normalizeCurrentUrl(): string
    {
        $request = Craft::$app->getRequest();
        $pageTrigger = Craft::$app->getConfig()->getGeneral()->getPageTrigger();
        $currentUrl = trim(urldecode($request->getAbsoluteUrl()), '/');
        $currentUrl = preg_replace('/\?.*/', '', $currentUrl);
        $currentUrl = strtolower($currentUrl);

        if (!str_starts_with($pageTrigger, '?')) {
            $pageTrigger = preg_quote($pageTrigger, '/');

            if (preg_match("/^(?:(.*)\/)?$pageTrigger(\d+)$/", $currentUrl, $match)) {
                $currentUrl = $match[1];
            }
        }

        return $currentUrl;
    }

    /**
     * Resolves active/current state for projected Dynamic children.
     */
    public function resolveProjections(array $nodes): void
    {
        if ($this->_shouldSkipActiveResolution()) {
            return;
        }

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $this->_resolveProjectionsForNode($node);
        }
    }

    public function isProjectedCurrent(ProjectedNode $node): bool
    {
        return $this->_matchProjectedType($node) === self::MATCH_EXACT;
    }

    public function isProjectedActive(ProjectedNode $node): bool
    {
        return $this->_matchProjectedType($node) !== self::MATCH_NONE;
    }

    public function findDeepestMatchInChildren(NodeElement $node): mixed
    {
        $deepest = null;
        $deepestLevel = 0;

        foreach ($node->getChildren()->all() as $child) {
            if ($child instanceof NodeElement) {
                if ($this->isCurrent($child) && (int)$child->level >= $deepestLevel) {
                    $deepest = $child;
                    $deepestLevel = (int)$child->level;
                }

                $nested = $this->findDeepestMatchInChildren($child);

                if ($nested && (int)($nested->level ?? 0) >= $deepestLevel) {
                    $deepest = $nested;
                    $deepestLevel = (int)($nested->level ?? 0);
                }
            } elseif ($child instanceof ProjectedNode) {
                if ($this->isProjectedCurrent($child) && $child->level >= $deepestLevel) {
                    $deepest = $child;
                    $deepestLevel = $child->level;
                }
            }
        }

        return $deepest;
    }


    // Private Methods
    // =========================================================================

    private function _matchType(NodeElement $node): int
    {
        $request = Craft::$app->getRequest();

        if ($this->_shouldSkipActiveResolution()) {
            return self::MATCH_NONE;
        }

        $siteUrl = trim(UrlHelper::siteUrl('', null, null, $node->siteId), '/');
        $nodeUrl = $this->_resolveMatchUrl($node, $request);

        if ($nodeUrl === null) {
            return self::MATCH_NONE;
        }

        $currentUrl = $this->normalizeCurrentUrl();

        if ($node->getRawElementUrl() === '__home__') {
            return $currentUrl === $nodeUrl ? self::MATCH_EXACT : self::MATCH_NONE;
        }

        if ($currentUrl === $nodeUrl) {
            return self::MATCH_EXACT;
        }

        if ($this->_matchesPathPrefix($node, $currentUrl, $nodeUrl, $siteUrl)) {
            return self::MATCH_PATH;
        }

        return self::MATCH_NONE;
    }

    private function _resolveMatchUrl(NodeElement $node, mixed $request): ?string
    {
        $rawElementUrl = $node->getRawElementUrl();

        if ($rawElementUrl !== null) {
            if ($rawElementUrl === '__home__') {
                $sitePath = parse_url(UrlHelper::siteUrl('', null, null, $node->siteId), PHP_URL_PATH) ?: '';
                $nodeUrl = trim(strtolower(rtrim($request->hostInfo, '/') . trim($sitePath, '/')), '/');
            } else {
                $nodeUrl = $this->_normalizeNodeUrl($node, '/' . ltrim($rawElementUrl, '/'), $request);
            }

            if ($node->urlSuffix) {
                $nodeUrl .= strtolower($node->urlSuffix);
            }

            return $nodeUrl;
        }

        $nodeUrl = (string)$node->getUrl(true);

        if ($nodeUrl === '' && !$node->isCustom() && !$node->isSite()) {
            return null;
        }

        return $this->_normalizeNodeUrl($node, $nodeUrl, $request);
    }

    private function _matchesPathPrefix(NodeElement $node, string $currentUrl, string $nodeUrl, string $siteUrl): bool
    {
        if ($nodeUrl === '' || !str_starts_with($currentUrl, $nodeUrl . '/')) {
            return false;
        }

        if ($node->isSite()) {
            return true;
        }

        return $nodeUrl !== $siteUrl;
    }

    private function _normalizeNodeUrl(NodeElement $node, string $nodeUrl, mixed $request): string
    {
        $nodeUrl = strtolower($nodeUrl);

        if (UrlHelper::isRootRelativeUrl($nodeUrl)) {
            $nodeUrl = $request->hostInfo . '/' . trim($nodeUrl, '/');
        }

        if (!UrlHelper::isAbsoluteUrl($nodeUrl)) {
            $nodeUrl = UrlHelper::siteUrl($nodeUrl, null, null, $node->siteId);
        }

        return trim($nodeUrl, '/');
    }

    private function _buildChildrenMap(array $nodes): array
    {
        $childrenByParentKey = [];
        $stack = [];

        foreach ($nodes as $node) {
            if (!$node instanceof NodeElement) {
                continue;
            }

            $level = max(1, (int)$node->level);

            foreach (array_keys($stack) as $stackLevel) {
                if ($stackLevel >= $level) {
                    unset($stack[$stackLevel]);
                }
            }

            if ($level > 1 && isset($stack[$level - 1])) {
                $parent = $stack[$level - 1];
                // Only wire same-site parents — site('*') batches must not cross locales.
                if ((int)$parent->siteId === (int)$node->siteId) {
                    $childrenByParentKey[$this->_nodeSiteKey($parent)][] = $node;
                }
            }

            $stack[$level] = $node;
        }

        return $childrenByParentKey;
    }

    /**
     * Composite identity for multi-site node lists (same pattern as NodeQuery::populate).
     */
    private function _nodeSiteKey(NodeElement $node): string
    {
        return $node->id . ':' . (int)$node->siteId;
    }

    private function _sortByLevelDesc(array $nodes): array
    {
        ArrayHelper::multisort($nodes, fn(mixed $node): int => (int)($node->level ?? 0), SORT_DESC);

        return $nodes;
    }

    private function _resolveProjectionsForNode(NodeElement $node): void
    {
        $hasActiveChild = false;

        foreach ($node->getChildren()->all() as $child) {
            if ($child instanceof ProjectedNode) {
                $matchType = $this->_matchProjectedType($child);
                $isCurrent = $matchType === self::MATCH_EXACT;
                $isActive = $matchType !== self::MATCH_NONE;
                $child->setActiveState($isCurrent, $isActive);

                if ($isCurrent || $isActive) {
                    $hasActiveChild = true;
                }
            } elseif ($child instanceof NodeElement) {
                if ($child->hasResolvedActiveState() && ($child->getActiveState()->isCurrent || $child->getActiveState()->isActive)) {
                    $hasActiveChild = true;
                }

                $this->_resolveProjectionsForNode($child);
            }
        }

        if ($hasActiveChild && $node->hasResolvedActiveState()) {
            $state = $node->getActiveState();
            $state->hasActiveChild = true;
            $state->isActive = $state->isActive || $state->hasActiveChild;
        }
    }

    private function _matchProjectedType(ProjectedNode $node): int
    {
        if ($this->_shouldSkipActiveResolution()) {
            return self::MATCH_NONE;
        }

        $nodeUrl = $node->getUrl();

        if (!$nodeUrl) {
            return self::MATCH_NONE;
        }

        $siteUrl = trim(UrlHelper::siteUrl('', null, null, $node->siteId), '/');
        $currentUrl = $this->normalizeCurrentUrl();
        $nodeUrl = trim(strtolower($nodeUrl), '/');

        if ($node->uri === '__home__') {
            return $currentUrl === $nodeUrl ? self::MATCH_EXACT : self::MATCH_NONE;
        }

        if ($currentUrl === $nodeUrl) {
            return self::MATCH_EXACT;
        }

        if (str_starts_with($currentUrl, rtrim($nodeUrl, '/') . '/')) {
            return self::MATCH_PATH;
        }

        return self::MATCH_NONE;
    }

    private function _shouldSkipActiveResolution(): bool
    {
        $request = Craft::$app->getRequest();

        return $request->getIsConsoleRequest()
            || $request->getIsCpRequest()
            || $request->getIsPreview();
    }
}
