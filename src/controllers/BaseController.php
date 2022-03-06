<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
use verbb\navigation\migrations\AmNavPlugin;
use verbb\navigation\migrations\NaveePlugin;

use Craft;
use craft\web\Controller;

use yii\web\Response;

class BaseController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionAmNavMigrate(): \yii\web\Response
    {
        // Backup!
        Craft::$app->getDb()->backup();

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

    public function actionNaveeMigrate(): \yii\web\Response
    {
        // Backup!
        Craft::$app->getDb()->backup();

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

    public function actionSettings(): \yii\web\Response
    {
        $settings = Navigation::$plugin->getSettings();

        return $this->renderTemplate('navigation/settings', [
            'settings' => $settings,
        ]);
    }

}