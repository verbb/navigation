<?php
namespace verbb\navigation\models;

use verbb\navigation\elements\Node as NodeElement;
use verbb\navigation\models\ProjectedNode;

use craft\base\Model;

class NavigationContext extends Model
{
    // Properties
    // =========================================================================

    public string $menuHandle;
    public array $nodes = [];
    public NodeElement|ProjectedNode|null $current = null;
    public array $currentNodes = [];
    public array $activeNodes = [];


    // Public Methods
    // =========================================================================

    public function current(): NodeElement|ProjectedNode|null
    {
        return $this->current;
    }

    public function currentNodes(): array
    {
        return $this->currentNodes;
    }

    public function activeNodes(): array
    {
        return $this->activeNodes;
    }

    public function parent(): NodeElement|ProjectedNode|null
    {
        $current = $this->current();

        if (!$current) {
            return null;
        }

        if ($current instanceof ProjectedNode) {
            return $current->parent;
        }

        return $current->getParent();
    }

    public function ancestors(): array
    {
        $current = $this->current();

        if (!$current) {
            return [];
        }

        if ($current instanceof ProjectedNode) {
            $ancestors = [];

            if ($current->parent instanceof NodeElement) {
                $ancestors = array_reverse($current->parent->getAncestors()->all());
                $ancestors[] = $current->parent;
            }

            return $ancestors;
        }

        return array_reverse($current->getAncestors()->all());
    }

    public function siblings(): array
    {
        $current = $this->current();

        if (!$current) {
            return [];
        }

        if ($current instanceof ProjectedNode) {
            if ($current->parent instanceof NodeElement) {
                return $current->parent->getChildren()->all();
            }

            return [];
        }

        $parent = $current->getParent();

        if (!$parent) {
            return NodeElement::find()
                ->menuHandle($this->menuHandle)
                ->level(1)
                ->all();
        }

        return $parent->getChildren()->all();
    }

    public function children(): array
    {
        $current = $this->current();

        if (!$current) {
            return [];
        }

        if ($current instanceof ProjectedNode) {
            return $current->getChildren();
        }

        return $current->getChildren()->all();
    }

    public function branch(): array
    {
        return array_merge($this->ancestors(), $this->siblings());
    }
}
