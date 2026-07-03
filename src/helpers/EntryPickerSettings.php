<?php
namespace verbb\navigation\helpers;

use Craft;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\Entry as EntryElement;
use craft\helpers\Component;
use craft\helpers\Cp;

class EntryPickerSettings
{
    // Static Methods
    // =========================================================================

    public static function renderPermissionSettingsHtml(array $settings, string $fieldPrefix): string
    {
        $html = Cp::lightswitchFieldHtml([
            'label' => Craft::t('navigation', 'Exclude Entries Without a URI'),
            'instructions' => Craft::t('navigation', 'When enabled, entries that do not have a public URL will not appear in the element picker.'),
            'id' => 'excludeWithoutUri',
            'name' => $fieldPrefix . '[excludeWithoutUri]',
            'on' => !empty($settings['excludeWithoutUri']),
        ]);

        $singleOptions = ElementPickerHelper::singleSectionOptions();

        if ($singleOptions) {
            $html .= Cp::checkboxSelectFieldHtml([
                'label' => Craft::t('navigation', 'Hide Singles'),
                'instructions' => Craft::t('navigation', 'Select single sections to hide from the element picker.'),
                'id' => 'hideSingleSectionUids',
                'name' => $fieldPrefix . '[hideSingleSectionUids]',
                'options' => $singleOptions,
                'values' => $settings['hideSingleSectionUids'] ?? [],
            ]);
        }

        $entryCondition = self::resolveSelectionCondition($settings['selectionCondition'] ?? null);
        $entryCondition->mainTag = 'div';
        $entryCondition->id = 'selectionCondition';
        $entryCondition->name = $fieldPrefix . '[selectionCondition]';
        $entryCondition->forProjectConfig = true;

        $html .= Cp::fieldHtml($entryCondition->getBuilderHtml(), [
            'label' => Craft::t('navigation', 'Selectable Entry Conditions'),
            'instructions' => Craft::t('navigation', 'Only include entries that match the following rules in the element picker.'),
        ]);

        return $html;
    }

    public static function resolveSelectionCondition(?array $config): EntryCondition
    {
        if (!empty($config['conditionRules'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                ['class' => EntryCondition::class, 'elementType' => EntryElement::class],
                $config,
            ));

            if ($condition instanceof EntryCondition) {
                return $condition;
            }
        }

        return EntryElement::createCondition();
    }

    /**
     * Normalizes posted permission settings for the Entry node type.
     */
    public static function normalizePermissionSettings(array $settings): array
    {
        $settings['excludeWithoutUri'] = !empty($settings['excludeWithoutUri']);

        if (!empty($settings['hideSingleSectionUids']) && is_array($settings['hideSingleSectionUids'])) {
            $settings['hideSingleSectionUids'] = array_values($settings['hideSingleSectionUids']);
        } else {
            unset($settings['hideSingleSectionUids']);
        }

        if (isset($settings['selectionCondition'])) {
            $conditionConfig = Component::cleanseConfig($settings['selectionCondition']);

            if (empty($conditionConfig['conditionRules'])) {
                unset($settings['selectionCondition']);
            } else {
                $condition = self::resolveSelectionCondition($conditionConfig);
                $settings['selectionCondition'] = $condition->getConfig();
            }
        }

        return $settings;
    }
}
