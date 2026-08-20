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
    // Static Methods
    // =========================================================================

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
