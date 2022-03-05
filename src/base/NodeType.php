<?php
namespace verbb\navigation\base;

use verbb\navigation\elements\Node;

use Craft;
use craft\base\Component;

abstract class NodeType extends Component implements NodeTypeInterface
{
    // Properties
    // =========================================================================

    public ?Node $node = null;


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

    public function getModalHtml(): ?string
    {
        return null;
    }

    public function getSettingsHtml(): ?string
    {
        return null;
    }

    public function getUrl(): ?string
    {
        return null;
    }
}