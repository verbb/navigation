<?php
namespace verbb\navigation\base;

use verbb\navigation\elements\Node;
use verbb\navigation\models\ProjectedNode;

use craft\base\ElementInterface;

interface DynamicSourceProvider
{
    // Static Methods
    // =========================================================================

    public static function handle(): string;
    public static function displayName(): string;
    public static function elementType(): string;
    public static function getAddNodeSchema(array $context): array;
    public static function getAddNodeDefaultData(): array;
    public static function renderSlideoutHtml(Node $node): string;
    public static function applyPostData(Node $node): void;
    public static function validateNode(Node $node): bool;
    public static function getDefaultTitle(Node $node): ?string;
    public static function getTypeLabel(Node $node): ?string;
    public static function getProjectedChildren(Node $parent, int $siteId): array;
    public static function getCacheTags(Node $node): array;
    public static function getCacheTagsForProjectedElement(ElementInterface $element): array;
    public static function shouldDeleteNodeOnSourceDelete(Node $node, string $sourceType, int $sourceId): bool;
}
