<?php
namespace verbb\navigation\elements\db;

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
    public $elementSiteId;
    public $siteId;
    public $navId;
    public $enabled = true;
    public $type;
    public $classes;
    public $newWindow = false;

    public $element;
    public $handle;


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
        $this->elementSiteId = $value;
        return $this;
    }

    public function navId($value)
    {
        $this->navId = $value;
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
            if ($row->active) {
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
        $this->joinElementTable('navigation_nodes');
        $this->subQuery->innerJoin('{{%navigation_navs}} navigation_navs', '[[navigation_nodes.navId]] = [[navigation_navs.id]]');
            
        // Join the element sites table (again) for the linked element
        $this->query->leftJoin('{{%elements_sites}} element_item_sites', '[[navigation_nodes.elementId]] = [[element_item_sites.elementId]] AND [[navigation_nodes.elementSiteId]] = [[element_item_sites.siteId]]');

        $this->query->select([
            'navigation_nodes.id',
            'navigation_nodes.elementId',
            'navigation_nodes.elementSiteId',
            'navigation_nodes.navId',
            'navigation_nodes.url',
            'navigation_nodes.type',
            'navigation_nodes.classes',
            'navigation_nodes.newWindow',

            // Join the element's uri onto the same query
            'element_item_sites.uri AS elementUrl',
        ]);

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.id', $this->id));
        }

        if ($this->elementId) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.elementId', $this->elementId));
        }

        if ($this->elementSiteId) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.elementSiteId', $this->elementSiteId));
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

        if ($this->newWindow) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.newWindow', $this->newWindow));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('navigation_navs.handle', $this->handle));
        }

        return parent::beforePrepare();
    }
}
