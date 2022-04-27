<?php
namespace verbb\navigation\base;

use craft\base\ComponentInterface;

interface NodeTypeInterface extends ComponentInterface
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string;

    public static function hasTitle(): bool;

    public static function hasUrl(): bool;

    public static function hasNewWindow(): bool;


    // Public Methods
    // =========================================================================

    public function getSettingsHtml(): ?string;

    public function getUrl(): ?string;
}
