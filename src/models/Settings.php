<?php
namespace verbb\navigation\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public string $pluginName = 'Navigation';
    public bool $bypassProjectConfig = false;
    public bool $propagateSiteElements = true;

    // TODO: remove at next breakpoint
    public array $disabledElements = [];

}