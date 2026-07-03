<?php
namespace verbb\navigation\helpers;

use verbb\navigation\nodetypes\Entry as EntryNodeType;

use Craft;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\Entry as EntryElement;
use craft\models\Section;

class ElementPickerHelper
{
    // Static Methods
    // =========================================================================

    /**
     * Intersects menu-configured element sources with sources the current user can access.
     */
    public static function filterSourcesForUser(string $elementType, mixed $configuredSources): array
    {
        $availableKeys = [];

        foreach (Craft::$app->getElementSources()->getSources($elementType, 'modal') as $source) {
            if (!isset($source['key'])) {
                continue;
            }

            if (($source['type'] ?? null) === 'heading') {
                continue;
            }

            $availableKeys[] = $source['key'];
        }

        if ($configuredSources === '*' || $configuredSources === null || $configuredSources === []) {
            return $availableKeys;
        }

        return array_values(array_intersect((array)$configuredSources, $availableKeys));
    }

    public static function getTypeSettings(array $permissions, string $nodeTypeClass): array
    {
        $permissions = MenuPermissions::normalize($permissions);

        return $permissions[$nodeTypeClass] ?? [];
    }

    public static function getPickerConfig(array $permissions, string $nodeTypeClass, string $elementType): array
    {
        if ($elementType !== EntryElement::class && $nodeTypeClass !== EntryNodeType::class) {
            return [];
        }

        $settings = self::getTypeSettings($permissions, EntryNodeType::class);
        $config = [];
        $criteria = [];

        if (!empty($settings['excludeWithoutUri'])) {
            $criteria['uri'] = ':notempty:';
        }

        $hiddenSectionIds = self::_resolveHiddenSingleSectionIds($settings['hideSingleSectionUids'] ?? []);

        if ($hiddenSectionIds) {
            $criteria['sectionId'] = array_merge(['not'], $hiddenSectionIds);
        }

        if ($criteria) {
            $config['criteria'] = $criteria;
        }

        $condition = self::_resolveSelectionCondition($settings['selectionCondition'] ?? null);

        if ($condition) {
            $config['condition'] = $condition->getConfig();
        }

        return $config;
    }

    public static function singleSectionOptions(): array
    {
        $options = [];

        foreach (Craft::$app->getEntries()->getAllSections() as $section) {
            if ($section->type !== Section::TYPE_SINGLE) {
                continue;
            }

            $options[] = [
                'value' => $section->uid,
                'label' => $section->name,
            ];
        }

        return $options;
    }


    // Private Methods
    // =========================================================================

    private static function _resolveHiddenSingleSectionIds(array $sectionUids): array
    {
        if (!$sectionUids) {
            return [];
        }

        $ids = [];
        $entries = Craft::$app->getEntries();

        foreach ($sectionUids as $uid) {
            $section = $entries->getSectionByUid($uid);

            if ($section && $section->type === Section::TYPE_SINGLE) {
                $ids[] = (int)$section->id;
            }
        }

        return $ids;
    }

    private static function _resolveSelectionCondition(?array $config): ?ElementConditionInterface
    {
        if (empty($config['conditionRules'])) {
            return null;
        }

        $condition = Craft::$app->getConditions()->createCondition(array_merge(
            ['class' => EntryCondition::class, 'elementType' => EntryElement::class],
            $config,
        ));

        return $condition instanceof ElementConditionInterface ? $condition : null;
    }
}
