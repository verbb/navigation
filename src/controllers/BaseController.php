<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
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
