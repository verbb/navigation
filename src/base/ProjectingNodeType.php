<?php
namespace verbb\navigation\base;

use verbb\navigation\elements\Node;

interface ProjectingNodeType extends NodeTypeInterface
{
    // Public Methods
    // =========================================================================

    public function getProjectedChildren(Node $parent, int $siteId): array;
}
