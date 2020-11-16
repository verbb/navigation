<?php
namespace verbb\navigation\elements\db;

use verbb\navigation\models\Nav as NavModel;
use verbb\navigation\records\Nav as NavRecord;

use Craft;
use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use craft\helpers\StringHelper;

use yii\db\Connection;

class NodeQuery extends ElementQuery
{
    // Properties
    // =========================================================================

    public $id;
    public $elementId;
    public $siteId;
    public $navId;
    public $enabled = true;
    public $type;
    public $classes;
    public $customAttributes;
    public $data;
    public $urlSuffix;
    public $newWindow = false;

    public $element;
    public $handle;
    public $hasUrl;


    // Public Methods
    // =========================================================================

    public function init()
    {
        $this->withStructure = true;

        parent::init();
    }

    public function elementId($value)
    {
        $this->elementId = $value;
        return $this;
    }

    public function elementSiteId($value)
    {
        $this->slug = $value;
        return $this;
    }

    public function navId($value)
    {
        $this->navId = $value;
        return $this;
    }

    public function navHandle($value)
    {
        $this->handle = $value;
        return $this;
    }

    public function nav($value)
    {
        if ($value instanceof NavModel) {
            $this->structureId = ($value->structureId ?: false);
            $this->navId = $value->id;
        } else if ($value !== null) {
            $this->navId = (new Query())
                ->select(['id'])
                ->from(NavRecord::tableName())
                ->where(Db::parseParam('handle', $value))
                ->column();
        } else {
            $this->navId = null;
        }

        return $this;
    }

    public function type($value)
    {
        $this->type = $value;
        return $this;
    }

    public function element($value)
    {
        $this->element = $value;
        return $this;
    }

    public function handle($value)
    {
        $this->handle = $value;
        return $this;
    }

    public function hasUrl(bool $value = false)
    {
        $this->hasUrl = $value;
        return $this;
    }

    // We set the active state on each node, however it gets trickier when trying to do things like settings the active
    // state when a child is active, which involves firing off additional element queries for each node's children, 
    // which quickly blow out queries. So instead, do this when the elements are populated
    public function populate($rows)
    {
        // Let the parent class handle this like normal
        $rows = parent::populate($rows);

        // Store all processed items by their ID, we need to lookup parents later
        $processedRows = ArrayHelper::index($rows, 'id');

        foreach ($rows as $row) {
            // If the current node is active, and it has a parent, set its active state
            if (is_a($row, Node::class) && $row->active) {
                $ancestors = $row->ancestors->all();

                foreach ($ancestors as $ancestor) {
                    if (isset($processedRows[$ancestor->id])) {
                        $processedRows[$ancestor->id]->isActive = true;
                    }
                }
            }
        }

        return $rows;
    }


    // Protected Methods
    // =========================================================================

    protected function beforePrepare(): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.navigation.schemaVersion', true);

        $this->joinElementTable('navigation_nodes');
        $this->subQuery->innerJoin('{{%navigation_navs}} navigation_navs', '[[navigation_nodes.navId]] = [[navigation_navs.id]]');

        $select = [
            'navigation_nodes.id',
            'navigation_nodes.elementId',
            'navigation_nodes.navId',
            'navigation_nodes.url',
            'navigation_nodes.type',
            'navigation_nodes.classes',
            'navigation_nodes.newWindow',

            // Join the element's uri onto the same query
            'element_item_sites.uri AS elementUrl',
        ];

        // Any new columns we add should be wrapped in a conditional, otherwise migrations
        // will likely fail. This is because a Node::find() query runs when elements are saved
        // which can happen in a variety of migrations.
        if (version_compare($schemaVersion, '1.0.14', '>')) {
            $select[] = 'navigation_nodes.customAttributes';
        }

        if (version_compare($schemaVersion, '1.0.15', '>')) {
            $select[] = 'navigation_nodes.urlSuffix';
        }

        if (version_compare($schemaVersion, '1.0.16', '>')) {
            $select[] = 'navigation_nodes.data';
        }

        $this->query->select($select);

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.id', $this->id));
        }

        if ($this->elementId) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.elementId', $this->elementId));
        }

        if ($this->navId) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.navId', $this->navId));
        }

        if ($this->type) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.type', $this->type));
        }

        if ($this->classes) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.classes', $this->classes));
        }

        if ($this->urlSuffix) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.urlSuffix', $this->urlSuffix));
        }

        if ($this->customAttributes) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.customAttributes', $this->customAttributes));
        }

        if ($this->data) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.data', $this->data));
        }

        if ($this->newWindow) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.newWindow', $this->newWindow));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('navigation_navs.handle', $this->handle));
        }

        if ($this->hasUrl) {
            $this->subQuery->andWhere(['or', ['not', ['navigation_nodes.elementId' => null, 'navigation_nodes.elementId' => '']], ['not', ['navigation_nodes.url' => null, 'navigation_nodes.url' => '']]]);
        }

        return parent::beforePrepare();
    }

    protected function afterPrepare(): bool
    {
        if (Craft::$app->getDb()->getIsMysql()) {
            $sql = 'CAST([[elements_sites.slug]] AS UNSIGNED)';
        } else {
            $sql = 'CAST([[elements_sites.slug]] AS INTEGER)';
        }

        // Join the element sites table (again) for the linked element
        $this->query->leftJoin('{{%elements_sites}} element_item_sites', '[[navigation_nodes.elementId]] = [[element_item_sites.elementId]] AND ' . $sql . ' = [[element_item_sites.siteId]]');
        
        return parent::afterPrepare();
    }
}
