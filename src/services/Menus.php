<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\deprecations\MenusDeprecations;
use verbb\navigation\deprecations\MenusLegacyConstants;
use verbb\navigation\elements\Menu;
use verbb\navigation\elements\Node;
use verbb\navigation\events\MenuEvent;
use verbb\navigation\helpers\ElementPickerHelper;
use verbb\navigation\helpers\MenuContentFieldLayout;
use verbb\navigation\helpers\MenuPermissions;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\models\MenuSiteSettings;
use verbb\navigation\models\Settings;
use verbb\navigation\records\Menu as MenuRecord;
use verbb\navigation\records\MenuSiteSettings as MenuSiteSettingsRecord;

use Craft;
use craft\base\Component;
use craft\base\MemoizableArray;
use craft\db\Query;
use craft\db\Table;
use craft\events\ConfigEvent;
use craft\events\DeleteSiteEvent;
use craft\events\FieldEvent;
use craft\events\SiteEvent;
use craft\base\Field;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\helpers\Queue;
use craft\helpers\StringHelper;
use craft\i18n\Translation;
use craft\models\FieldLayout;
use craft\models\Structure;
use craft\queue\jobs\ApplyNewPropagationMethod;
use craft\queue\jobs\ResaveElements;

use Throwable;

use yii\db\ActiveRecord;

class Menus extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_SAVE_MENU = 'beforeSaveMenu';
    public const EVENT_AFTER_SAVE_MENU = 'afterSaveMenu';
    public const EVENT_BEFORE_APPLY_MENU_DELETE = 'beforeApplyMenuDelete';
    public const EVENT_BEFORE_DELETE_MENU = 'beforeDeleteMenu';
    public const EVENT_AFTER_DELETE_MENU = 'afterDeleteMenu';

    public const CONFIG_MENU_KEY = 'navigation.menus';


    // Traits
    // =========================================================================

    use MenusDeprecations;
    use MenusLegacyConstants;


    // Properties
    // =========================================================================

    public bool $autoResaveNodes = true;

    private ?MemoizableArray $_menus = null;


    // Public Methods
    // =========================================================================

    public function getAllMenus(): array
    {
        return $this->_menus()->all();
    }

    public function getEditableMenus(): array
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return $this->getAllMenus();
        }

        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return [];
        }

        return ArrayHelper::where($this->getAllMenus(), function(MenuSettings $nav) use ($user) {
            return $user->can("navigation-manageMenu:$nav->uid");
        }, true, true, false);
    }

    public function getEditableMenusForSite($site): array
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return $this->getAllMenus();
        }

        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return [];
        }

        return ArrayHelper::where($this->getAllMenus(), function(MenuSettings $nav) use ($user, $site) {
            return $user->can("navigation-manageMenu:$nav->uid") && in_array($site->id, $nav->getSiteIds());
        }, true, true, false);
    }

    public function getEditableMenuIds(): array
    {
        return ArrayHelper::getColumn($this->getEditableMenus(), 'id');
    }

    public function getMenuByHandle(string $handle): ?MenuSettings
    {
        return $this->_menus()->firstWhere('handle', $handle, true);
    }

    public function getMenuById(int $id): ?MenuSettings
    {
        return $this->_menus()->firstWhere('id', $id);
    }

    public function getMenuByUid(string $uid): ?MenuSettings
    {
        return $this->_menus()->firstWhere('uid', $uid, true);
    }

    public function getMenuSiteSettings(int $menuId): array
    {
        $siteSettings = $this->_createMenuSiteSettingsQuery()
            ->where(['menus_sites.menuId' => $menuId])
            ->all();

        foreach ($siteSettings as $key => $value) {
            $siteSettings[$key] = new MenuSiteSettings($value);
        }

        return $siteSettings;
    }

    public function saveMenu(MenuSettings $nav, bool $runValidation = true): bool
    {
        $isNewNav = !$nav->id;

        // Fire a 'beforeSaveMenu' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_MENU)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_MENU, new MenuEvent([
                'menu' => $nav,
                'isNew' => $isNewNav,
            ]));
        }

        if ($runValidation && !$nav->validate()) {
            Navigation::info('Navigation not saved due to validation error.');
            return false;
        }

        if ($isNewNav) {
            $nav->uid = StringHelper::UUID();

            $nav->sortOrder = (new Query())
                ->from(['{{%navigation_menus}}'])
                ->max('[[sortOrder]]') + 1;
        }

        // If they've set maxLevels to 0 (don't ask why), then pretend like there are none.
        if ((int)$nav->maxLevels === 0) {
            $nav->maxLevels = null;
        }

        $configData = $nav->getConfig();

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        // There's some edge-cases where devs know what they're doing.
        // See https://github.com/verbb/navigation/issues/88
        if ($settings->bypassProjectConfig && !Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $event = new ConfigEvent([
                'tokenMatches' => [$nav->uid],
                'newValue' => $configData,
            ]);

            $this->handleChangedMenu($event);
        } else {
            $configPath = self::CONFIG_MENU_KEY . '.' . $nav->uid;
            Craft::$app->getProjectConfig()->set($configPath, $configData, "Save navigation “{$nav->handle}”");
        }

        if ($isNewNav) {
            $nav->id = Db::idByUid('{{%navigation_menus}}', $nav->uid);
        }

        return true;
    }

    public function saveMenuContentFromRequest(int $menuId, ?int $siteId = null): bool
    {
        $request = Craft::$app->getRequest();

        if ($request->getBodyParam('fieldsLocation') === null) {
            return true;
        }

        $siteId ??= (int)($request->getBodyParam('siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        $menuElement = Menu::find()->id($menuId)->siteId($siteId)->status(null)->one();

        if (!$menuElement) {
            Craft::error("Menu content save failed: menu element not found for menu {$menuId}, site {$siteId}.", __METHOD__);

            return false;
        }

        $menuElement->siteId = $siteId;

        if (!MenuContentFieldLayout::hasCustomFields(MenuContentFieldLayout::withoutTitle($menuElement->getFieldLayout()))) {
            return true;
        }

        $nav = $this->getMenuById($menuId);

        if ($nav) {
            // Menu name is owned by menu settings, not menu content fields.
            $menuElement->title = $nav->name;
        }

        if ($menuElement->menuFieldLayoutId) {
            $menuElement->fieldLayoutId = $menuElement->menuFieldLayoutId;
        }

        $fieldsLocation = $request->getParam('fieldsLocation', 'fields');
        $menuElement->setFieldValuesFromRequest($fieldsLocation);
        $menuElement->setScenario(Menu::SCENARIO_LIVE);

        if (!Craft::$app->getElements()->saveElement($menuElement)) {
            Craft::error('Menu content save failed: ' . json_encode($menuElement->getErrors()), __METHOD__);

            return false;
        }

        Craft::$app->getElements()->invalidateCachesForElement($menuElement);

        return true;
    }

    public function saveMenuContentFromDraft(int $menuId, int $siteId, array $fieldValues): bool
    {
        if ($fieldValues === []) {
            return true;
        }

        $menuElement = Menu::find()->id($menuId)->siteId($siteId)->status(null)->one();

        if (!$menuElement) {
            Craft::error("Menu content save failed: menu element not found for menu {$menuId}, site {$siteId}.", __METHOD__);

            return false;
        }

        $menuElement->siteId = $siteId;

        if (!MenuContentFieldLayout::hasCustomFields(MenuContentFieldLayout::withoutTitle($menuElement->getFieldLayout()))) {
            return true;
        }

        $nav = $this->getMenuById($menuId);

        if ($nav) {
            $menuElement->title = $nav->name;
        }

        if ($menuElement->menuFieldLayoutId) {
            $menuElement->fieldLayoutId = $menuElement->menuFieldLayoutId;
        }

        $menuElement->setFieldValues($fieldValues);
        $menuElement->setScenario(Menu::SCENARIO_LIVE);

        if (!Craft::$app->getElements()->saveElement($menuElement)) {
            Craft::error('Menu content save failed: ' . json_encode($menuElement->getErrors()), __METHOD__);

            return false;
        }

        Craft::$app->getElements()->invalidateCachesForElement($menuElement);

        return true;
    }

    public function getMenuContentTabsForBuilder(int $menuId, int $siteId, array $draftValues = []): array
    {
        $menuElement = Menu::find()->id($menuId)->siteId($siteId)->status(null)->one();

        if (!$menuElement) {
            return [];
        }

        if ($draftValues !== []) {
            Navigation::$plugin->getBuilderState()->applyMenuContentDraft($menuElement, $draftValues);
        }

        $fieldLayout = MenuContentFieldLayout::withoutTitle($menuElement->getFieldLayout());

        if (!$fieldLayout || !MenuContentFieldLayout::hasCustomFields($fieldLayout)) {
            return [];
        }

        return MenuContentFieldLayout::getBuilderFormTabs($fieldLayout, $menuElement);
    }

    public function handleChangedMenu(ConfigEvent $event): void
    {
        $menuUid = $event->tokenMatches[0];
        $data = $event->newValue;

        // Make sure fields and sites are processed
        ProjectConfigHelper::ensureAllSitesProcessed();
        ProjectConfigHelper::ensureAllFieldsProcessed();

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $structureData = $data['structure'];
            $siteSettingData = $data['siteSettings'] ?? [];
            $structureUid = $structureData['uid'];

            // Basic data
            $navRecord = $this->_getMenuRecord($menuUid, true);
            $isNewNav = $navRecord->getIsNewRecord();

            $navRecord->name = $data['name'];
            $navRecord->handle = $data['handle'];
            $navRecord->instructions = $data['instructions'];
            $navRecord->maxNodes = $data['maxNodes'] ?? '';
            $navRecord->sortOrder = $data['sortOrder'];
            $navRecord->defaultPlacement = $data['defaultPlacement'] ?? MenuSettings::DEFAULT_PLACEMENT_END;
            $navRecord->permissions = MenuPermissions::normalize($data['permissions'] ?? []);

            $schemaVersion = Craft::$app->getProjectConfig()->get('plugins.navigation.schemaVersion', true);

            if (version_compare($schemaVersion, '2.0.5', '>=')) {
                $navRecord->propagationMethod = $data['propagationMethod'] ?? MenuSettings::PROPAGATION_METHOD_ALL;
            }

            if (version_compare($schemaVersion, '2.0.6', '>=')) {
                $navRecord->maxNodesSettings = $data['maxNodesSettings'] ?? [];
            }

            if (version_compare($schemaVersion, '2.1.2', '>=')) {
                $navRecord->showSiteMenu = $data['showSiteMenu'] ?? true;
            }

            $navRecord->titleTranslationMethod = $data['titleTranslationMethod'] ?? Field::TRANSLATION_METHOD_SITE;
            $navRecord->titleTranslationKeyFormat = $data['titleTranslationKeyFormat'] ?? null;

            $navRecord->uid = $menuUid;
            $propagationMethodChanged = false;

            if (version_compare($schemaVersion, '2.0.5', '>=')) {
                $propagationMethodChanged = $navRecord->propagationMethod != $navRecord->getOldAttribute('propagationMethod');
            }

            // Structure
            $structuresService = Craft::$app->getStructures();
            $structure = $structuresService->getStructureByUid($structureUid, true) ?? new Structure(['uid' => $structureUid]);
            $structure->maxLevels = $structureData['maxLevels'];
            $structuresService->saveStructure($structure);

            $navRecord->structureId = $structure->id;

            // Save the field layout
            if (!empty($data['fieldLayouts'])) {
                // Save the field layout
                $layout = FieldLayout::createFromConfig(reset($data['fieldLayouts']));
                $layout->id = $navRecord->fieldLayoutId;
                $layout->type = Node::class;
                $layout->uid = key($data['fieldLayouts']);
                
                Craft::$app->getFields()->saveLayout($layout, false);
                
                $navRecord->fieldLayoutId = $layout->id;
            } else if ($navRecord->fieldLayoutId) {
                // Delete the field layout
                Craft::$app->getFields()->deleteLayoutById($navRecord->fieldLayoutId);
                
                $navRecord->fieldLayoutId = null;
            }

            if (!empty($data['menuFieldLayouts'])) {
                $menuLayoutConfig = MenuContentFieldLayout::stripTitleFromConfig(reset($data['menuFieldLayouts']));
                $menuLayout = FieldLayout::createFromConfig($menuLayoutConfig);
                $menuLayout->id = $navRecord->menuFieldLayoutId;
                $menuLayout->type = Menu::class;
                $menuLayout->uid = key($data['menuFieldLayouts']);

                Craft::$app->getFields()->saveLayout($menuLayout, false);

                $navRecord->menuFieldLayoutId = $menuLayout->id;
            } else if ($navRecord->menuFieldLayoutId) {
                Craft::$app->getFields()->deleteLayoutById($navRecord->menuFieldLayoutId);

                $navRecord->menuFieldLayoutId = null;
            }

            if ($navRecord->menuFieldLayoutId) {
                Craft::$app->getDb()->createCommand()
                    ->update('{{%elements}}', ['fieldLayoutId' => $navRecord->menuFieldLayoutId], ['id' => $navRecord->id])
                    ->execute();
            } else {
                Craft::$app->getDb()->createCommand()
                    ->update('{{%elements}}', ['fieldLayoutId' => null], ['id' => $navRecord->id])
                    ->execute();
            }

            $resaveNodes = (
                $navRecord->handle !== $navRecord->getOldAttribute('handle') ||
                $propagationMethodChanged ||
                $navRecord->fieldLayoutId != $navRecord->getOldAttribute('fieldLayoutId') ||
                $navRecord->structureId != $navRecord->getOldAttribute('structureId')
            );

            if ($wasTrashed = (bool)$navRecord->dateDeleted) {
                $navRecord->restore();

                $resaveNodes = true;
            } else {
                $navRecord->save(false);
            }

            $this->_syncMenuElement($navRecord, $siteSettingData);

            // Update the site settings
            // -----------------------------------------------------------------

            if (!$isNewNav) {
                // Get the old nav site settings
                $allOldSiteSettingsRecords = MenuSiteSettingsRecord::find()
                    ->where(['menuId' => $navRecord->id])
                    ->indexBy('siteId')
                    ->all();
            } else {
                $allOldSiteSettingsRecords = [];
            }

            $siteIdMap = Db::idsByUids(Table::SITES, array_keys($siteSettingData));
            $hasNewSite = false;

            foreach ($siteSettingData as $siteUid => $siteSettings) {
                $siteId = $siteIdMap[$siteUid] ?? null;

                // In case there's site data for a site no longer there. Legacy data that should be removed
                if (!$siteId || !$siteSettings) {
                    continue;
                }

                // Was this already selected?
                if (!$isNewNav && isset($allOldSiteSettingsRecords[$siteId])) {
                    $siteSettingsRecord = $allOldSiteSettingsRecords[$siteId];
                } else {
                    $siteSettingsRecord = new MenuSiteSettingsRecord();
                    $siteSettingsRecord->menuId = $navRecord->id;
                    $siteSettingsRecord->siteId = $siteId;
                    $resaveNodes = true;
                    $hasNewSite = true;
                }

                $siteSettingsRecord->enabled = $siteSettings['enabled'];

                $siteSettingsRecord->save(false);
            }

            if (!$isNewNav) {
                // Drop any sites that are no longer being used, as well as the associated node/element site rows
                $affectedSiteUids = array_keys($siteSettingData);

                foreach ($allOldSiteSettingsRecords as $siteId => $siteSettingsRecord) {
                    $siteUid = array_search($siteId, $siteIdMap, false);

                    if (!in_array($siteUid, $affectedSiteUids, false)) {
                        $siteSettingsRecord->delete();
                        $resaveNodes = true;
                    }
                }
            }

            if (!$isNewNav && $resaveNodes) {
                // If the propagation method just changed, we definitely need to update nodes for that
                if ($propagationMethodChanged) {
                    Queue::push(new ApplyNewPropagationMethod([
                        'description' => Translation::prep('app', 'Applying new propagation method to {nav} nodes', [
                            'menu' => $navRecord->name,
                        ]),
                        'elementType' => Node::class,
                        'criteria' => [
                            'menuId' => $navRecord->id,
                            'structureId' => $navRecord->structureId,
                        ],
                    ]));
                } else if ($this->autoResaveNodes) {
                    Queue::push(new ResaveElements([
                        'description' => Translation::prep('app', 'Resaving {nav} nodes', [
                            'menu' => $navRecord->name,
                        ]),
                        'elementType' => Node::class,
                        'criteria' => [
                            'menuId' => $navRecord->id,
                            'siteId' => array_values($siteIdMap),
                            'preferSites' => [Craft::$app->getSites()->getPrimarySite()->id],
                            'unique' => true,
                            'status' => null,
                            'drafts' => null,
                            'provisionalDrafts' => null,
                            'revisions' => null,
                        ],
                        'updateSearchIndex' => $hasNewSite,
                    ]));
                }
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->_menus = null;

        if ($wasTrashed) {
            $this->_restoreNodesDeletedWithMenu((int)$navRecord->id);
        }

        $nav = $this->getMenuById($navRecord->id);

        // Fire an 'afterSaveMenu' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_MENU)) {
            $this->trigger(self::EVENT_AFTER_SAVE_MENU, new MenuEvent([
                'menu' => $nav,
                'isNew' => $isNewNav,
            ]));
        }

        // Invalidate node caches
        Craft::$app->getElements()->invalidateCachesForElementType(Node::class);
        Navigation::$plugin->getNavigationCache()->invalidateMenu($nav->uid);
    }

    public function deleteMenuById(int $menuId): bool
    {
        $nav = $this->getMenuById($menuId);

        if (!$nav) {
            return false;
        }

        return $this->deleteMenu($nav);
    }

    public function deleteMenu(MenuSettings $nav): bool
    {
        // Fire a 'beforeDeleteMenu' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_MENU)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_MENU, new MenuEvent([
                'menu' => $nav,
            ]));
        }

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        // There's some edge-cases where devs know what they're doing.
        // See https://github.com/verbb/navigation/issues/88
        if ($settings->bypassProjectConfig && !Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
            $event = new ConfigEvent([
                'tokenMatches' => [$nav->uid],
            ]);

            $this->handleDeletedMenu($event);
        } else {
            Craft::$app->getProjectConfig()->remove(self::CONFIG_MENU_KEY . '.' . $nav->uid);
        }

        return true;
    }

    public function handleDeletedMenu(ConfigEvent $event): void
    {
        $uid = $event->tokenMatches[0];
        $navRecord = $this->_getMenuRecord($uid);

        if (!$navRecord->id) {
            return;
        }

        $nav = $this->getMenuById($navRecord->id);

        // Fire a 'beforeApplyMenuDelete' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_MENU_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_MENU_DELETE, new MenuEvent([
                'menu' => $nav,
            ]));
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // All nodes *should* be deleted by now via their nav, but loop through all the sites in case
            // there are any lingering entries from unsupported sites
            $nodeQuery = Node::find()
                ->menuId($navRecord->id)
                ->status(null);
            
            $elementsService = Craft::$app->getElements();
            
            foreach (Craft::$app->getSites()->getAllSiteIds() as $siteId) {
                foreach (Db::each($nodeQuery->siteId($siteId)) as $node) {
                    $node->deletedWithMenu = true;
                    $elementsService->deleteElement($node);
                }
            }

            // Delete the structure
            if ($navRecord->structureId) {
                Craft::$app->getStructures()->deleteStructureById($navRecord->structureId);
            }

            // Delete the field layout.
            if ($navRecord->fieldLayoutId) {
                Craft::$app->getFields()->deleteLayoutById($navRecord->fieldLayoutId);
            }

            // Delete the navigation
            Craft::$app->getDb()->createCommand()
                ->softDelete('{{%navigation_menus}}', ['id' => $navRecord->id])
                ->execute();

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->_menus = null;

        // Fire an 'afterDeleteMenu' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_MENU)) {
            $this->trigger(self::EVENT_AFTER_DELETE_MENU, new MenuEvent([
                'menu' => $nav,
            ]));
        }

        // Invalidate node caches
        Craft::$app->getElements()->invalidateCachesForElementType(Node::class);
        Navigation::$plugin->getNavigationCache()->invalidateMenu($nav->uid);
    }

    public function afterSaveSite(SiteEvent $event): void
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        if (!$settings->autoEnableNewSites || !$event->isNew) {
            return;
        }

        foreach ($this->getAllMenus() as $nav) {
            $siteSettings = $nav->getSiteSettings();

            if (isset($siteSettings[$event->site->id])) {
                continue;
            }

            $config = $nav->getConfig();
            $config['siteSettings'][$event->site->uid] = ['enabled' => true];
            Craft::$app->getProjectConfig()->set(self::CONFIG_MENU_KEY . '.' . $nav->uid, $config);
        }
    }

    public function pruneDeletedSite(DeleteSiteEvent $event): void
    {
        $siteUid = $event->site->uid;

        $projectConfig = Craft::$app->getProjectConfig();
        $navs = $projectConfig->get(self::CONFIG_MENU_KEY);

        // Loop through the navs and prune the UID from field layouts.
        if (is_array($navs)) {
            foreach ($navs as $menuUid => $nav) {
                $projectConfig->remove(self::CONFIG_MENU_KEY . '.' . $menuUid . '.siteSettings.' . $siteUid, 'Prune deleted site settings');
            }
        }
    }

    public function pruneDeletedField(FieldEvent $event): void
    {
        $field = $event->field;
        $fieldUid = $field->uid;

        $projectConfig = Craft::$app->getProjectConfig();
        $navs = $projectConfig->get(self::CONFIG_MENU_KEY);

        // Engage stealth mode
        $projectConfig->muteEvents = true;

        // Loop through the navs and prune the UID from field layouts.
        if (is_array($navs)) {
            foreach ($navs as $menuUid => $nav) {
                if (!empty($nav['fieldLayouts'])) {
                    foreach ($nav['fieldLayouts'] as $layoutUid => $layout) {
                        if (!empty($layout['tabs'])) {
                            foreach ($layout['tabs'] as $tabUid => $tab) {
                                $projectConfig->remove(self::CONFIG_MENU_KEY . '.' . $menuUid . '.fieldLayouts.' . $layoutUid . '.tabs.' . $tabUid . '.fields.' . $fieldUid, 'Prune deleted field');
                            }
                        }
                    }
                }
            }
        }

        // Allow events again
        $projectConfig->muteEvents = false;
    }

    public function reorderMenus(array $navIds): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();

        $uidsByIds = Db::uidsByIds('{{%navigation_menus}}', $navIds);

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        foreach ($navIds as $navOrder => $menuId) {
            if (!empty($uidsByIds[$menuId])) {
                $menuUid = $uidsByIds[$menuId];

                // There's some edge-cases where devs know what they're doing.
                // See https://github.com/verbb/navigation/issues/88
                if ($settings->bypassProjectConfig && !Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
                    $configData = $this->getMenuById($menuId)->getConfig();
                    $configData['sortOrder'] = $navOrder + 1;

                    $event = new ConfigEvent([
                        'tokenMatches' => [$menuUid],
                        'newValue' => $configData,
                    ]);

                    $this->handleChangedMenu($event);
                } else {
                    $projectConfig->set(self::CONFIG_MENU_KEY . '.' . $menuUid . '.sortOrder', $navOrder + 1);
                }
            }
        }

        return true;
    }

    public function getBuilderTabs($nav): array
    {
        $tabs = [];
        $permissions = MenuPermissions::normalize($nav->permissions ?? []);

        $registeredNodeTypes = Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes();

        foreach ($registeredNodeTypes as $nodeType) {
            $typeClass = $nodeType::class;

            if (!MenuPermissions::isTypeEnabled($permissions, $typeClass, $nodeType->getPermissionEnabledDefault())) {
                continue;
            }

            $key = StringHelper::toKebabCase($nodeType->displayName());

            if ($nodeType instanceof \verbb\navigation\base\ElementNodeType) {
                $config = $nodeType::getBuilderConfig();
                $elementType = $nodeType::getElementType();
                $configuredSources = MenuPermissions::getTypeSources($permissions, $typeClass);

                $tabs[$key] = [
                    'label' => $config['label'],
                    'button' => $config['button'],
                    'type' => $typeClass,
                    'elementType' => $elementType,
                    'category' => 'element',
                    'sources' => ElementPickerHelper::filterSourcesForUser($elementType, $configuredSources),
                    'pickerConfig' => ElementPickerHelper::getPickerConfig($permissions, $typeClass, $elementType),
                ];
                continue;
            }

            $tabs[$key] = [
                'label' => $nodeType->displayName(),
                'button' => Craft::t('navigation', 'Add {name}', [
                    'name' => mb_strtolower($nodeType->displayName()),
                ]),
                'type' => $typeClass,
                'category' => 'nodeType',
                'nodeType' => $nodeType,
            ];
        }

        return $tabs;
    }


    // Private Methods
    // =========================================================================

    private function _menus(): MemoizableArray
    {
        if (!isset($this->_menus)) {
            $menus = [];

            foreach ($this->_createMenuQuery()->all() as $result) {
                $menu = new MenuSettings($result);
                $menu->permissions = MenuPermissions::normalize($menu->permissions ?? []);
                $menus[$menu->id] = $menu;
            }

            $this->_menus = new MemoizableArray($menus);

            if (!empty($menus) && Craft::$app->getRequest()->getIsCpRequest()) {
                $allSiteSettings = $this->_createMenuSiteSettingsQuery()
                    ->where(['menus_sites.menuId' => array_keys($menus)])
                    ->all();

                $siteSettingsByMenu = [];

                foreach ($allSiteSettings as $siteSettings) {
                    $siteSettingsByMenu[$siteSettings['menuId']][] = new MenuSiteSettings($siteSettings);
                }

                foreach ($siteSettingsByMenu as $menuId => $menuSiteSettings) {
                    $menus[$menuId]->setSiteSettings($menuSiteSettings);
                }
            }
        }

        return $this->_menus;
    }

    private function _createMenuQuery(): Query
    {
        $query = (new Query())
            ->select([
                'navs.id',
                'navs.structureId',
                'navs.fieldLayoutId',
                'navs.name',
                'navs.handle',
                'navs.instructions',
                'navs.sortOrder',
                'navs.maxNodes',
                'navs.defaultPlacement',
                'navs.permissions',
                'navs.uid',
                'structures.maxLevels',
            ])
            ->leftJoin(['structures' => '{{%structures}}'], [
                'and',
                '[[structures.id]] = [[navs.structureId]]',
                ['structures.dateDeleted' => null],
            ])
            ->from(['navs' => '{{%navigation_menus}}'])
            ->where(['navs.dateDeleted' => null])
            ->orderBy(['sortOrder' => SORT_ASC]);

            $schemaVersion = Craft::$app->getProjectConfig()->get('plugins.navigation.schemaVersion');

            if (version_compare($schemaVersion, '2.0.5', '>=')) {
                $query->addSelect('navs.propagationMethod');
            }

            if (version_compare($schemaVersion, '2.0.6', '>=')) {
                $query->addSelect('navs.maxNodesSettings');
            }

            if (version_compare($schemaVersion, '2.1.2', '>=')) {
                $query->addSelect('navs.showSiteMenu');
            }

            if (version_compare($schemaVersion, '3.0.0', '>=')) {
                $query->addSelect('navs.menuFieldLayoutId');
            }

            if (version_compare($schemaVersion, '4.0.0', '>=')) {
                $query->addSelect(['navs.titleTranslationMethod', 'navs.titleTranslationKeyFormat']);
            }

        return $query;
    }

    private function _createMenuSiteSettingsQuery(): Query
    {
        return (new Query())
            ->select([
                'menus_sites.id',
                'menus_sites.menuId',
                'menus_sites.siteId',
                'menus_sites.enabled',
            ])
            ->from(['menus_sites' => '{{%navigation_menus_sites}}'])
            ->innerJoin(['sites' => Table::SITES], '[[sites.id]] = [[menus_sites.siteId]]')
            ->orderBy(['sites.sortOrder' => SORT_ASC]);
    }

    private function _getMenuRecord(string $uid, bool $withTrashed = false): ActiveRecord|array
    {
        $query = $withTrashed ? MenuRecord::findWithTrashed() : MenuRecord::find();
        $query->andWhere(['uid' => $uid]);
        return $query->one() ?? new MenuRecord();
    }

    /**
     * Restore nodes soft-deleted with a menu, parent-before-child, so structure placement succeeds.
     *
     * @see https://github.com/verbb/navigation/issues/415
     */
    private function _restoreNodesDeletedWithMenu(int $menuId): void
    {
        $rows = (new Query())
            ->select(['id', 'parentId'])
            ->from(['{{%navigation_nodes}}'])
            ->where(['menuId' => $menuId])
            ->all();

        if ($rows === []) {
            return;
        }

        $rowsById = [];

        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $rowsById[$id] = [
                'id' => $id,
                'parentId' => !empty($row['parentId']) ? (int)$row['parentId'] : null,
            ];
        }

        $depths = [];
        $resolveDepth = function(int $id) use (&$resolveDepth, &$depths, $rowsById): int {
            if (isset($depths[$id])) {
                return $depths[$id];
            }

            $parentId = $rowsById[$id]['parentId'] ?? null;

            if (!$parentId || !isset($rowsById[$parentId])) {
                return $depths[$id] = 0;
            }

            return $depths[$id] = $resolveDepth($parentId) + 1;
        };

        foreach (array_keys($rowsById) as $nodeId) {
            $resolveDepth($nodeId);
        }

        uasort(
            $rowsById,
            static fn(array $a, array $b): int => ($depths[$a['id']] ?? 0) <=> ($depths[$b['id']] ?? 0),
        );

        $elementsService = Craft::$app->getElements();

        foreach ($rowsById as $row) {
            $node = Node::find()
                ->id($row['id'])
                ->site('*')
                ->unique()
                ->status(null)
                ->trashed()
                ->andWhere(['navigation_nodes.deletedWithMenu' => true])
                ->one();

            if (!$node) {
                continue;
            }

            $elementsService->restoreElement($node);
        }
    }

    private function _syncMenuElement(MenuRecord $navRecord, array $siteSettingData): void
    {
        $db = Craft::$app->getDb();

        $exists = (new Query())
            ->from(['{{%elements}}'])
            ->where(['id' => $navRecord->id])
            ->exists();

        if (!$exists) {
            $now = Db::prepareDateForDb(new \DateTime());

            $db->createCommand()->insert('{{%elements}}', [
                'id' => $navRecord->id,
                'canonicalId' => $navRecord->id,
                'draftId' => null,
                'revisionId' => null,
                'fieldLayoutId' => null,
                'type' => Menu::class,
                'enabled' => true,
                'archived' => false,
                'dateCreated' => $now,
                'dateUpdated' => $now,
                'dateDeleted' => null,
                'deletedWithOwner' => null,
                'uid' => $navRecord->uid,
            ])->execute();
        }

        $siteIdMap = Db::idsByUids(Table::SITES, array_keys($siteSettingData));

        foreach ($siteSettingData as $siteUid => $siteSettings) {
            $siteId = $siteIdMap[$siteUid] ?? null;

            if (!$siteId || !$siteSettings) {
                continue;
            }

            $siteRowExists = (new Query())
                ->from(['{{%elements_sites}}'])
                ->where(['elementId' => $navRecord->id, 'siteId' => $siteId])
                ->exists();

            if ($siteRowExists) {
                continue;
            }

            $now = Db::prepareDateForDb(new \DateTime());

            $db->createCommand()->insert('{{%elements_sites}}', [
                'elementId' => $navRecord->id,
                'siteId' => $siteId,
                'slug' => null,
                'uri' => null,
                'enabled' => (bool)($siteSettings['enabled'] ?? true),
                'dateCreated' => $now,
                'dateUpdated' => $now,
                'uid' => StringHelper::UUID(),
            ])->execute();
        }

        $menu = Menu::find()->id($navRecord->id)->status(null)->one();

        if ($menu) {
            $menu->title = $navRecord->name;
            $menu->handle = $navRecord->handle;
            Craft::$app->getElements()->saveElement($menu, false);
        }
    }
}
