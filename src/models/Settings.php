<?php
namespace verbb\navigation\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $pluginName = 'Navigation';
    public $disabledElements = [];
    public $bypassProjectConfig = false;

}