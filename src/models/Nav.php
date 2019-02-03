<?php
namespace verbb\navigation\models;

use craft\base\Model;
use craft\validators\HandleValidator;

class Nav extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $name;
    public $handle;
    public $instructions;
    public $sortOrder;
    public $propagateNodes = false;
    public $maxLevels;
    public $structureId;


    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [['id', 'structureId', 'maxLevels'], 'number', 'integerOnly' => true],
            [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']],
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255],
        ];
    }

}