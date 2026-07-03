<?php
namespace verbb\navigation\base;

use verbb\navigation\elements\Node;
use verbb\navigation\helpers\NodeTypeSchemaFields;

use Craft;
use craft\base\ElementInterface;

abstract class ElementNodeType extends NodeType
{
    // Static Methods
    // =========================================================================

    abstract public static function getElementType(): string;

    public static function hasUrl(): bool
    {
        return true;
    }

    public static function hasNewWindow(): bool
    {
        return true;
    }

    public static function getTag(): string
    {
        return 'a';
    }

    /**
     * Builder tab metadata (replaces legacy Elements service entries).
     */
    public static function getBuilderConfig(): array
    {
        $elementType = static::getElementType();

        return [
            'label' => Craft::t('site', $elementType::pluralDisplayName()),
            'button' => Craft::t('navigation', 'Add {name}', [
                'name' => mb_strtolower($elementType::pluralDisplayName()),
            ]),
            'type' => static::class,
            'elementType' => $elementType,
            'category' => 'elementNodeType',
            'color' => static::getColor(),
            'default' => true,
        ];
    }

    public static function getAddNodeSchema(array $context): array
    {
        return [NodeTypeSchemaFields::newWindowField()];
    }

    /**
     * Node type values stored in `navigation_nodes.type` (4.x class + legacy element FQCN).
     */
    public static function getStoredTypeValues(): array
    {
        return array_values(array_unique([
            static::class,
            static::getElementType(),
        ]));
    }

    /**
     * Element IDs bulk-soft-deleted when Craft removes a source without per-element delete events.
     * Return null when this node type does not handle the source type.
     *
     * Matching nodes are disabled (not deleted) so they can be restored when the source is restored.
     */
    public static function getBulkSoftDeletedElementIds(string $sourceType, int $sourceId): ?array
    {
        return null;
    }


    // Public Methods
    // =========================================================================

    public function getUrl(): ?string
    {
        if (!$this->node?->elementId) {
            return null;
        }

        $element = $this->node->getElement();

        if (!$element instanceof ElementInterface) {
            return null;
        }

        return $element->getUrl();
    }

    public function getDefaultTitle(): string
    {
        $element = $this->node?->getElement();

        if ($element instanceof ElementInterface && $element->hasTitles()) {
            return (string)$element->title;
        }

        return static::displayName();
    }

    public function beforeSaveNode(bool $isNew): bool
    {
        if (!$this->node?->elementId) {
            $this->node->addError('elementId', Craft::t('navigation', 'Element ID is required.'));
            $this->node->addError('linkedElementId', Craft::t('navigation', 'Linked Element ID is required.'));

            return false;
        }

        return true;
    }

    public function getPermissionEnabledDefault(): bool
    {
        return static::getBuilderConfig()['default'] ?? true;
    }

    public function getPermissionSourceOptions(): array
    {
        $options = [];

        foreach (Craft::$app->getElementSources()->getSources(static::getElementType(), 'modal') as $source) {
            if (!isset($source['key'], $source['label'])) {
                continue;
            }

            $options[] = [
                'value' => $source['key'],
                'label' => Craft::t('site', $source['label']),
            ];
        }

        return $options;
    }
}
