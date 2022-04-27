<?php
namespace verbb\navigation\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    public string $pluginName = 'Navigation';
    public bool $bypassProjectConfig = false;
    

    // Public Methods
    // =========================================================================

    public function __construct($config = [])
    {
        // Remove deprecated settings
        unset($config['disabledElements'], $config['propagateSiteElements']);

        parent::__construct($config);
    }

}