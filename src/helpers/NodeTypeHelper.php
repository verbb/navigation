<?php
namespace verbb\navigation\helpers;

use verbb\navigation\nodetypes\Asset;
use verbb\navigation\nodetypes\Category;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Product;
use verbb\navigation\nodetypes\Site;

use craft\elements\Asset as AssetElement;
use craft\elements\Category as CategoryElement;
use craft\elements\Entry as EntryElement;
use craft\fields\data\ColorData;

class NodeTypeHelper
{
    // Static Methods
    // =========================================================================

    /**
     * Maps legacy 3.x element FQCNs stored in `navigation_nodes.type` to 4.x node type classes.
     */
    public static function legacyElementTypeMap(): array
    {
        $map = [
            EntryElement::class => Entry::class,
            CategoryElement::class => Category::class,
            AssetElement::class => Asset::class,
        ];

        if (class_exists(\craft\commerce\elements\Product::class)) {
            $map[\craft\commerce\elements\Product::class] = Product::class;
        }

        return $map;
    }

    /**
     * Maps v3 built-in node type class names (stored in `navigation_nodes.type`) to 4.x short names.
     */
    public static function legacyV3NodeTypeClassMap(): array
    {
        return [
            'verbb\\navigation\\nodetypes\\CustomType' => Custom::class,
            'verbb\\navigation\\nodetypes\\PassiveType' => Passive::class,
            'verbb\\navigation\\nodetypes\\SiteType' => Site::class,
        ];
    }

    public static function resolveTypeClass(?string $type): ?string
    {
        if (!$type) {
            return null;
        }

        $type = self::legacyElementTypeMap()[$type] ?? $type;

        return self::legacyV3NodeTypeClassMap()[$type] ?? $type;
    }

    public static function isLegacyElementType(?string $type): bool
    {
        return $type && isset(self::legacyElementTypeMap()[$type]);
    }

    public static function resolvePermissionsTypeKeys(array $permissions): array
    {
        return MenuPermissions::normalize($permissions);
    }

    public static function rgbTriplet(string $hexColor): string
    {
        $colorData = new ColorData($hexColor);

        return "{$colorData->getRed()},{$colorData->getGreen()},{$colorData->getBlue()}";
    }

    /**
     * Derives a text RGB triplet that meets WCAG AA (4.5:1) against the tinted badge background.
     * Node types still only define one base color via `getColor()`.
     */
    public static function accessibleTextColorRgb(string $hexColor, float $bgAlpha = 0.1): string
    {
        $base = new ColorData($hexColor);

        $bgR = (int)round(255 * (1 - $bgAlpha) + $base->getRed() * $bgAlpha);
        $bgG = (int)round(255 * (1 - $bgAlpha) + $base->getGreen() * $bgAlpha);
        $bgB = (int)round(255 * (1 - $bgAlpha) + $base->getBlue() * $bgAlpha);
        $bgLuminance = self::_relativeLuminance($bgR, $bgG, $bgB);

        for ($mix = 0.0; $mix <= 1.0; $mix += 0.05) {
            $r = (int)round($base->getRed() * (1 - $mix));
            $g = (int)round($base->getGreen() * (1 - $mix));
            $b = (int)round($base->getBlue() * (1 - $mix));

            if (self::_contrastRatio(self::_relativeLuminance($r, $g, $b), $bgLuminance) >= 4.5) {
                return "$r,$g,$b";
            }
        }

        return '51,51,51';
    }


    // Private Methods
    // =========================================================================

    private static function _relativeLuminance(int $r, int $g, int $b): float
    {
        $channels = [$r, $g, $b];

        foreach ($channels as &$channel) {
            $channel /= 255;
            $channel = $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    private static function _contrastRatio(float $luminanceA, float $luminanceB): float
    {
        $lighter = max($luminanceA, $luminanceB);
        $darker = min($luminanceA, $luminanceB);

        return ($lighter + 0.05) / ($darker + 0.05);
    }
}
