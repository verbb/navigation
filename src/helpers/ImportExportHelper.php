<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Menu as MenuElement;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuImportResult;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\MenuSiteSettings;
use verbb\navigation\models\NodeSiteSettings;
use verbb\navigation\Navigation;

use Craft;
use craft\base\ElementInterface;
use craft\db\Query;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\models\Site;

use DateTime;
use Throwable;

class ImportExportHelper
{
    // Static Methods
    // =========================================================================

    public static function generateMenuExport(MenuSettings $menu, ?int $siteId = null): array
    {
        $siteId ??= (int)Craft::$app->getSites()->getPrimarySite()->id;
        $sourceSite = Craft::$app->getSites()->getSiteById($siteId);

        $payload = [
            'exportVersion' => self::EXPORT_VERSION,
            'exportedAt' => (new DateTime())->format(DateTime::ATOM),
            'sourceSiteHandle' => $sourceSite?->handle,
            'menu' => self::_exportMenuSettings($menu),
            'nodes' => self::_exportNodeTree($menu, $siteId),
        ];

        $menuFieldValues = self::_exportMenuFieldValues($menu, $siteId);

        if ($menuFieldValues !== []) {
            $payload['menuFieldValues'] = $menuFieldValues;
        }

        return $payload;
    }

    /**
     * Accepts a decoded array or JSON string.
     */
    public static function importMenuFromJson(array|string $json, string $menuAction = 'create'): MenuImportResult
    {
        $result = new MenuImportResult();

        if (is_string($json)) {
            try {
                $json = Json::decode($json);
            } catch (Throwable $e) {
                $result->addImportError('Invalid JSON: ' . $e->getMessage());

                return $result;
            }
        }

        if (isset($json[0]) && is_array($json[0])) {
            $json = $json[0];
        }

        if (!self::_validateExportPayload($json, $result)) {
            return $result;
        }

        $menuData = $json['menu'];
        $handle = (string)($menuData['handle'] ?? '');
        $existingMenu = $handle !== '' ? Navigation::$plugin->getMenus()->getMenuByHandle($handle) : null;

        if ($menuAction === 'update') {
            if (!$existingMenu) {
                $result->addImportError("No existing menu found with handle “{$handle}”.");

                return $result;
            }

            $menu = self::_createMenuFromImport($menuData, $existingMenu);
        } else {
            if ($existingMenu) {
                $menuData['handle'] = self::_uniqueMenuHandle($handle);
            }

            $menu = self::_createMenuFromImport($menuData);
        }

        if (!Navigation::$plugin->getMenus()->saveMenu($menu)) {
            foreach ($menu->getErrors() as $attribute => $errors) {
                foreach ((array)$errors as $error) {
                    $result->addImportError("Menu {$attribute}: {$error}");
                }
            }

            return $result;
        }

        $savedMenu = Navigation::$plugin->getMenus()->getMenuByHandle($menu->handle);

        if (!$savedMenu) {
            $result->addImportError('Menu saved but could not be reloaded.');

            return $result;
        }

        $result->menu = $savedMenu;

        if ($menuAction === 'update') {
            self::_deleteMenuNodes($savedMenu);
        }

        $siteId = self::_resolveSiteIdByHandle($json['sourceSiteHandle'] ?? null)
            ?? (int)Craft::$app->getSites()->getPrimarySite()->id;

        self::_importNodeTree($json['nodes'] ?? [], $savedMenu, null, $siteId, $result);
        self::_importMenuFieldValues($savedMenu, $json['menuFieldValues'] ?? [], $result);

        return $result;
    }

    public static function resolveImportFileLocation(string $filename, ?string $basePath = null): ?string
    {
        if (!preg_match(self::IMPORT_FILENAME_PATTERN, $filename)) {
            return null;
        }

        $basePath ??= Craft::$app->getPath()->getTempPath();
        $path = $basePath . DIRECTORY_SEPARATOR . $filename;

        return is_file($path) ? $path : null;
    }


    // Constants
    // =========================================================================

    public const EXPORT_VERSION = '1.0.0';

    public const IMPORT_FILENAME_PATTERN = '/^navigation-import-\d{6}_\d{6}\.json$/';


    // Private Methods
    // =========================================================================

    private static function _validateExportPayload(array $json, MenuImportResult $result): bool
    {
        $version = $json['exportVersion'] ?? $json['navigation'] ?? null;

        if (!$version) {
            $result->addImportError('Missing exportVersion.');

            return false;
        }

        if ($version !== self::EXPORT_VERSION) {
            $result->addWarning("Export version {$version} differs from supported " . self::EXPORT_VERSION . '. Import may be incomplete.');
        }

        if (empty($json['menu']) || !is_array($json['menu'])) {
            $result->addImportError('Missing menu block.');

            return false;
        }

        if (empty($json['menu']['handle'])) {
            $result->addImportError('Missing menu handle.');

            return false;
        }

        if (!isset($json['nodes']) || !is_array($json['nodes'])) {
            $result->addImportError('Missing nodes array.');

            return false;
        }

        return true;
    }

    private static function _exportMenuSettings(MenuSettings $menu): array
    {
        $data = [
            'name' => $menu->name,
            'handle' => $menu->handle,
            'instructions' => $menu->instructions,
            'propagationMethod' => $menu->propagationMethod,
            'titleTranslationMethod' => $menu->titleTranslationMethod,
            'titleTranslationKeyFormat' => $menu->titleTranslationKeyFormat,
            'defaultEnabledForPropagatedSites' => $menu->defaultEnabledForPropagatedSites,
            'maxNodes' => $menu->maxNodes,
            'maxLevels' => $menu->maxLevels,
            'maxNodesSettings' => $menu->maxNodesSettings,
            'defaultPlacement' => $menu->defaultPlacement,
            'showSiteMenu' => $menu->showSiteMenu,
            'permissions' => NodeTypeHelper::resolvePermissionsTypeKeys($menu->permissions),
            'siteSettings' => [],
        ];

        foreach ($menu->getSiteSettings() as $siteSettings) {
            $site = Craft::$app->getSites()->getSiteById($siteSettings->siteId);

            if (!$site) {
                continue;
            }

            $data['siteSettings'][$site->handle] = [
                'enabled' => (bool)$siteSettings->enabled,
            ];
        }

        return $data;
    }

    private static function _exportNodeTree(MenuSettings $menu, int $siteId): array
    {
        $nodes = Node::find()
            ->menuId($menu->id)
            ->siteId($siteId)
            ->status(null)
            ->all();

        if (!$nodes) {
            return [];
        }

        $childrenMap = [];

        foreach ($nodes as $node) {
            $parent = $node->getParent();
            $parentKey = $parent ? (int)$parent->id : 0;
            $childrenMap[$parentKey][] = $node;
        }

        return self::_exportNodeBranch($childrenMap, 0, $siteId);
    }

    private static function _exportNodeBranch(array $childrenMap, int $parentId, int $siteId): array
    {
        $branch = [];

        foreach ($childrenMap[$parentId] ?? [] as $node) {
            $branch[] = self::_exportNode($node, $siteId, $childrenMap);
        }

        return $branch;
    }

    private static function _exportNode(Node $node, int $siteId, array $childrenMap): array
    {
        $data = [
            'title' => $node->title,
            'type' => $node->type,
            'classes' => $node->classes,
            'urlSuffix' => $node->urlSuffix,
            'newWindow' => (bool)$node->newWindow,
            'customAttributes' => $node->customAttributes,
            'data' => self::_exportNodeData($node->data),
            'enabled' => (bool)$node->enabled,
            'enabledForSite' => (bool)$node->getEnabledForSite(),
            'children' => self::_exportNodeBranch($childrenMap, (int)$node->id, $siteId),
        ];

        $fieldValues = $node->getSerializedFieldValues();

        if ($fieldValues !== []) {
            $data['fieldValues'] = $fieldValues;
        }

        if ($node->elementId) {
            $element = Craft::$app->getElements()->getElementById($node->elementId);

            if ($element instanceof ElementInterface) {
                $data['linkedElementUid'] = $element->uid;
                $data['linkedElementType'] = get_class($element);
            }
        }

        if ($url = $node->getRawUrl()) {
            $data['url'] = $url;
        }

        $siteOverrides = self::_exportNodeSiteOverrides($node);

        if ($siteOverrides !== []) {
            $data['siteOverrides'] = $siteOverrides;
        }

        $linkedSite = Craft::$app->getSites()->getSiteById($node->getElementSiteId());

        if ($linkedSite) {
            $data['linkedElementSiteHandle'] = $linkedSite->handle;
        }

        return $data;
    }

    /**
     * Strip internal builder flags from exported node data.
     */
    private static function _exportNodeData(array $data): array
    {
        $keys = [
            Node::PENDING_PUBLISH_DATA_KEY,
            Node::PENDING_DELETE_DATA_KEY,
            Node::PENDING_DELETE_STATE_DATA_KEY,
            Node::PENDING_EDIT_DATA_KEY,
            Node::LINKED_ELEMENT_DISABLED_DATA_KEY,
            Node::LINKED_ELEMENT_DISABLED_STATE_DATA_KEY,
        ];

        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    private static function _exportNodeSiteOverrides(Node $node): array
    {
        if (!$node->id) {
            return [];
        }

        $overrides = [];

        foreach (Navigation::$plugin->getNodeSites()->getAllSettingsForNode($node->id) as $settings) {
            $site = Craft::$app->getSites()->getSiteById($settings->siteId);

            if (!$site || $site->id === $node->siteId) {
                continue;
            }

            $block = array_filter([
                'url' => $settings->url,
                'urlSuffix' => $settings->urlSuffix,
                'linkedElementSiteHandle' => ($settings->linkedElementSiteId
                    ? Craft::$app->getSites()->getSiteById($settings->linkedElementSiteId)?->handle
                    : null),
            ], static fn($value) => $value !== null && $value !== '');

            if ($block !== []) {
                $overrides[$site->handle] = $block;
            }
        }

        return $overrides;
    }

    private static function _exportMenuFieldValues(MenuSettings $menu, int $sourceSiteId): array
    {
        $values = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $menuElement = MenuElement::find()
                ->id($menu->id)
                ->siteId($site->id)
                ->status(null)
                ->one();

            if (!$menuElement) {
                continue;
            }

            $fieldValues = $menuElement->getSerializedFieldValues();

            if ($fieldValues === []) {
                continue;
            }

            $values[$site->handle] = $fieldValues;
        }

        if ($values === [] && $sourceSiteId) {
            return [];
        }

        return $values;
    }

    private static function _createMenuFromImport(array $data, ?MenuSettings $existingMenu = null): MenuSettings
    {
        $menu = $existingMenu ?? new MenuSettings();
        $siteSettingsData = ArrayHelper::remove($data, 'siteSettings');

        $menu->setAttributes($data, false);

        if (is_array($siteSettingsData)) {
            $siteSettings = [];

            foreach ($siteSettingsData as $siteHandle => $settings) {
                $siteId = self::_resolveSiteIdByHandle((string)$siteHandle);

                if (!$siteId) {
                    continue;
                }

                $siteSettings[] = new MenuSiteSettings([
                    'siteId' => $siteId,
                    'enabled' => (bool)($settings['enabled'] ?? true),
                ]);
            }

            if ($siteSettings !== []) {
                $menu->setSiteSettings($siteSettings);
            }
        }

        if (!$existingMenu) {
            $menu->uid = StringHelper::UUID();
        }

        return $menu;
    }

    private static function _importNodeTree(
        array $nodes,
        MenuSettings $menu,
        ?Node $parent,
        int $siteId,
        MenuImportResult $result,
    ): void {
        foreach ($nodes as $nodeData) {
            $children = ArrayHelper::remove($nodeData, 'children', []);
            $siteOverrides = ArrayHelper::remove($nodeData, 'siteOverrides', []);

            $node = self::_createNodeFromImport($nodeData, $menu, $siteId, $result);

            if (!$node) {
                $result->nodesSkipped++;

                continue;
            }

            if ($parent) {
                $node->setParentId($parent->id);
            }

            if (!Craft::$app->getElements()->saveElement($node)) {
                $result->nodesSkipped++;
                $result->addWarning("Failed saving node “{$node->title}”: " . json_encode($node->getErrors()));

                continue;
            }

            $result->nodesCreated++;
            self::_importNodeSiteOverrides($node, $siteOverrides, $result);

            if ($children !== []) {
                self::_importNodeTree($children, $menu, $node, $siteId, $result);
            }
        }
    }

    private static function _createNodeFromImport(
        array $data,
        MenuSettings $menu,
        int $siteId,
        MenuImportResult $result,
    ): ?Node {
        $type = NodeTypeHelper::resolveTypeClass($data['type'] ?? null);

        if (!$type || !class_exists($type)) {
            $result->addWarning('Skipped node with unknown type: ' . ($data['type'] ?? '(empty)'));

            return null;
        }

        $node = new Node([
            'menuId' => $menu->id,
            'siteId' => $siteId,
            'type' => $type,
            'title' => $data['title'] ?? '',
            'classes' => $data['classes'] ?? null,
            'urlSuffix' => $data['urlSuffix'] ?? null,
            'newWindow' => (bool)($data['newWindow'] ?? false),
            'customAttributes' => $data['customAttributes'] ?? [],
            'data' => $data['data'] ?? [],
            'enabled' => (bool)($data['enabled'] ?? true),
        ]);

        $node->setEnabledForSite((bool)($data['enabledForSite'] ?? true));

        if (array_key_exists('url', $data)) {
            $node->setUrl($data['url']);
        }

        if (!empty($data['fieldValues']) && is_array($data['fieldValues'])) {
            $node->setFieldValues($data['fieldValues']);
        }

        $linkedElementUid = $data['linkedElementUid'] ?? null;
        $linkedElementType = $data['linkedElementType'] ?? null;

        if ($linkedElementUid && $linkedElementType) {
            $element = Craft::$app->getElements()->getElementByUid($linkedElementUid, $linkedElementType);

            if ($element instanceof ElementInterface) {
                $node->elementId = (int)$element->id;
            } else {
                $result->addWarning("Unresolved linked element UID “{$linkedElementUid}” for node “{$node->title}”.");
            }
        }

        if (!empty($data['linkedElementSiteHandle'])) {
            $linkedSiteId = self::_resolveSiteIdByHandle($data['linkedElementSiteHandle']);

            if ($linkedSiteId) {
                $node->setElementSiteId($linkedSiteId);
            }
        }

        return $node;
    }

    private static function _importNodeSiteOverrides(Node $node, array $siteOverrides, MenuImportResult $result): void
    {
        if (!$node->id || $siteOverrides === []) {
            return;
        }

        foreach ($siteOverrides as $siteHandle => $override) {
            if (!is_array($override)) {
                continue;
            }

            $siteId = self::_resolveSiteIdByHandle((string)$siteHandle);

            if (!$siteId) {
                $result->addWarning("Skipped node site override for unknown site “{$siteHandle}”.");

                continue;
            }

            $settings = new NodeSiteSettings([
                'nodeId' => (int)$node->id,
                'siteId' => $siteId,
                'url' => $override['url'] ?? null,
                'urlSuffix' => $override['urlSuffix'] ?? null,
                'linkedElementSiteId' => !empty($override['linkedElementSiteHandle'])
                    ? self::_resolveSiteIdByHandle($override['linkedElementSiteHandle'])
                    : null,
            ]);

            Navigation::$plugin->getNodeSites()->saveSettings($settings);
        }
    }

    private static function _importMenuFieldValues(MenuSettings $menu, array $menuFieldValues, MenuImportResult $result): void
    {
        if ($menuFieldValues === [] || !$menu->id) {
            return;
        }

        foreach ($menuFieldValues as $siteHandle => $fieldValues) {
            if (!is_array($fieldValues) || $fieldValues === []) {
                continue;
            }

            $siteId = self::_resolveSiteIdByHandle((string)$siteHandle);

            if (!$siteId) {
                $result->addWarning("Skipped menu field values for unknown site “{$siteHandle}”.");

                continue;
            }

            if (!Navigation::$plugin->getMenus()->saveMenuContentFromDraft((int)$menu->id, $siteId, $fieldValues)) {
                $result->addWarning("Failed saving menu field values for site “{$siteHandle}”.");
            }
        }
    }

    private static function _deleteMenuNodes(MenuSettings $menu): void
    {
        $nodes = Node::find()
            ->menuId($menu->id)
            ->status(null)
            ->all();

        foreach ($nodes as $node) {
            Craft::$app->getElements()->deleteElement($node, true);
        }
    }

    private static function _uniqueMenuHandle(string $handle): string
    {
        $existingHandles = (new Query())
            ->select(['handle'])
            ->from(['{{%navigation_menus}}'])
            ->column();

        $unique = $handle;
        $suffix = 1;

        while (in_array($unique, $existingHandles, true)) {
            $unique = $handle . $suffix;
            $suffix++;
        }

        return $unique;
    }

    private static function _resolveSiteIdByHandle(?string $handle): ?int
    {
        $handle = trim((string)$handle);

        if ($handle === '') {
            return null;
        }

        $site = Craft::$app->getSites()->getSiteByHandle($handle);

        return $site ? (int)$site->id : null;
    }
}
