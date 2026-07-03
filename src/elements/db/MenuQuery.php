<?php
namespace verbb\navigation\elements\db;

use Craft;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class MenuQuery extends ElementQuery
{
    // Properties
    // =========================================================================

    public mixed $handle = null;


    // Public Methods
    // =========================================================================

    public function handle($value): static
    {
        $this->handle = $value;
        return $this;
    }

    public function menuHandle($value): static
    {
        return $this->handle($value);
    }


    // Protected Methods
    // =========================================================================

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('navigation_menus');

        $this->query->select([
            'navigation_menus.id',
            'navigation_menus.structureId',
            'navigation_menus.fieldLayoutId AS nodeFieldLayoutId',
            'navigation_menus.menuFieldLayoutId',
            'navigation_menus.name AS title',
            'navigation_menus.handle',
            'navigation_menus.instructions',
            'navigation_menus.sortOrder',
            'navigation_menus.propagationMethod',
            'navigation_menus.maxNodes',
            'navigation_menus.maxNodesSettings',
            'navigation_menus.permissions',
            'navigation_menus.defaultPlacement',
            'navigation_menus.showSiteMenu',
            'navigation_menus.uid AS menuUid',
        ]);

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('navigation_menus.handle', $this->handle));
        }

        return parent::beforePrepare();
    }
}
