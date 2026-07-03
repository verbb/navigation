<?php
namespace verbb\navigation\models;

use craft\base\Model;

class NodeActiveState extends Model
{
    // Properties
    // =========================================================================

    public bool $isCurrent = false;
    public bool $isActive = false;
    public bool $hasActiveChild = false;


    // Public Methods
    // =========================================================================

    public function isEmpty(): bool
    {
        return !$this->isCurrent && !$this->isActive && !$this->hasActiveChild;
    }
}
