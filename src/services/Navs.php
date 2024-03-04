<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\events\NavEvent;
use verbb\navigation\models\Nav as NavModel;
use verbb\navigation\models\Nav_SiteSettings;
use verbb\navigation\models\Settings;
use verbb\navigation\records\Nav as NavRecord;
use verbb\navigation\records\Nav_SiteSettings as Nav_SiteSettingsRecord;

use Craft;
use craft\base\Component;
use craft\base\ElementInterface;
use craft\base\MemoizableArray;
use craft\db\Query;
use craft\db\Table;
use craft\events\ConfigEvent;
use craft\events\DeleteSiteEvent;
use craft\events\FieldEvent;
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

class Navs extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_BEFORE_SAVE_NAV = 'beforeSaveNav';
    public const EVENT_AFTER_SAVE_NAV = 'afterSaveNav';
    public const EVENT_BEFORE_APPLY_NAV_DELETE = 'beforeApplyNavDelete';
    public const EVENT_BEFORE_DELETE_NAV = 'beforeDeleteNav';
    public const EVENT_AFTER_DELETE_NAV = 'afterDeleteNav';

    public const CONFIG_NAV_KEY = 'navigation.navs';


    // Properties
    // =========================================================================
    
    public bool $autoResaveNodes = true;

    private ?MemoizableArray $_navs = null;


    // Public Methods
    // =========================================================================

    public function getAllNavs(): array
    {
        return $this->_navs()->all();
    }

    public function getEditableNavs(): array
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return $this->getAllNavs();
        }

        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return [];
        }

        return ArrayHelper::where($this->getAllNavs(), function(NavModel $nav) use ($user) {
            return $user->can("navigation-manageNav:$nav->uid");
        }, true, true, false);
    }

    public function getEditableNavsForSite($site): array
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return $this->getAllNavs();
        }

        $user = Craft::$app->getUser()->getIdentity();

        if (!$user) {
            return [];
        }

        return ArrayHelper::where($this->getAllNavs(), function(NavModel $nav) use ($user, $site) {
            return $user->can("navigation-manageNav:$nav->uid") && in_array($site->id, $nav->getSiteIds());
        }, true, true, false);
    }

    public function getEditableNavIds(): array
    {
        return ArrayHelper::getColumn($this->getEditableNavs(), 'id');
    }

    public function getNavByHandle(string $handle): ?NavModel
    {
        return $this->_navs()->firstWhere('handle', $handle, true);
    }

    public function getNavById(int $id): ?NavModel
    {
        return $this->_navs()->firstWhere('id', $id);
    }

    public function getNavByUid(string $uid): ?NavModel
    {
        return $this->_navs()->firstWhere('uid', $uid, true);
    }

    public function getNavSiteSettings(int $navId): array
    {
        $siteSettings = $this->_createNavSiteSettingsQuery()
            ->where(['navs_sites.navId' => $navId])
            ->all();

        foreach ($siteSettings as $key => $value) {
            $siteSettings[$key] = new Nav_SiteSettings($value);
        }

        return $siteSettings;
    }

    public function saveNav(NavModel $nav, bool $runValidation = true): bool
    {
        $isNewNav = !$nav->id;

        // Fire a 'beforeSaveNav' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_NAV)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_NAV, new NavEvent([
                'nav' => $nav,
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
                ->from(['{{%navigation_navs}}'])
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

            $this->handleChangedNav($event);
        } else {
            $configPath = self::CONFIG_NAV_KEY . '.' . $nav->uid;
            Craft::$app->getProjectConfig()->set($configPath, $configData, "Save navigation “{$nav->handle}”");
        }

        if ($isNewNav) {
            $nav->id = Db::idByUid('{{%navigation_navs}}', $nav->uid);
        }

        return true;
    }

    public function handleChangedNav(ConfigEvent $event): void
    {
        $navUid = $event->tokenMatches[0];
        $data = $event->newValue;

        // Make sure sites are processed
        ProjectConfigHelper::ensureAllSitesProcessed();

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            $structureData = $data['structure'];
            $siteSettingData = $data['siteSettings'] ?? [];
            $structureUid = $structureData['uid'];

            // Basic data
            $navRecord = $this->_getNavRecord($navUid, true);
            $isNewNav = $navRecord->getIsNewRecord();

            $navRecord->name = $data['name'];
            $navRecord->handle = $data['handle'];
            $navRecord->instructions = $data['instructions'];
            $navRecord->maxNodes = $data['maxNodes'] ?? '';
            $navRecord->sortOrder = $data['sortOrder'];
            $navRecord->defaultPlacement = $data['defaultPlacement'] ?? NavModel::DEFAULT_PLACEMENT_END;
            $navRecord->permissions = $data['permissions'] ?? [];

            $schemaVersion = Craft::$app->getProjectConfig()->get('plugins.navigation.schemaVersion', true);

            if (version_compare($schemaVersion, '2.0.5', '>=')) {
                $navRecord->propagationMethod = $data['propagationMethod'] ?? NavModel::PROPAGATION_METHOD_ALL;
            }

            if (version_compare($schemaVersion, '2.0.6', '>=')) {
                $navRecord->maxNodesSettings = $data['maxNodesSettings'] ?? [];
            }

            $navRecord->uid = $navUid;
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

            // Update the site settings
            // -----------------------------------------------------------------

            if (!$isNewNav) {
                // Get the old nav site settings
                $allOldSiteSettingsRecords = Nav_SiteSettingsRecord::find()
                    ->where(['navId' => $navRecord->id])
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
                    $siteSettingsRecord = new Nav_SiteSettingsRecord();
                    $siteSettingsRecord->navId = $navRecord->id;
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
                            'nav' => $navRecord->name,
                        ]),
                        'elementType' => Node::class,
                        'criteria' => [
                            'navId' => $navRecord->id,
                            'structureId' => $navRecord->structureId,
                        ],
                    ]));
                } else if ($this->autoResaveNodes) {
                    Queue::push(new ResaveElements([
                        'description' => Translation::prep('app', 'Resaving {nav} nodes', [
                            'nav' => $navRecord->name,
                        ]),
                        'elementType' => Node::class,
                        'criteria' => [
                            'navId' => $navRecord->id,
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
        $this->_navs = null;

        if ($wasTrashed) {
            // Restore the nodes that were deleted with the nav
            $nodes = Node::find()
                ->navId($navRecord->id)
                ->drafts(null)
                ->draftOf(false)
                ->status(null)
                ->trashed()
                ->site('*')
                ->unique()
                ->andWhere(['navigation_nodes.deletedWithNav' => true])
                ->all();

            Craft::$app->getElements()->restoreElements($nodes);
        }

        $nav = $this->getNavById($navRecord->id);

        // Fire an 'afterSaveNav' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_NAV)) {
            $this->trigger(self::EVENT_AFTER_SAVE_NAV, new NavEvent([
                'nav' => $nav,
                'isNew' => $isNewNav,
            ]));
        }

        // Invalidate node caches
        Craft::$app->getElements()->invalidateCachesForElementType(Node::class);
    }

    public function deleteNavById(int $navId): bool
    {
        $nav = $this->getNavById($navId);

        if (!$nav) {
            return false;
        }

        return $this->deleteNav($nav);
    }

    public function deleteNav(NavModel $nav): bool
    {
        // Fire a 'beforeDeleteNav' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_NAV)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_NAV, new NavEvent([
                'nav' => $nav,
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

            $this->handleDeletedNav($event);
        } else {
            Craft::$app->getProjectConfig()->remove(self::CONFIG_NAV_KEY . '.' . $nav->uid);
        }

        return true;
    }

    public function handleDeletedNav(ConfigEvent $event): void
    {
        $uid = $event->tokenMatches[0];
        $navRecord = $this->_getNavRecord($uid);

        if (!$navRecord->id) {
            return;
        }

        $nav = $this->getNavById($navRecord->id);

        // Fire a 'beforeApplyNavDelete' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_APPLY_NAV_DELETE)) {
            $this->trigger(self::EVENT_BEFORE_APPLY_NAV_DELETE, new NavEvent([
                'nav' => $nav,
            ]));
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // All nodes *should* be deleted by now via their nav, but loop through all the sites in case
            // there are any lingering entries from unsupported sites
            $nodeQuery = Node::find()
                ->navId($navRecord->id)
                ->status(null);
            
            $elementsService = Craft::$app->getElements();
            
            foreach (Craft::$app->getSites()->getAllSiteIds() as $siteId) {
                foreach (Db::each($nodeQuery->siteId($siteId)) as $node) {
                    $node->deletedWithNav = true;
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
                ->softDelete('{{%navigation_navs}}', ['id' => $navRecord->id])
                ->execute();

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Clear caches
        $this->_navs = null;

        // Fire an 'afterDeleteNav' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_NAV)) {
            $this->trigger(self::EVENT_AFTER_DELETE_NAV, new NavEvent([
                'nav' => $nav,
            ]));
        }

        // Invalidate node caches
        Craft::$app->getElements()->invalidateCachesForElementType(Node::class);
    }

    public function pruneDeletedSite(DeleteSiteEvent $event): void
    {
        $siteUid = $event->site->uid;

        $projectConfig = Craft::$app->getProjectConfig();
        $navs = $projectConfig->get(self::CONFIG_NAV_KEY);

        // Loop through the navs and prune the UID from field layouts.
        if (is_array($navs)) {
            foreach ($navs as $navUid => $nav) {
                $projectConfig->remove(self::CONFIG_NAV_KEY . '.' . $navUid . '.siteSettings.' . $siteUid, 'Prune deleted site settings');
            }
        }
    }

    public function pruneDeletedField(FieldEvent $event): void
    {
        $field = $event->field;
        $fieldUid = $field->uid;

        $projectConfig = Craft::$app->getProjectConfig();
        $navs = $projectConfig->get(self::CONFIG_NAV_KEY);

        // Engage stealth mode
        $projectConfig->muteEvents = true;

        // Loop through the navs and prune the UID from field layouts.
        if (is_array($navs)) {
            foreach ($navs as $navUid => $nav) {
                if (!empty($nav['fieldLayouts'])) {
                    foreach ($nav['fieldLayouts'] as $layoutUid => $layout) {
                        if (!empty($layout['tabs'])) {
                            foreach ($layout['tabs'] as $tabUid => $tab) {
                                $projectConfig->remove(self::CONFIG_NAV_KEY . '.' . $navUid . '.fieldLayouts.' . $layoutUid . '.tabs.' . $tabUid . '.fields.' . $fieldUid, 'Prune deleted field');
                            }
                        }
                    }
                }
            }
        }

        // Allow events again
        $projectConfig->muteEvents = false;
    }

    public function reorderNavs(array $navIds): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();

        $uidsByIds = Db::uidsByIds('{{%navigation_navs}}', $navIds);

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        foreach ($navIds as $navOrder => $navId) {
            if (!empty($uidsByIds[$navId])) {
                $navUid = $uidsByIds[$navId];

                // There's some edge-cases where devs know what they're doing.
                // See https://github.com/verbb/navigation/issues/88
                if ($settings->bypassProjectConfig && !Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
                    $configData = $this->getNavById($navId)->getConfig();
                    $configData['sortOrder'] = $navOrder + 1;

                    $event = new ConfigEvent([
                        'tokenMatches' => [$navUid],
                        'newValue' => $configData,
                    ]);

                    $this->handleChangedNav($event);
                } else {
                    $projectConfig->set(self::CONFIG_NAV_KEY . '.' . $navUid . '.sortOrder', $navOrder + 1);
                }
            }
        }

        return true;
    }

    public function buildNavTree($nodes, &$nodeTree): void
    {
        foreach ($nodes as $key => $node) {
            $nodeTree[$key] = $node->toArray();
            $nodeTree[$key]['active'] = $node->getActive();
            $nodeTree[$key]['target'] = $node->getTarget();
            $nodeTree[$key]['element'] = $node->getElement() ? $node->getElement()->toArray() : null;

            if ($children = $node->children->all()) {
                $this->buildNavTree($children, $nodeTree[$key]['children']);
            }
        }
    }

    public function getBuilderTabs($nav): array
    {
        $tabs = [];

        $registeredElements = Navigation::$plugin->getElements()->getRegisteredElements();
        $registeredNodeTypes = Navigation::$plugin->getNodeTypes()->getRegisteredNodeTypes();

        foreach ($registeredElements as $registeredElement) {
            $enabled = $nav->permissions[$registeredElement['type']]['enabled'] ?? $registeredElement['default'] ?? false;
            $permissions = $nav->permissions[$registeredElement['type']]['permissions'] ?? '*';

            if ($enabled) {
                $key = StringHelper::toKebabCase($registeredElement['label']);

                $registeredElement['category'] = 'element';
                $registeredElement['sources'] = $permissions;

                $tabs[$key] = $registeredElement;
            }
        }

        foreach ($registeredNodeTypes as $nodeType) {
            $enabled = $nav->permissions[get_class($nodeType)]['enabled'] ?? true;

            if ($enabled) {
                $key = StringHelper::toKebabCase($nodeType->displayName());

                $tabs[$key] = [
                    'label' => $nodeType->displayName(),
                    'button' => Craft::t('navigation', 'Add {name}', ['name' => $nodeType->displayName()]),
                    'type' => get_class($nodeType),
                    'category' => 'nodeType',
                    'nodeType' => $nodeType,
                ];
            }
        }

        return $tabs;
    }


    // Private Methods
    // =========================================================================

    private function _navs(): MemoizableArray
    {
        if (!isset($this->_navs)) {
            $navs = [];

            foreach ($this->_createNavQuery()->all() as $result) {
                $navs[] = new NavModel($result);
            }

            $this->_navs = new MemoizableArray($navs);

            if (!empty($navs) && Craft::$app->getRequest()->getIsCpRequest()) {
                // Eager load the site settings
                $allSiteSettings = $this->_createNavSiteSettingsQuery()
                    ->where(['navs_sites.navId' => array_keys($navs)])
                    ->all();

                $siteSettingsBySection = [];

                foreach ($allSiteSettings as $siteSettings) {
                    $siteSettingsByNav[$siteSettings['navId']][] = new Nav_SiteSettings($siteSettings);
                }

                foreach ($siteSettingsBySection as $navId => $navSiteSettings) {
                    $navs[$navId]->setSiteSettings($navSiteSettings);
                }
            }
        }

        return $this->_navs;
    }

    private function _createNavQuery(): Query
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
            ->from(['navs' => '{{%navigation_navs}}'])
            ->where(['navs.dateDeleted' => null])
            ->orderBy(['sortOrder' => SORT_ASC]);

            $schemaVersion = Craft::$app->getProjectConfig()->get('plugins.navigation.schemaVersion');

            if (version_compare($schemaVersion, '2.0.5', '>=')) {
                $query->addSelect('navs.propagationMethod');
            }

            if (version_compare($schemaVersion, '2.0.6', '>=')) {
                $query->addSelect('navs.maxNodesSettings');
            }

        return $query;
    }

    private function _createNavSiteSettingsQuery(): Query
    {
        return (new Query())
            ->select([
                'navs_sites.id',
                'navs_sites.navId',
                'navs_sites.siteId',
                'navs_sites.enabled',
            ])
            ->from(['navs_sites' => '{{%navigation_navs_sites}}'])
            ->innerJoin(['sites' => Table::SITES], '[[sites.id]] = [[navs_sites.siteId]]')
            ->orderBy(['sites.sortOrder' => SORT_ASC]);
    }

    private function _getNavRecord(string $uid, bool $withTrashed = false): ActiveRecord|array
    {
        $query = $withTrashed ? NavRecord::findWithTrashed() : NavRecord::find();
        $query->andWhere(['uid' => $uid]);
        return $query->one() ?? new NavRecord();
    }
}
