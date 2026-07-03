<?php
namespace verbb\navigation\elements\db;

use verbb\navigation\deprecations\NodeQueryDeprecations;
use verbb\navigation\elements\Menu;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation;

use Craft;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class NodeQuery extends ElementQuery
{
    // Traits
    // =========================================================================

    use NodeQueryDeprecations;


    // Properties
    // =========================================================================

    public mixed $id = null;
    public mixed $elementId = null;
    public mixed $siteId = null;
    public mixed $menuId = null;
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
    public bool $withLinkedElements = false;
    public ?bool $withNodeHierarchy = null;
    public bool $withMenu = false;
    public ?bool $withProjectedChildren = null;
    public bool $internalReadFetch = false;
    public bool $bypassReadCache = false;
    public bool $skipPostCacheProcessing = false;
    public bool $useNavigationCache = false;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        if (!isset($this->withStructure)) {
            $this->withStructure = true;
        }

        parent::init();
    }

    public function all($db = null): array
    {
        if (!$this->bypassReadCache && Navigation::$plugin->getNavigationCache()->shouldCacheQuery($this)) {
            return Navigation::$plugin->getNodeRead()->fetchCachedAll($this);
        }

        return parent::all($db);
    }

    public function elementId($value): static
    {
        $this->elementId = $value;
        return $this;
    }

    public function menuId($value): static
    {
        $this->menuId = $value;

        return $this;
    }

    public function menu($value): static
    {
        if ($value instanceof MenuSettings || $value instanceof Menu) {
            $this->structureId = ($value->structureId ?: false);
            $this->menuId = $value->id;
        } else if ($value !== null) {
            $this->handle($value);
        } else {
            $this->menuId = null;
            $this->handle = null;
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

    public function menuHandle($value): static
    {
        return $this->handle($value);
    }

    public function hasUrl(bool $value = false): static
    {
        $this->hasUrl = $value;
        return $this;
    }

    /**
     * Batch-hydrates linked Craft elements after the node query executes.
     */
    public function withLinkedElements(bool $value = true): static
    {
        $this->withLinkedElements = $value;
        return $this;
    }

    /**
     * Wires parent/child relationships in memory after the node query executes.
     *
     * Pass `false` to opt out. Omit the argument (default null) for smart auto on nav-scoped front-end reads.
     */
    public function withNodeHierarchy(?bool $value = true): static
    {
        $this->withNodeHierarchy = $value;
        return $this;
    }

    /**
     * Opts this query into plugin tree caching when cache mode is `manual`.
     */
    public function withNavigationCache(bool $value = true): static
    {
        $this->useNavigationCache = $value;
        return $this;
    }

    /**
     * Batch-hydrates the parent Menu element (including custom fields) after the node query executes.
     */
    public function withMenu(bool $value = true): static
    {
        $this->withMenu = $value;
        return $this;
    }

    /**
     * Wires read-time Dynamic projections into hierarchy output.
     *
     * Pass `false` to skip projected children. Omit the argument (default null) to include projections.
     */
    public function withProjectedChildren(?bool $value = true): static
    {
        $this->withProjectedChildren = $value;
        return $this;
    }

    public function shouldProjectChildren(): bool
    {
        return $this->withProjectedChildren !== false;
    }

    public function shouldWireNodeHierarchy(): bool
    {
        if ($this->withNodeHierarchy === false) {
            return false;
        }

        if ($this->withNodeHierarchy === true) {
            return true;
        }

        if ($this->internalReadFetch) {
            return false;
        }

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return false;
        }

        return (bool)($this->menuId || $this->handle);
    }

    // We set the active state on each node, however it gets trickier when trying to do things like settings the active
    // state when a child is active, which involves firing off additional element queries for each node's children, 
    // which quickly blow out queries. So instead, do this when the elements are populated
    public function populate($rows): array
    {
        $siteDataByNodeId = [];

        foreach ($rows as &$row) {
            $siteDataByNodeId[(int)$row['id']] = [
                'url' => $row['nodeSiteUrl'] ?? null,
                'urlSuffix' => $row['nodeSiteUrlSuffix'] ?? null,
                'linkedElementSiteId' => $row['nodeSiteLinkedElementSiteId'] ?? null,
                'elementUrl' => $row['elementUrl'] ?? null,
            ];

            unset(
                $row['nodeSiteUrl'],
                $row['nodeSiteUrlSuffix'],
                $row['nodeSiteLinkedElementSiteId'],
                $row['elementUrl'],
            );
        }
        unset($row);

        $rows = parent::populate($rows);

        if ($rows) {
            foreach ($rows as $node) {
                $data = $siteDataByNodeId[$node->id] ?? null;

                if (!$data) {
                    continue;
                }

                if ($data['url'] !== null) {
                    $node->setUrl($data['url']);
                }

                if ($data['urlSuffix'] !== null) {
                    $node->urlSuffix = $data['urlSuffix'];
                }

                if ($data['linkedElementSiteId']) {
                    $node->setElementSiteId($data['linkedElementSiteId']);
                }

                if ($data['elementUrl'] !== null) {
                    $node->setElementUrl($data['elementUrl']);
                }
            }
        }

        if ($this->shouldWireNodeHierarchy()) {
            $rows = Navigation::$plugin->getNodeRead()->assembleNodeHierarchyForQueryResult(
                $this,
                $rows,
                $this->withLinkedElements,
                $this->shouldProjectChildren(),
            );
        } elseif ($this->withLinkedElements) {
            Navigation::$plugin->getNodeRead()->eagerLoadLinkedElements($rows);
        }

        if ($this->withMenu) {
            Navigation::$plugin->getNodeRead()->eagerLoadMenus($rows);
        }

        if (!$this->skipPostCacheProcessing && !Craft::$app->getRequest()->getIsCpRequest()) {
            Navigation::$plugin->getActiveMatcher()->resolve($rows);

            if ($this->shouldProjectChildren()) {
                Navigation::$plugin->getActiveMatcher()->resolveProjections($rows);
            }
        }

        return $rows;
    }


    // Protected Methods
    // =========================================================================

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('navigation_nodes');
        $this->subQuery->innerJoin('{{%navigation_menus}} navigation_menus', '[[navigation_nodes.menuId]] = [[navigation_menus.id]]');

        $this->query->select([
            'navigation_nodes.id',
            'navigation_nodes.elementId',
            'navigation_nodes.menuId',
            'navigation_nodes.type',
            'navigation_nodes.classes',
            'navigation_nodes.newWindow',
            'navigation_nodes.customAttributes',
            'navigation_nodes.data',
            'node_sites.url AS nodeSiteUrl',
            'node_sites.urlSuffix AS nodeSiteUrlSuffix',
            'node_sites.linkedElementSiteId AS nodeSiteLinkedElementSiteId',
            'element_item_sites.uri AS elementUrl',
        ]);

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.id', $this->id));
        }

        if ($this->elementId) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.elementId', $this->elementId));
        }

        if ($this->menuId) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.menuId', $this->menuId));
        }

        if ($this->type) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.type', $this->type));
        }

        if ($this->classes) {
            $this->subQuery->andWhere(Db::parseParam('navigation_nodes.classes', $this->classes));
        }

        if ($this->urlSuffix) {
            $this->subQuery->andWhere(Db::parseParam('node_sites.urlSuffix', $this->urlSuffix));
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
            $this->subQuery->andWhere(Db::parseParam('navigation_menus.handle', $this->handle));
        }

        if ($this->hasUrl) {
            $this->subQuery->andWhere(['or',
                ['not', ['navigation_nodes.elementId' => null]],
                ['not', ['node_sites.url' => null]],
                ['not', ['node_sites.url' => '']],
            ]);
        }

        $siteId = $this->_resolveSiteId();

        $this->subQuery->leftJoin(
            '{{%navigation_nodes_sites}} node_sites',
            '[[node_sites.nodeId]] = [[navigation_nodes.id]] AND [[node_sites.siteId]] = ' . (int)$siteId,
        );

        $this->query->leftJoin(
            '{{%navigation_nodes_sites}} node_sites',
            '[[node_sites.nodeId]] = [[navigation_nodes.id]] AND [[node_sites.siteId]] = ' . (int)$siteId,
        );

        return parent::beforePrepare();
    }

    protected function afterPrepare(): bool
    {
        $siteId = $this->_resolveSiteId();

        $this->query->leftJoin(
            '{{%elements_sites}} element_item_sites',
            '[[navigation_nodes.elementId]] = [[element_item_sites.elementId]] AND [[element_item_sites.siteId]] = COALESCE([[node_sites.linkedElementSiteId]], ' . (int)$siteId . ')',
        );

        return parent::afterPrepare();
    }

    protected function _resolveSiteId(): int
    {
        if ($this->siteId !== null && $this->siteId !== '*') {
            if (is_array($this->siteId)) {
                return (int)reset($this->siteId);
            }

            return (int)$this->siteId;
        }

        return Craft::$app->getSites()->getCurrentSite()->id;
    }
}
