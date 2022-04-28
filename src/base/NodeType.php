<?php
namespace verbb\navigation\base;

use verbb\navigation\elements\Node;

use Craft;
use craft\base\Component;

abstract class NodeType extends Component implements NodeTypeInterface
{
    // Static Methods
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

    public static function getColor(): string
    {
        return '#888888';
    }


    // Properties
    // =========================================================================

    public ?Node $node = null;


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