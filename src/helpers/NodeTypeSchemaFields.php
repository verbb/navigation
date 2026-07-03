<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;

use Craft;

/**
 * Shared Plugin Kit field definitions for the builder quick-add form.
 */
class NodeTypeSchemaFields
{
    // Static Methods
    // =========================================================================

    public static function parentField(array $parentOptions): array
    {
        return self::_comboboxField([
            'name' => 'parentId',
            'label' => Craft::t('navigation', 'Parent'),
            'instructions' => Craft::t('navigation', 'Select a node as the parent.'),
            'placeholder' => Craft::t('navigation', 'Top level'),
            'options' => array_values(array_filter(
                array_map(
                    static fn(array $option) => [
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'disabled' => $option['disabled'] ?? false,
                    ],
                    $parentOptions,
                ),
                static fn(array $option) => (string)$option['value'] !== '' && (int)$option['value'] !== 0,
            )),
        ]);
    }

    public static function newWindowField(): array
    {
        return self::_lightswitchField([
            'name' => 'newWindow',
            'label' => Craft::t('navigation', 'Open in new window'),
            'instructions' => Craft::t('navigation', 'Whether to open this node in a new window.'),
        ]);
    }

    public static function enabledForPropagatedSitesField(bool $default = true): array
    {
        return self::_lightswitchField([
            'name' => 'enabledForPropagatedSites',
            'label' => Craft::t('navigation', 'Enabled on other sites'),
            'instructions' => Craft::t('navigation', 'Whether this node should be enabled on other sites this menu propagates to.'),
            'defaultValue' => $default,
        ]);
    }

    public static function titleField(): array
    {
        return self::_textField([
            'name' => 'title',
            'label' => Craft::t('app', 'Title'),
            'validation' => 'required',
        ]);
    }

    public static function urlField(): array
    {
        return self::_textField([
            'name' => 'url',
            'label' => Craft::t('app', 'URL'),
        ]);
    }

    public static function siteIdField(): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Select a site'),
            'value' => '',
        ]];

        foreach (Craft::$app->getSites()->getEditableSites() as $site) {
            if ($site->hasUrls) {
                $options[] = [
                    'label' => $site->name,
                    'value' => (string)$site->id,
                ];
            }
        }

        return self::_selectField([
            'name' => 'data.siteId',
            'label' => Craft::t('app', 'Site'),
            'instructions' => Craft::t('navigation', 'Select a site to use its Base URL.'),
            'validation' => 'required',
            'options' => $options,
        ]);
    }

    public static function dynamicSourceField(): array
    {
        return self::_selectField([
            'name' => 'data.dynamicSource',
            'label' => Craft::t('navigation', 'Source'),
            'instructions' => Craft::t('navigation', 'Choose which elements to project as child menu items.'),
            'validation' => 'required',
            'options' => Navigation::$plugin->getDynamicSources()->getProviderOptions(),
        ]);
    }

    public static function sectionIdField(): array
    {
        return self::_comboboxField([
            'name' => 'data.sectionId',
            'label' => Craft::t('app', 'Section'),
            'instructions' => Craft::t('navigation', 'Choose a channel or structure section. Its entries will appear as children when the menu is rendered.'),
            'placeholder' => Craft::t('navigation', 'Select a section'),
            'validation' => 'required',
            'options' => EntrySectionSettings::sectionOptions(),
        ]);
    }

    public static function categoryGroupIdField(): array
    {
        return self::_comboboxField([
            'name' => 'data.groupId',
            'label' => Craft::t('navigation', 'Category Group'),
            'instructions' => Craft::t('navigation', 'Choose a category group. Its categories will appear as children when the menu is rendered.'),
            'placeholder' => Craft::t('navigation', 'Select a category group'),
            'validation' => 'required',
            'options' => CategoryGroupSettings::groupOptions(),
        ]);
    }

    public static function volumeIdField(): array
    {
        return self::_comboboxField([
            'name' => 'data.volumeId',
            'label' => Craft::t('app', 'Volume'),
            'instructions' => Craft::t('navigation', 'Choose an asset volume. Its assets will appear as children when the menu is rendered.'),
            'placeholder' => Craft::t('navigation', 'Select a volume'),
            'validation' => 'required',
            'options' => AssetVolumeSettings::volumeOptions(),
        ]);
    }


    // Private Methods
    // =========================================================================

    private static function _comboboxField(array $config): array
    {
        return array_merge([
            '$field' => 'combobox',
            'multiple' => false,
        ], $config);
    }

    private static function _selectField(array $config): array
    {
        return array_merge([
            '$field' => 'select',
        ], $config);
    }

    private static function _textField(array $config): array
    {
        return array_merge([
            '$field' => 'text',
        ], $config);
    }

    private static function _lightswitchField(array $config): array
    {
        return array_merge([
            '$field' => 'lightswitch',
        ], $config);
    }
}
