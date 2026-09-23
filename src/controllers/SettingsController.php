<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
use verbb\navigation\models\Settings;

use Craft;

use yii\web\Response;

use verbb\base\controllers\SettingsController as BaseSettingsController;

class SettingsController extends BaseSettingsController
{
    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();
        $segment = Craft::$app->getRequest()->getSegment(3);
        $template = $segment === 'performance'
            ? 'navigation/settings/performance'
            : 'navigation/settings/index';

        return $this->renderTemplate($template, [
            'settings' => $settings,
            'selectedSubnavItem' => 'settings',
            'importError' => Craft::$app->getUrlManager()->getRouteParams()['importError'] ?? null,
            'exportError' => Craft::$app->getUrlManager()->getRouteParams()['exportError'] ?? null,
        ]);
    }
}
