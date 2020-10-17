<?php
namespace verbb\navigation\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $pluginName = 'Navigation';
    public $bypassProjectConfig = false;
    public $propagateSiteElements = true;

    // TODO: remove at next breakpoint
    public $disabledElements = [];

}