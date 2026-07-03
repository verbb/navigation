<?php
namespace verbb\navigation\models;

use craft\base\Model;
use craft\validators\SiteIdValidator;

class BuildSession extends Model
{
    // Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $menuId = null;
    public ?int $siteId = null;
    public ?int $userId = null;
    public array $structureMoves = [];
    public array $addedNodeIds = [];
    public array $stagedDeletes = [];
    public ?int $menuDraftId = null;
    public array $menuContentDraft = [];
    public array $nodeDraftMap = [];
    public ?string $uid = null;


    // Public Methods
    // =========================================================================

    public function getChangeCount(bool $includeStructure = true): int
    {
        $count = count($this->addedNodeIds) + count($this->stagedDeletes);

        if ($includeStructure && $this->structureMoves !== []) {
            $count++;
        }

        return $count;
    }

    public function hasStructureMoves(): bool
    {
        return $this->structureMoves !== [];
    }


    // Protected Methods
    // =========================================================================

    protected function defineRules(): array
    {
        $rules = parent::defineRules();
        $rules[] = [['id', 'menuId', 'siteId', 'userId', 'menuDraftId'], 'integer'];
        $rules[] = [['siteId'], SiteIdValidator::class];

        return $rules;
    }
}
