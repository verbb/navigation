<?php
namespace verbb\navigation\elements\db;

use verbb\navigation\elements\Node;
use verbb\navigation\models\Nav as NavModel;

use Craft;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;

class NodeQuery extends ElementQuery
{
    // Properties
    // =========================================================================

    public mixed $id = null;
    public mixed $elementId = null;
    public mixed $siteId = null;
    public mixed $navId = null;
    public mixed $enabled = true;
    public mixed $type = null;
    public mixed $classes = null;
    public mixed $customAttributes = null;
    public mixed $data = null;
    public mixed $urlSuffix = null;
    public mixed $newWindow = false;
    public mixed $element = null;
    public mixed $handle = null;
    public mixed $hasUrl = false;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        if (!isset($this->withStructure)) {
            $this->withStructure = true;
        }

        parent::init();
    }

    public function elementId($value): static
    {
        $this->elementId = $value;
        return $this;
    }

    public function elementSiteId($value): static
    {
        $this->slug = $value;
        return $this;
    }

    public function navId($value): static
    {
        $this->navId = $value;
        return $this;
    }

    public function navHandle($value): static
    {
        $this->handle = $value;
        return $this;
    }

    public function nav($value): static
    {
        if ($value instanceof NavModel) {
            $this->structureId = ($value->structureId ?: false);
            $this->navId = $value->id;
        } else if ($value !== null) {
            $this->navId = (new Query())
                ->select(['id'])
                ->from('{{%navigation_navs}}')
                ->where(Db::parseParam('handle', $value))
                ->column();
        } else {
            $this->navId = null;
        }

        return $this;
    }

    public function type($value): static
    {
        $this->type = $value;
        return $this;
    }

    public function element($value): static
    {
        $this->element = $value;
        return $this;
    }

    public function handle($value): static
    {
        $this->handle = $value;
        return $this;
    }

    public function hasUrl(bool $value = false): static
    {
        $this->hasUrl = $value;
        return $this;
    }

    // We set the active state on each node, however it gets trickier when trying to do things like settings the active
    // state when a child is active, which involves firing off additional element queries for each node's children, 
    // which quickly blow out queries. So instead, do this when the elements are populated
    public function populate($rows): array
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
        $this->joinElementTable('navigation_nodes');
        $this->subQuery->innerJoin('{{%navigation_navs}} navigation_navs', '[[navigation_nodes.navId]] = [[navigation_navs.id]]');

        $this->query->select([
            'navigation_nodes.id',
            'navigation_nodes.elementId',
            'navigation_nodes.navId',
            'navigation_nodes.url',
            'navigation_nodes.type',
            'navigation_nodes.classes',
            'navigation_nodes.newWindow',
            'navigation_nodes.customAttributes',
            'navigation_nodes.urlSuffix',
            'navigation_nodes.data',

            // Join the element's uri onto the same query
            'element_item_sites.uri AS elementUrl',
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
