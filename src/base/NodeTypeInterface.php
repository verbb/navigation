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
    public static function getColor(): string;


    // Public Methods
    // =========================================================================

    public function getEditorHtml(): ?string;
    public function getUrl(): ?string;
}
