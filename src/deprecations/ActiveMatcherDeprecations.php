<?php
namespace verbb\navigation\deprecations;

use verbb\navigation\elements\Node as NodeElement;

use Craft;

trait ActiveMatcherDeprecations
{
    // Public Methods
    // =========================================================================

    public function applyToNodes(array $nodes): void
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'ActiveMatcher `applyToNodes()` has been deprecated. Use `resolve()` instead.');

        $this->resolve($nodes);
    }

    public function matches(NodeElement $node, bool $includeChildren = true): bool
    {
        // Deprecated in 4.0.0
        Craft::$app->getDeprecator()->log(__METHOD__, 'ActiveMatcher `matches()` has been deprecated. Use `isActive()` or `isCurrent()` instead.');

        if ($node->hasResolvedActiveState()) {
            return $includeChildren ? $node->getActiveState()->isActive : $node->getActiveState()->isCurrent;
        }

        $matchType = $this->_matchType($node);

        if (!$includeChildren) {
            return $matchType === self::MATCH_EXACT;
        }

        return $matchType !== self::MATCH_NONE;
    }
}
