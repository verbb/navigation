<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;
use craft\models\Structure;

use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\events\NavEvent;
use verbb\navigation\models\Nav as NavModel;
use verbb\navigation\records\Nav as NavRecord;

use yii\web\UserEvent;

class Navs extends Component
{
    // Constants
    // =========================================================================

    const EVENT_BEFORE_SAVE_NAV = 'beforeSaveNav';
    const EVENT_AFTER_SAVE_NAV = 'afterSaveNav';
    const EVENT_BEFORE_DELETE_NAV = 'beforeDeleteNav';
    const EVENT_AFTER_DELETE_NAV = 'afterDeleteNav';


    // Properties
    // =========================================================================

    private $_navsById;


    // Public Methods
    // =========================================================================

    public function getAllNavs($indexBy = null)
    {
        $records = NavRecord::find()
            ->indexBy($indexBy)
            ->orderBy('sortOrder asc')
            ->all();
        
        $models = [];

        foreach ($records as $record) {
            $models[] = $this->_createNavFromRecord($record);
        }

        return $models;
    }

    public function getNavById(int $navId)
    {
        $record = NavRecord::find()
            ->where(['id' => $navId])
            ->with('structure')
            ->one();

        return $this->_createNavFromRecord($record);
    }

    public function getNavByHandle($handle)
    {
        $record = NavRecord::find()
            ->where(['handle' => $handle])
            ->with('structure')
            ->one();

        return $this->_createNavFromRecord($record);
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

        if (!$isNewNav) {
            $navRecord = NavRecord::find()
                ->where(['id' => $nav->id])
                ->one();

            if (!$navRecord) {
                throw new NavNotFoundException("No navigation exists with the ID '{$nav->id}'");
            }

            $oldNav = new NavModel($navRecord->toArray([
                'id',
                'structureId',
                'name',
                'handle',
            ]));
        } else {
            $navRecord = new NavRecord();
        }

        // If they've set maxLevels to 0 (don't ask why), then pretend like there are none.
        if ((int)$nav->maxLevels === 0) {
            $nav->maxLevels = null;
        }

        $navRecord->name = $nav->name;
        $navRecord->handle = $nav->handle;
        $navRecord->propagateNodes = $nav->propagateNodes;

        if (!$nav->propagateNodes) {
            $navRecord->propagateNodes = false;
        }

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            // Create/update the structure
            if ($isNewNav) {
                $structure = new Structure();
            } else {
                $structure = Craft::$app->getStructures()->getStructureById($oldNav->structureId);
            }

            $structure->maxLevels = $nav->maxLevels;
            Craft::$app->getStructures()->saveStructure($structure);
            $navRecord->structureId = $structure->id;
            $nav->structureId = $structure->id;

            // Save the nav
            $navRecord->save(false);

            // Now that we have a nav ID, save it on the model
            if (!$nav->id) {
                $nav->id = $navRecord->id;
            }

            // Might as well update our cache of the nav while we have it.
            $this->_navsById[$nav->id] = $nav;

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Fire an 'afterSaveNav' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_NAV)) {
            $this->trigger(self::EVENT_AFTER_SAVE_NAV, new NavEvent([
                'nav' => $nav,
                'isNew' => $isNewNav,
            ]));
        }

        return true;
    }

    public function deleteNavById(int $navId): bool
    {
        $nav = $this->getNavById($navId);

        // Fire a 'beforeDeleteNav' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_NAV)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_NAV, new NavEvent([
                'nav' => $nav
            ]));
        }

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            Craft::$app->getDb()->createCommand()
                ->delete('{{%navigation_navs}}', ['id' => $navId])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Fire an 'afterDeleteAssetTransform' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_NAV)) {
            $this->trigger(self::EVENT_AFTER_DELETE_NAV, new NavEvent([
                'nav' => $nav
            ]));
        }

        return true;
    }

    public function reorderNavs(array $navIds): bool
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            foreach ($navIds as $navOrder => $navId) {
                $navRecord = $this->_getNavRecordById($navId);
                $navRecord->sortOrder = $navOrder + 1;
                $navRecord->save();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }


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
            'sortOrder',
            'propagateNodes',
        ]));

        if ($record->structure) {
            $nav->maxLevels = $record->structure->maxLevels;
        }

        return $nav;
    }

    private function _getNavRecordById(int $navId = null): NavRecord
    {
        if ($navId !== null) {
            $navRecord = NavRecord::findOne(['id' => $navId]);

            if (!$navRecord) {
                throw new NavException(Craft::t('navigation', 'No navigation exists with the ID “{id}”.', ['id' => $navId]));
            }
        } else {
            $navRecord = new NavRecord();
        }

        return $navRecord;
    }
    
}