<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\ElementNodeType;
use verbb\navigation\helpers\EntryPickerSettings;

use Craft;
use craft\elements\Entry as EntryElement;

class Entry extends ElementNodeType
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('site', EntryElement::pluralDisplayName());
    }

    public static function getElementType(): string
    {
        return EntryElement::class;
    }

    public static function getColor(): string
    {
        return '#5e5378';
    }


    // Public Methods
    // =========================================================================

    public function getPermissionSettingsHtml(array $settings, string $fieldPrefix = ''): string
    {
        return EntryPickerSettings::renderPermissionSettingsHtml($settings, $fieldPrefix);
    }
}
