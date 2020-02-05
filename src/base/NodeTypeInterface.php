<?php
namespace verbb\navigation\base;

use craft\base\ComponentInterface;

interface NodeTypeInterface extends ComponentInterface
{
    // Static
    // =========================================================================

    public static function displayName(): string;
    public static function hasTitle(): bool;
    public static function hasUrl(): bool;
    public static function hasNewWindow(): bool;
    public static function hasClasses(): bool;


    // Public Methods
    // =========================================================================

    public function getSettingsHtml();
    public function getUrl();
}
