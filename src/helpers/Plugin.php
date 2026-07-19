<?php
namespace verbb\navigation\helpers;

use verbb\navigation\Navigation;
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

        self::registerAsset('src/main.tsx');
    }

    public static function registerAsset(string $path): void
    {
        $viteService = Navigation::$plugin->getVite();

        $scriptOptions = [
            'depends' => [
                BuilderAsset::class,
            ],
            'onload' => '',
        ];

        $styleOptions = [
            'depends' => [
                BuilderAsset::class,
            ],
        ];

        $viteService->register($path, false, $scriptOptions, $styleOptions);

        if ($viteService->devServerRunning()) {
            $viteService->register('@vite/client', false);
        }
    }

    public static function isDebug(): bool
    {
        return Navigation::$plugin->getVite()->devServerRunning();
    }
}
