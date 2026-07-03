<?php
namespace verbb\navigation\base;

use verbb\navigation\elements\Node;
use verbb\navigation\helpers\NodeTypeSchemaFields;

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

    public static function getTag(): string
    {
        return static::hasUrl() ? 'a' : 'span';
    }

    /**
     * Plugin Kit schema fields for the builder quick-add panel.
     */
    public static function getAddNodeSchema(array $context): array
    {
        $fields = [];

        if (static::hasNewWindow()) {
            $fields[] = NodeTypeSchemaFields::newWindowField();
        }

        if (static::hasTitle()) {
            $fields[] = NodeTypeSchemaFields::titleField();
        }

        if (static::hasUrl()) {
            $fields[] = NodeTypeSchemaFields::urlField();
        }

        return $fields;
    }

    /**
     * Default `data` payload for quick-add forms.
     */
    public static function getAddNodeDefaultData(): array
    {
        return [];
    }


    // Properties
    // =========================================================================

    public ?Node $node = null;


    // Public Methods
    // =========================================================================

    public function getEditorHtml(): ?string
    {
        return null;
    }

    /**
     * @deprecated in 4.0.0. Use [[getEditorHtml()]] instead.
     */
    public function getModalHtml(): ?string
    {
        return $this->getEditorHtml();
    }

    /**
     * @deprecated in 4.0.0. Use [[getEditorHtml()]] instead.
     */
    public function getSettingsHtml(): ?string
    {
        return $this->getEditorHtml();
    }

    public function getUrl(): ?string
    {
        return null;
    }

    public function getDefaultTitle(): string
    {
        return static::displayName();
    }

    public function getTypeLabel(): string
    {
        return static::displayName();
    }

    public function beforeSaveNode(bool $isNew): bool
    {
        return true;
    }

    public function getPermissionEnabledDefault(): bool
    {
        return true;
    }

    public function getPermissionSourceOptions(): array
    {
        return [];
    }

    public function getPermissionSettingsHtml(array $settings, string $fieldPrefix = ''): string
    {
        return '';
    }
}
