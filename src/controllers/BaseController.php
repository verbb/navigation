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

    public function actionAmNavMigrate(): Response
    {
        // Backup!
        Craft::$app->getDb()->backup();

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();
        $request = Craft::$app->getRequest();

        $migration = new AmNavPlugin();
        $migration->propagate = (bool)$request->getParam('propagate', true);
        $migration->assignToDefaultSite = (bool)$request->getParam('assignToDefaultSite', false);

        ob_start();
        $migration->up();
        $output = ob_get_clean();

        $output = nl2br($output);

        Craft::$app->getSession()->setNotice(Craft::t('navigation', 'A&M Nav migrated.'));

        return $this->renderTemplate('navigation/settings', [
            'output' => $output,
            'settings' => $settings,
        ]);
    }

    public function actionNaveeMigrate(): Response
    {
        // Backup!
        Craft::$app->getDb()->backup();

        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();
        $request = Craft::$app->getRequest();

        $migration = new NaveePlugin();

        ob_start();
        $migration->up();
        $output = ob_get_clean();

        $output = nl2br($output);

        Craft::$app->getSession()->setNotice(Craft::t('navigation', 'Navee migrated.'));

        return $this->renderTemplate('navigation/settings', [
            'output' => $output,
            'settings' => $settings,
        ]);
    }

    public function actionSettings(): Response
    {
        /* @var Settings $settings */
        $settings = Navigation::$plugin->getSettings();

        return $this->renderTemplate('navigation/settings', [
            'settings' => $settings,
        ]);
    }

}