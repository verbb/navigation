<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;
use verbb\navigation\migrations\plugins\MigrationResult;
use verbb\navigation\models\MenuImportResult;
use verbb\navigation\models\MenuSiteSettings;
use verbb\navigation\nodetypes\Asset;
use verbb\navigation\nodetypes\Category;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Entry;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Product;
use verbb\navigation\nodetypes\Site;

use Craft;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\helpers\Json;

use DateTime;

class PluginMigrationHelper
{
    // Static Methods
    // =========================================================================

    public static function tableExists(string $table): bool
    {
        return Craft::$app->getDb()->tableExists($table);
    }

    public static function isPluginInstalled(string $handle): bool
    {
        return Craft::$app->getPlugins()->isPluginInstalled($handle);
    }

    public static function countTableRows(string $table): int
    {
        if (!self::tableExists($table)) {
            return 0;
        }

        return (int)(new Query())->from(['{{%' . $table . '}}'])->count();
    }

    public static function menuHandleExists(string $handle): bool
    {
        return Navigation::$plugin->getMenus()->getMenuByHandle($handle) !== null;
    }

    public static function resolveLinkedElement(?int $elementId, ?string $elementType = null): array
    {
        if (!$elementId) {
            return [null, null];
        }

        $elementClass = self::resolveElementClass($elementType);
        $element = Craft::$app->getElements()->getElementById($elementId, $elementClass);

        if (!$element instanceof ElementInterface) {
            return [null, null];
        }

        return [$element->uid, get_class($element)];
    }

    /**
     * Maps short handles (e.g. Navigate's `entry`) or full class names to element classes.
     */
    public static function resolveElementClass(?string $elementType): ?string
    {
        if (!$elementType) {
            return null;
        }

        if (class_exists($elementType)) {
            return $elementType;
        }

        return match (strtolower(trim($elementType))) {
            'entry' => \craft\elements\Entry::class,
            'category' => \craft\elements\Category::class,
            'asset' => \craft\elements\Asset::class,
            'product' => class_exists(\craft\commerce\elements\Product::class)
                ? \craft\commerce\elements\Product::class
                : null,
            default => null,
        };
    }

    public static function mapFreeNavNodeType(?string $nodeType): ?string
    {
        return match ($nodeType) {
            'entry' => Entry::class,
            'category' => Category::class,
            'asset' => Asset::class,
            'product' => class_exists(\craft\commerce\elements\Product::class) ? Product::class : Custom::class,
            'custom' => Custom::class,
            'passive' => Passive::class,
            'site' => Site::class,
            default => null,
        };
    }

    /**
     * Navigate stores element links as type `element` with the Craft type in `elementType`.
     * The slideout UI uses capitalized `Url` and `Heading` values.
     */
    public static function resolveNavigateNodeType(?string $type, ?string $elementType = null): ?string
    {
        $type = strtolower(trim((string)$type));

        if ($type === 'element') {
            return self::mapNavigateNodeType($elementType);
        }

        return self::mapNavigateNodeType($type);
    }

    public static function mapNavigateNodeType(?string $type): ?string
    {
        return match (strtolower(trim((string)$type))) {
            'entry' => Entry::class,
            'category' => Category::class,
            'asset' => Asset::class,
            'url' => Custom::class,
            'heading' => Passive::class,
            default => null,
        };
    }

    public static function mapTkaNodeType(?string $type): ?string
    {
        return match ($type) {
            'entry' => Entry::class,
            'url', 'external' => Custom::class,
            'anchor' => Entry::class,
            default => Custom::class,
        };
    }

    public static function buildNestedTree(
        array $flat,
        callable $buildNode,
        int|string $idKey = 'id',
        int|string $parentKey = 'parentId',
    ): array {
        $indexed = [];
        $roots = [];

        foreach ($flat as $row) {
            $id = $row[$idKey];
            $indexed[$id] = $buildNode($row);
            $indexed[$id]['children'] = [];
        }

        foreach ($flat as $row) {
            $id = $row[$idKey];
            $parentId = $row[$parentKey] ?? null;

            if ($parentId && isset($indexed[$parentId])) {
                $indexed[$parentId]['children'][] = &$indexed[$id];
            } else {
                $roots[] = &$indexed[$id];
            }
        }

        return $roots;
    }

    /**
     * Olivemenus uses parent_id = 0 for root items.
     */
    public static function buildNestedTreeFromParentId(
        array $flat,
        callable $buildNode,
        int|string $idKey = 'id',
        int|string $parentKey = 'parent_id',
        int $rootParentValue = 0,
    ): array {
        $normalized = array_map(static function(array $row) use ($parentKey, $rootParentValue, $idKey) {
            if (($row[$parentKey] ?? null) == $rootParentValue) {
                $row['parentId'] = null;
            } else {
                $row['parentId'] = $row[$parentKey] ?? null;
            }

            $row['id'] = $row[$idKey];

            return $row;
        }, $flat);

        return self::buildNestedTree($normalized, $buildNode);
    }

    public static function baseExportPayload(array $menuData, array $nodes, ?string $sourceSiteHandle = null): array
    {
        $sourceSiteHandle ??= Craft::$app->getSites()->getPrimarySite()->handle;

        return [
            'exportVersion' => ImportExportHelper::EXPORT_VERSION,
            'exportedAt' => (new DateTime())->format(DateTime::ATOM),
            'sourceSiteHandle' => $sourceSiteHandle,
            'menu' => $menuData,
            'nodes' => $nodes,
        ];
    }

    public static function exportSiteSettings(array $siteSettings): array
    {
        $data = [];

        foreach ($siteSettings as $settings) {
            $siteId = is_array($settings) ? $settings['siteId'] : $settings->siteId;
            $enabled = is_array($settings) ? $settings['enabled'] : $settings->enabled;
            $site = Craft::$app->getSites()->getSiteById($siteId);

            if (!$site) {
                continue;
            }

            $data[$site->handle] = ['enabled' => (bool)$enabled];
        }

        return $data;
    }

    public static function allSitesEnabled(): array
    {
        $settings = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $settings[] = new MenuSiteSettings([
                'siteId' => (int)$site->id,
                'enabled' => true,
            ]);
        }

        return $settings;
    }

    public static function singleSiteEnabled(int $siteId): array
    {
        $settings = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $settings[] = new MenuSiteSettings([
                'siteId' => (int)$site->id,
                'enabled' => (int)$site->id === $siteId,
            ]);
        }

        return $settings;
    }

    public static function parseCustomAttributes(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        try {
            $decoded = Json::decode($value);
        } catch (\Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    public static function mergeImportResult(MigrationResult $result, MenuImportResult $import): void
    {
        foreach ($import->warnings as $warning) {
            $result->addLine(\verbb\navigation\migrations\plugins\Line::warning($warning, 1));
        }

        foreach ($import->errors as $error) {
            $result->addLine(\verbb\navigation\migrations\plugins\Line::error($error, 1));
            $result->ok = false;
            $result->incrementStat('errors');
        }

        $result->incrementStat('nodesCreated', $import->nodesCreated);
        $result->incrementStat('nodesSkipped', $import->nodesSkipped);
    }

    /**
     * Element-backed node types without a resolvable link are downgraded so import can succeed.
     */
    public static function normalizeImportedNode(array $node): array
    {
        $elementTypes = [Entry::class, Category::class, Asset::class, Product::class, Site::class];
        $type = $node['type'] ?? null;

        if (!in_array($type, $elementTypes, true) || !empty($node['linkedElementUid'])) {
            return [$node, null];
        }

        $title = (string)($node['title'] ?? 'Untitled');
        $warning = "Node “{$title}” has no linked element; converted to Custom URL.";

        $node['type'] = Custom::class;
        unset($node['linkedElementUid'], $node['linkedElementType']);

        if (empty($node['url'])) {
            $node['url'] = '#';
        }

        return [$node, $warning];
    }

    public static function finalizeNode(array $node, ?callable $onWarning = null): array
    {
        [$node, $warning] = self::normalizeImportedNode($node);

        if ($warning && $onWarning) {
            $onWarning($warning);
        }

        return $node;
    }
}
