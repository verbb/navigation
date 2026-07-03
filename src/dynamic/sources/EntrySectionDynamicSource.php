<?php
namespace verbb\navigation\dynamic\sources;

use verbb\navigation\base\DynamicSourceProvider;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\DynamicSourceTypes;
use verbb\navigation\helpers\EntrySectionSettings;
use verbb\navigation\helpers\NodeTypeSchemaFields;
use verbb\navigation\models\ProjectedNode;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry as EntryElement;
use craft\models\Section;

class EntrySectionDynamicSource implements DynamicSourceProvider
{
    // Static Methods
    // =========================================================================

    public static function handle(): string
    {
        return 'entrySection';
    }

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Entry section');
    }

    public static function elementType(): string
    {
        return EntryElement::class;
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [
            NodeTypeSchemaFields::sectionIdField(),
        ];
    }

    public static function getAddNodeDefaultData(): array
    {
        return ['sectionId' => ''];
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        return EntrySectionSettings::renderSlideoutHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        EntrySectionSettings::applyPostData($node);
    }

    public static function validateNode(Node $node): bool
    {
        if (!EntrySectionSettings::getSectionFromNode($node)) {
            $node->addError('data', Craft::t('navigation', 'Please select a section.'));

            return false;
        }

        return true;
    }

    public static function getDefaultTitle(Node $node): ?string
    {
        return EntrySectionSettings::getSectionFromNode($node)?->name;
    }

    public static function getTypeLabel(Node $node): ?string
    {
        return EntrySectionSettings::getSectionFromNode($node)?->name;
    }

    public static function getProjectedChildren(Node $parent, int $siteId): array
    {
        $settings = is_array($parent->data) ? $parent->data : [];
        $sectionId = (int)($settings['sectionId'] ?? 0);

        if (!$sectionId) {
            return [];
        }

        $parentEntryId = (int)($settings['parentEntryId'] ?? 0) ?: null;
        $section = Craft::$app->getEntries()->getSectionById($sectionId);

        $query = EntryElement::find()
            ->sectionId($sectionId)
            ->siteId($siteId)
            ->status(null);

        if ($parentEntryId) {
            $query->descendantOf($parentEntryId)->level(max(1, (int)$parent->level) + 1);
        } elseif ($section?->type === Section::TYPE_STRUCTURE) {
            $query->level(1);
        }

        EntrySectionSettings::applyToQuery($query, $parent);

        return array_map(
            static fn(EntryElement $entry): ProjectedNode => ProjectedNode::fromElement($entry, $parent),
            $query->all(),
        );
    }

    public static function getCacheTags(Node $node): array
    {
        $section = EntrySectionSettings::getSectionFromNode($node);

        if (!$section?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->sectionTag($section->uid)];
    }

    public static function getCacheTagsForProjectedElement(ElementInterface $element): array
    {
        if (!$element instanceof EntryElement) {
            return [];
        }

        $section = $element->getSection();

        if (!$section?->uid) {
            return [];
        }

        return [Navigation::$plugin->getNavigationCache()->sectionTag($section->uid)];
    }

    public static function shouldDeleteNodeOnSourceDelete(Node $node, string $sourceType, int $sourceId): bool
    {
        return $sourceType === DynamicSourceTypes::SECTION
            && (int)($node->data['sectionId'] ?? 0) === $sourceId;
    }
}
