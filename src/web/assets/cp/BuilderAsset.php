<?php
namespace verbb\navigation\web\assets\cp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset as CraftCpAsset;

use verbb\base\assetbundles\CpAsset as VerbbCpAsset;

class BuilderAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init(): void
    {
        // Vite manifest entries are registered via Navigation::$plugin->getVite().
        $this->sourcePath = '@verbb/navigation/web/assets/cp/dist';

        $this->depends = [
            NavigationCpAsset::class,
            VerbbCpAsset::class,
            CraftCpAsset::class,
        ];

        parent::init();
    }
}
