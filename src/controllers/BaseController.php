<?php
namespace verbb\navigation\controllers;

use Craft;
use craft\web\Controller;

use verbb\navigation\Navigation;

class BaseController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSettings()
    {
        $settings = Navigation::$plugin->getSettings();

        $this->renderTemplate('navigation/settings', array(
            'settings' => $settings,
        ));
    }

}