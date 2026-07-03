<?php
namespace verbb\navigation\web\assets\cp;

use craft\web\AssetBundle;

class BuilderAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init(): void
    {
        $this->sourcePath = '@verbb/navigation/web/assets/cp/dist';

        $this->depends = [
            NavigationCpAsset::class,
        ];

        $this->js = [
            'builder.js',
        ];

        parent::init();
    }
}
