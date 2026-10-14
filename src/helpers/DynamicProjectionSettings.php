<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

use Craft;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;

/**
 * Shared Dynamic projection controls (limit, etc.) used by every source settings helper.
 */
class DynamicProjectionSettings
{
    // Constants
    // =========================================================================

    public const POSITION_AFTER = 'after';
    public const POSITION_BEFORE = 'before';
    public const POSITION_DATA_KEY = 'projectedChildrenPosition';


    // Static Methods
    // =========================================================================

    public static function positionFieldHtml(Node $node): string
    {
        return Cp::selectFieldHtml([
            'label' => Craft::t('navigation', 'Projected items position'),
            'instructions' => Craft::t('navigation', 'Choose whether projected items appear before or after manually added children.'),
            'id' => self::POSITION_DATA_KEY,
            'name' => 'data[' . self::POSITION_DATA_KEY . ']',
            'value' => self::getPosition($node),
            'options' => self::positionOptions(),
        ]);
    }

    public static function positionOptions(): array
    {
        return [
            ['label' => Craft::t('navigation', 'After manual children'), 'value' => self::POSITION_AFTER],
            ['label' => Craft::t('navigation', 'Before manual children'), 'value' => self::POSITION_BEFORE],
        ];
    }

    public static function getPosition(Node $node): string
    {
        return self::normalizePosition($node->data[self::POSITION_DATA_KEY] ?? null);
    }

    public static function setPosition(Node $node, mixed $position): void
    {
        $data = is_array($node->data) ? $node->data : [];
        $data[self::POSITION_DATA_KEY] = self::normalizePosition($position);
        $node->data = $data;
    }

    public static function normalizePosition(mixed $position): string
    {
        return $position === self::POSITION_BEFORE ? self::POSITION_BEFORE : self::POSITION_AFTER;
    }

    public static function projectedChildrenFirst(Node $node): bool
    {
        return self::getPosition($node) === self::POSITION_BEFORE;
    }

    public static function mergeChildren(Node $node, array $stored, array $projected): array
    {
        return self::projectedChildrenFirst($node)
            ? array_merge($projected, $stored)
            : array_merge($stored, $projected);
    }

    public static function limitFieldHtml(Node $node): string
    {
        $limit = $node->data['limit'] ?? '';

        return Cp::textFieldHtml([
            'label' => Craft::t('app', 'Limit'),
            'instructions' => Craft::t('navigation', 'Maximum number of items to project. Leave blank for no limit.'),
            'id' => 'limit',
            'name' => 'limit',
            'value' => $limit === null || $limit === '' ? '' : (string)(int)$limit,
            'type' => 'number',
            'min' => 1,
            'size' => 5,
            'placeholder' => Craft::t('navigation', 'No limit'),
        ]);
    }

    public static function applyLimit(ElementQueryInterface $query, array $settings): void
    {
        $limit = (int)($settings['limit'] ?? 0);

        // 0 / empty means unrestricted — only clamp when authors set an explicit positive limit.
        if ($limit > 0) {
            $query->limit($limit);
        }
    }
}
