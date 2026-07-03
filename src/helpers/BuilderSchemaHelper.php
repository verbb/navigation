<?php
namespace verbb\navigation\helpers;

use verbb\navigation\base\NodeType;

class BuilderSchemaHelper
{
    // Static Methods
    // =========================================================================

    public static function compileAddNodeSchema(array $tab, bool $showParent, array $parentOptions): array
    {
        return self::compileSchema(self::buildAddNodeSchema($tab, $showParent, $parentOptions));
    }

    public static function buildAddNodeSchema(array $tab, bool $showParent, array $parentOptions): array
    {
        $fields = [];

        if ($showParent) {
            $fields[] = NodeTypeSchemaFields::parentField($parentOptions);
        }

        $typeClass = $tab['type'] ?? null;

        if (is_string($typeClass) && is_subclass_of($typeClass, NodeType::class)) {
            $fields = array_merge($fields, $typeClass::getAddNodeSchema($tab));
        }

        return $fields;
    }

    public static function compileSchema(array $schema): array
    {
        $entries = [];
        self::_collectSchemaFields($schema, '', $entries);

        return [
            'schema' => $schema,
            'fieldEntries' => $entries,
        ];
    }


    // Private Methods
    // =========================================================================

    private static function _collectSchemaFields($node, string $prefix, array &$entries): void
    {
        if (!is_array($node)) {
            return;
        }

        if (array_is_list($node)) {
            foreach ($node as $child) {
                self::_collectSchemaFields($child, $prefix, $entries);
            }

            return;
        }

        if (isset($node['$field'], $node['name'])) {
            $entries[] = [
                'path' => $prefix . $node['name'],
                'field' => self::_sanitizeFieldEntryNode($node),
            ];
        }

        $childPrefix = $node['schemaChildPrefix'] ?? '';

        if (isset($node['schema'])) {
            self::_collectSchemaFields($node['schema'], $prefix . $childPrefix, $entries);
        } elseif (isset($node['children'])) {
            self::_collectSchemaFields($node['children'], $prefix . $childPrefix, $entries);
        }
    }

    private static function _sanitizeFieldEntryNode(array $node): array
    {
        unset($node['children'], $node['schema']);

        return $node;
    }
}
