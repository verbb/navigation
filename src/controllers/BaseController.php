<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
use verbb\navigation\migrations\AmNavPlugin;
use verbb\navigation\migrations\NaveePlugin;
use verbb\navigation\models\Settings;

use Craft;
use craft\web\Controller;

use yii\web\Response;

class BaseController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSettings(): Response
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        return $this->renderTemplate('navigation/settings', [
            'settings' => $settings,
        ]);
    }

}