<?php
namespace verbb\navigation\nodetypes;

use verbb\navigation\base\ElementNodeType;
use verbb\navigation\helpers\EntryPickerSettings;
use verbb\navigation\helpers\DynamicSourceTypes;

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

    public static function getBulkSoftDeletedElementIds(string $sourceType, int $sourceId): ?array
    {
        if ($sourceType !== DynamicSourceTypes::SECTION) {
            return null;
        }

        return EntryElement::find()
            ->sectionId($sourceId)
            ->status(null)
            ->trashed(null)
            ->ids();
    }


    // Public Methods
    // =========================================================================

    public function getPermissionSettingsHtml(array $settings, string $fieldPrefix = ''): string
    {
        return EntryPickerSettings::renderPermissionSettingsHtml($settings, $fieldPrefix);
    }
}
