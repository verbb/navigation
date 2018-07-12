<?php
namespace verbb\navigation\elements\db;

use Craft;
use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
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


    // Protected Methods
    // =========================================================================

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('navigation_nodes');
        $this->subQuery->innerJoin('{{%navigation_navs}}', '[[navigation_nodes.navId]] = [[navigation_navs.id]]');

        $this->query->select([
            'navigation_nodes.*',
        ]);

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

        if ($this->newWindow) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.newWindow', $this->newWindow));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('navigation_navs.handle', $this->handle));
        }

        return parent::beforePrepare();
    }
}
