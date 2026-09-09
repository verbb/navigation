<?php
namespace verbb\navigation\dynamic\sources;

use verbb\navigation\base\DynamicSource;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\CategoryGroupSettings;
use verbb\navigation\helpers\DynamicSourceTypes;
use verbb\navigation\helpers\NodeTypeSchemaFields;
use verbb\navigation\models\ProjectedNode;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Category as CategoryElement;

class CategoryGroupDynamicSource extends DynamicSource
{
    // Static Methods
    // =========================================================================

    public static function handle(): string
    {
        return 'categoryGroup';
    }

    public static function elementType(): string
    {
        return CategoryElement::class;
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [
            NodeTypeSchemaFields::categoryGroupIdField(),
        ];
    }

    public static function getAddNodeDefaultData(): array
    {
        return ['groupId' => ''];
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        return CategoryGroupSettings::renderSlideoutHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        CategoryGroupSettings::applyPostData($node);
    }

    public static function validateNode(Node $node): bool
    {
        if (!CategoryGroupSettings::getGroupFromNode($node)) {
            $node->addError('data', Craft::t('navigation', 'Please select a category group.'));

            return false;
        }

        return true;
    }

    public static function getDefaultTitle(Node $node): ?string
    {
        return CategoryGroupSettings::getGroupFromNode($node)?->name;
    }

    public static function getTypeLabel(Node $node): ?string
    {
        return CategoryGroupSettings::getGroupFromNode($node)?->name;
    }

    public static function getProjectedChildren(Node $parent, int $siteId): array
    {
        $settings = is_array($parent->data) ? $parent->data : [];
        $groupId = (int)($settings['groupId'] ?? 0);

        if (!$groupId) {
            return [];
        }

        $parentCategoryId = (int)($settings['parentCategoryId'] ?? 0) ?: null;
        $group = Craft::$app->getCategories()->getGroupById($groupId);

        // Live/public categories only — same visibility contract as entry projections.
        $query = CategoryElement::find()
            ->groupId($groupId)
            ->siteId($siteId);
        Navigation::$plugin->getDynamicSources()->applyProjectionStatus($query);

        if ($parentCategoryId) {
            $query->descendantOf($parentCategoryId)->level(max(1, (int)$parent->level) + 1);
        } elseif ($group && (int)$group->maxLevels !== 1) {
            $query->level(1);
        }

        CategoryGroupSettings::applyToQuery($query, $parent);

        return array_map(
            static fn(CategoryElement $category): ProjectedNode => ProjectedNode::fromElement($category, $parent),
            $query->all(),
        );
    }

    public static function getCacheTags(Node $node): array
    {
        $group = CategoryGroupSettings::getGroupFromNode($node);

        if (!$group?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->categoryGroupTag($group->uid)];
    }

    public static function getCacheTagsForProjectedElement(ElementInterface $element): array
    {
        if (!$element instanceof CategoryElement) {
            return [];
        }

        $group = Craft::$app->getCategories()->getGroupById((int)$element->groupId);

        if (!$group?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->categoryGroupTag($group->uid)];
    }

    public static function shouldDeleteNodeOnSourceDelete(Node $node, string $sourceType, int $sourceId): bool
    {
        return $sourceType === DynamicSourceTypes::CATEGORY_GROUP
            && (int)($node->data['groupId'] ?? 0) === $sourceId;
    }
}
