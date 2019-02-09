<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\events\NavEvent;
use verbb\navigation\models\Nav as NavModel;
use verbb\navigation\records\Nav as NavRecord;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\events\ConfigEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\models\Structure;

use yii\web\UserEvent;

class Navs extends Component
{
    // Constants
    // =========================================================================

    const EVENT_BEFORE_SAVE_NAV = 'beforeSaveNav';
    const EVENT_AFTER_SAVE_NAV = 'afterSaveNav';
    const EVENT_BEFORE_DELETE_NAV = 'beforeDeleteNav';
    const EVENT_BEFORE_APPLY_NAV_DELETE = 'beforeApplyNavDelete';
    const EVENT_AFTER_DELETE_NAV = 'afterDeleteNav';

    const CONFIG_NAV_KEY = 'navigation.navs';


    // Properties
    // =========================================================================

    private $_navs;


    // Public Methods
    // =========================================================================

    public function getAllNavs(): array
    {
        if ($this->_navs !== null) {
            return $this->_navs;
        }

        $this->_navs = [];

        $navRecords = NavRecord::find()
            ->with('structure')
            ->all();

        foreach ($navRecords as $navRecord) {
            $this->_navs[] = $this->_createNavFromRecord($navRecord);
        }

        return $this->_navs;
    }

    public function getNavByHandle(string $handle)
    {
        return ArrayHelper::firstWhere($this->getAllNavs(), 'handle', $handle, true);
    }

    public function getNavById(int $id)
    {
        return ArrayHelper::firstWhere($this->getAllNavs(), 'id', $id);
    }

    public function getNavByUid(string $uid)
    {
        return ArrayHelper::firstWhere($this->getAllNavs(), 'uid', $uid, true);
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
            Craft::info('Navigation not saved due to validation error.', __METHOD__);
            return false;
        }

        if ($isNewNav) {
            $nav->uid = StringHelper::UUID();
        } else if (!$nav->uid) {
            $nav->uid = Db::uidById('{{%navigation_navs}}', $nav->id);
        }

        // If they've set maxLevels to 0 (don't ask why), then pretend like there are none.
        if ((int)$nav->maxLevels === 0) {
            $nav->maxLevels = null;
        }

        $projectConfig = Craft::$app->getProjectConfig();

        $configData = [
            'name' => $nav->name,
            'handle' => $nav->handle,
            'instructions' => $nav->instructions,
            'propagateNodes' => $nav->propagateNodes,
        ];

        if (!$nav->propagateNodes) {
            $configData['propagateNodes'] = false;
        }

        $configPath = self::CONFIG_NAV_KEY . '.' . $nav->uid;
        $projectConfig->set($configPath, $configData);

        if ($isNewNav) {
            $nav->id = Db::idByUid('{{%navigation_navs}}', $nav->uid);
        }

        return true;
    }

    public function handleChangedNav(ConfigEvent $event)
    {
        $navUid = $event->tokenMatches[0];
        $data = $event->newValue;

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            $structureData = $data['structure'];
            $siteData = $data['siteSettings'];
            $structureUid = $structureData['uid'];

            $navRecord = $this->_getNavRecord($navUid);
            $isNewNav = $navRecord->getIsNewRecord();

            $navRecord->name = $data['name'];
            $navRecord->handle = $data['handle'];
            $navRecord->instructions = $data['instructions'];
            $navRecord->propagateNodes = $data['propagateNodes'];
            // $navRecord->isArchived = false;
            // $navRecord->dateArchived = null;
            $navRecord->uid = $navUid;

            // Structure
            $structuresService = Craft::$app->getStructures();
            $structure = $structuresService->getStructureByUid($structureUid, true) ?? new Structure(['uid' => $structureUid]);
            $structure->maxLevels = $structureData['maxLevels'];
            $structuresService->saveStructure($structure);

            $navRecord->structureId = $structure->id;

            $navRecord->save(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Fire an 'afterSaveNav' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_NAV)) {
            $this->trigger(self::EVENT_AFTER_SAVE_NAV, new NavEvent([
                'nav' => $this->getNavById($navRecord->id),
                'isNew' => $isNewNav,
            ]));
        }
    }

    public function deleteNavById(int $navId): bool
    {
        $nav = $this->getNavById($navId);

        if (!$nav) {
            return false;
        }

        return $this->deleteNav($nav);
    }

    public function deleteNav($navId): bool
    {
        // Fire a 'beforeDeleteNav' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_NAV)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_NAV, new NavEvent([
                'nav' => $nav
            ]));
        }

        Craft::$app->getProjectConfig()->remove(self::CONFIG_NAV_KEY . '.' . $nav->uid);
        
        return true;
    }

    public function handleDeletedNav(ConfigEvent $event)
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
            // Delete the nodes
            $nodes = Nodes::find()
                ->anyStatus()
                ->navId($navRecord->id)
                ->all();

            $elementsService = Craft::$app->getElements();

            foreach ($nodes as $node) {
                $elementsService->deleteElement($node);
            }

            // Delete the structure
            Craft::$app->getStructures()->deleteStructureById($navRecord->structureId);

            // Delete the navigation
            Craft::$app->getDb()->createCommand()
                ->softDelete('{{%navigation_navs}}', ['id' => $navRecord->id])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Craft::$app->getDb()->createCommand()
        //     ->delete('{{%navigation_navs}}', ['uid' => $navUid])
        //     ->execute();

        // Clear caches
        $this->_navs = null;

        // Fire an 'afterDeleteNav' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_NAV)) {
            $this->trigger(self::EVENT_AFTER_DELETE_NAV, new NavEvent([
                'nav' => $nav
            ]));
        }
    }

    public function reorderNavs(array $navIds): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();

        $uidsByIds = Db::uidsByIds('{{%navigation_navs}}', $navIds);

        foreach ($navIds as $navOrder => $navId) {
            if (!empty($uidsByIds[$navId])) {
                $navUid = $uidsByIds[$navId];
                $projectConfig->set(self::CONFIG_NAV_KEY . '.' . $navUid . '.sortOrder', $navOrder + 1);
            }
        }

        return true;
    }





    // public function reorderNavs(array $navIds): bool
    // {
    //     $transaction = Craft::$app->getDb()->beginTransaction();

    //     try {
    //         foreach ($navIds as $navOrder => $navId) {
    //             $navRecord = $this->_getNavRecordById($navId);
    //             $navRecord->sortOrder = $navOrder + 1;
    //             $navRecord->save();
    //         }

    //         $transaction->commit();
    //     } catch (\Throwable $e) {
    //         $transaction->rollBack();

    //         throw $e;
    //     }

    //     return true;
    // }


    // Private Methods
    // =========================================================================

    private function _createNavFromRecord(NavRecord $record = null)
    {
        if (!$record) {
            return null;
        }

        $nav = new NavModel($record->toArray([
            'id',
            'structureId',
            'name',
            'handle',
            'instructions',
            'sortOrder',
            'propagateNodes',
            'uid',
        ]));

        if ($record->structure) {
            $nav->maxLevels = $record->structure->maxLevels;
        }

        return $nav;
    }

    private function _getNavRecord(string $uid): NavRecord
    {
        return NavRecord::findOne(['uid' => $uid]) ?? new NavRecord();
    }
    
}