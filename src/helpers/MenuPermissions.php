<?php
namespace verbb\navigation\helpers;

class MenuPermissions
{
    // Static Methods
    // =========================================================================

    /**
     * Normalizes menu permission keys to node type class names (e.g. `verbb\navigation\nodetypes\Entry`).
     */
    public static function normalize(array $permissions): array
    {
        $normalized = [];

        foreach ($permissions as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $typeClass = self::resolveTypeKey((string)$key);
            $merged = self::_mergeTypeSettings(
                $normalized[$typeClass] ?? null,
                $value,
            );
            $merged['typeClass'] = $typeClass;

            $normalized[$typeClass] = self::_normalizeTypeSettings($merged);
        }

        return $normalized;
    }

    public static function resolveTypeKey(string $key): string
    {
        return NodeTypeHelper::legacyElementTypeMap()[$key]
            ?? NodeTypeHelper::legacyV3NodeTypeClassMap()[$key]
            ?? NodeTypeHelper::resolveTypeClass($key)
            ?? $key;
    }

    public static function isTypeEnabled(array $permissions, string $nodeTypeClass, bool $default = true): bool
    {
        $permissions = self::normalize($permissions);
        $settings = $permissions[$nodeTypeClass] ?? null;

        if ($settings === null) {
            return $default;
        }

        return (bool)($settings['enabled'] ?? $default);
    }

    public static function getTypeSources(array $permissions, string $nodeTypeClass, mixed $default = '*'): mixed
    {
        $permissions = self::normalize($permissions);

        return $permissions[$nodeTypeClass]['permissions'] ?? $default;
    }


    // Private Methods
    // =========================================================================

    private static function _mergeTypeSettings(?array $existing, array $incoming): array
    {
        if ($existing === null) {
            return $incoming;
        }

        $enabled = (bool)($existing['enabled'] ?? false) || (bool)($incoming['enabled'] ?? false);
        $permissions = $incoming['permissions'] ?? $existing['permissions'] ?? '*';

        return array_merge($existing, $incoming, [
            'enabled' => $enabled,
            'permissions' => $permissions,
        ]);
    }

    private static function _normalizeTypeSettings(array $settings): array
    {
        if (array_key_exists('enabled', $settings)) {
            $settings['enabled'] = (bool)$settings['enabled'];
        }

        $typeClass = $settings['typeClass'] ?? null;

        if ($typeClass === \verbb\navigation\nodetypes\Entry::class) {
            $settings = \verbb\navigation\helpers\EntryPickerSettings::normalizePermissionSettings($settings);
        }

        unset($settings['typeClass']);

        return $settings;
    }
}
