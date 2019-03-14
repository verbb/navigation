<?php
namespace verbb\navigation\models;

use verbb\navigation\records\Nav as NavRecord;

use craft\base\Model;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

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
    public $uid;


    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return $this->handle;
    }

    public function attributeLabels()
    {
        return [
            'handle' => Craft::t('app', 'Handle'),
            'name' => Craft::t('app', 'Name'),
        ];
    }

    public function rules()
    {
        return [
            [['id', 'structureId', 'maxLevels'], 'number', 'integerOnly' => true],
            [['handle'], HandleValidator::class, 'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']],
            [['handle'], UniqueValidator::class, 'targetClass' => NavRecord::class],
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255],
        ];
    }

}