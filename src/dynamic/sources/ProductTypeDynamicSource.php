<?php
namespace verbb\navigation\dynamic\sources;

use verbb\navigation\base\DynamicSource;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\DynamicSourceTypes;
use verbb\navigation\helpers\ProductTypeSettings;
use verbb\navigation\models\ProjectedNode;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementInterface;
use craft\commerce\elements\Product as ProductElement;

class ProductTypeDynamicSource extends DynamicSource
{
    // Static Methods
    // =========================================================================

    public static function handle(): string
    {
        return 'productType';
    }

    public static function elementType(): string
    {
        return ProductElement::class;
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [];
    }

    public static function getAddNodeDefaultData(): array
    {
        return ['productTypeId' => ''];
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        return ProductTypeSettings::renderSlideoutHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        ProductTypeSettings::applyPostData($node);
    }

    public static function validateNode(Node $node): bool
    {
        if (!ProductTypeSettings::getProductTypeFromNode($node)) {
            $node->addError('data', Craft::t('navigation', 'Please select a product type.'));

            return false;
        }

        return true;
    }

    public static function getDefaultTitle(Node $node): ?string
    {
        return ProductTypeSettings::getProductTypeFromNode($node)?->name;
    }

    public static function getTypeLabel(Node $node): ?string
    {
        return ProductTypeSettings::getProductTypeFromNode($node)?->name;
    }

    public static function getProjectedChildren(Node $parent, int $siteId): array
    {
        $settings = is_array($parent->data) ? $parent->data : [];
        $productTypeId = (int)($settings['productTypeId'] ?? 0);

        if (!$productTypeId) {
            return [];
        }

        // Live products only for public projections.
        $query = ProductElement::find()
            ->typeId($productTypeId)
            ->siteId($siteId);
        Navigation::$plugin->getDynamicSources()->applyProjectionStatus($query);

        ProductTypeSettings::applyToQuery($query, $parent);

        return array_map(
            static fn(ProductElement $product): ProjectedNode => ProjectedNode::fromElement($product, $parent),
            $query->all(),
        );
    }

    public static function getCacheTags(Node $node): array
    {
        $productType = ProductTypeSettings::getProductTypeFromNode($node);

        if (!$productType?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->productTypeTag($productType->uid)];
    }

    public static function getCacheTagsForProjectedElement(ElementInterface $element): array
    {
        if (!$element instanceof ProductElement) {
            return [];
        }

        $type = \craft\commerce\Plugin::getInstance()->getProductTypes()->getProductTypeById((int)$element->typeId);

        if (!$type?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->productTypeTag($type->uid)];
    }

    public static function shouldDeleteNodeOnSourceDelete(Node $node, string $sourceType, int $sourceId): bool
    {
        return $sourceType === DynamicSourceTypes::PRODUCT_TYPE
            && (int)($node->data['productTypeId'] ?? 0) === $sourceId;
    }
}
