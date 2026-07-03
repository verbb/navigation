<?php
namespace verbb\navigation\deprecations;

trait SettingsDeprecations
{
    // Public Methods
    // =========================================================================

    public function __construct($config = [])
    {
        unset($config['disabledElements'], $config['propagateSiteElements']);

        parent::__construct($config);
    }
}
