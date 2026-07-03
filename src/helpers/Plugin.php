<?php
namespace verbb\navigation\helpers;

use verbb\navigation\web\assets\cp\BuilderAsset;
use verbb\navigation\web\assets\cp\NavigationCpAsset;

use Craft;

class Plugin
{
    // Static Methods
    // =========================================================================

    public static function registerCpAssets(): void
    {
        Craft::$app->getView()->registerAssetBundle(NavigationCpAsset::class);
    }

    public static function registerCpBuilderAssets(): void
    {
        Craft::$app->getView()->registerAssetBundle(BuilderAsset::class);
    }
}
