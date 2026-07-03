<?php
namespace verbb\navigation\models;

use craft\base\Model;

class MenuImportResult extends Model
{
    // Properties
    // =========================================================================

    public array $warnings = [];
    public array $errors = [];
    public int $nodesCreated = 0;
    public int $nodesSkipped = 0;
    public ?MenuSettings $menu = null;


    // Public Methods
    // =========================================================================

    public function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    public function addImportError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function hasImportErrors(): bool
    {
        return $this->errors !== [];
    }
}
