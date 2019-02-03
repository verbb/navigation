<?php
namespace verbb\navigation\controllers;

use verbb\navigation\Navigation;
use verbb\navigation\migrations\AmNavPlugin;

use Craft;
use craft\web\Controller;

class BaseController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionMigrate()
    {
        // Backup!
        Craft::$app->getDb()->backup();

        $migration = new AmNavPlugin();

        ob_start();
        $migration->up();
        $output = ob_get_contents();
        ob_end_clean();

        // echo "<pre>";
        // print_r($output);
        // echo "</pre>";

        Craft::$app->getSession()->setNotice(Craft::t('navigation', 'A&M Nav migrated.'));

        return $this->redirect('navigation/settings');
    }

    public function actionSettings()
    {
        $settings = Navigation::$plugin->getSettings();

        $this->renderTemplate('navigation/settings', array(
            'settings' => $settings,
        ));
    }

}