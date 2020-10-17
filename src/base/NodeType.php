<?php
namespace verbb\navigation\base;

use craft\base\Component;

abstract class NodeType extends Component implements NodeTypeInterface
{
    // Properties
    // =========================================================================

    public $node = null;


    // Static
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('navigation', 'Node Type');
    }

    public static function hasTitle(): bool
    {
        return true;
    }

    public static function hasUrl(): bool
    {
        return true;
    }

    public static function hasNewWindow(): bool
    {
        return false;
    }

    public static function hasClasses(): bool
    {
        return false;
    }

    public static function hasAttributes(): bool
    {
        return false;
    }


    // Public Methods
    // =========================================================================

    public function getModalHtml()
    {
        return null;
    }

    public function getSettingsHtml()
    {
        return null;
    }

    public function getUrl()
    {
        return null;
    }
}