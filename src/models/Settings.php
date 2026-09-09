<?php
namespace verbb\navigation\models;

use verbb\navigation\deprecations\SettingsDeprecations;
use verbb\navigation\services\NavigationCache;

use craft\base\Model;

class Settings extends Model
{
    // Traits
    // =========================================================================

    use SettingsDeprecations;


    // Properties
    // =========================================================================

    public string $pluginName = 'Navigation';
    public bool $bypassProjectConfig = false;
    public string $cacheMode = NavigationCache::MODE_AUTO;
    public string $cacheProfile = NavigationCache::PROFILE_STANDARD;
    public ?int $cacheDuration = 86400;
    public bool $autoEnableNewSites = true;
    public bool $builderLiveStructure = false;


    // Public Methods
    // =========================================================================

    public function rules(): array
    {
        return [
            [['pluginName'], 'trim'],
            [['pluginName'], 'required'],
            [['pluginName'], 'string'],
            [['bypassProjectConfig', 'autoEnableNewSites', 'builderLiveStructure'], 'boolean'],
            [['cacheMode'], 'in', 'range' => [
                NavigationCache::MODE_OFF,
                NavigationCache::MODE_AUTO,
                NavigationCache::MODE_STATIC,
                NavigationCache::MODE_MANUAL,
            ]],
            [['cacheProfile'], 'in', 'range' => [
                NavigationCache::PROFILE_LITE,
                NavigationCache::PROFILE_STANDARD,
                NavigationCache::PROFILE_FULL,
            ]],
            [['cacheDuration'], 'integer', 'min' => 60],
        ];
    }
}
