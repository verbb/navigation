<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;

use craft\elements\User;

use verbb\base\services\Templates as BaseTemplates;

class Templates extends BaseTemplates
{
    // Properties
    // =========================================================================

    public string $pluginClass = Navigation::class;
    public string|false|null $sandboxedAutoescape = false;


    // Public Methods
    // =========================================================================

    public function getSandboxedVariables(): array
    {
        return $this->getSiteTemplateVariables();
    }

    public function getDefaultSandboxedAllowedMethods(): array
    {
        return [
            User::class => ['isInGroup'],
        ] + parent::getDefaultSandboxedAllowedMethods();
    }
}
