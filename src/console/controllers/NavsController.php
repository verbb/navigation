<?php
namespace verbb\navigation\console\controllers;

use Craft;
use craft\console\Controller;
use craft\db\Query;
use craft\helpers\Console;
use craft\helpers\Db;
use craft\helpers\StringHelper;

use yii\console\ExitCode;

use DateTime;

/**
 * Manages Navigations.
 */
class NavsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Fix a Craft 3 > Craft 4 migration issue with empty sites.
     */
    public function actionFixSites(): int
    {
        $navs = (new Query())
            ->select(['*'])
            ->from('{{%navigation_navs}}')
            ->all();

        foreach ($navs as $nav) {
            $navSite = (new Query())
                ->select(['*'])
                ->from('{{%navigation_navs_sites}}')
                ->where(['navId' => $nav['id']])
                ->all();

            if (!$navSite) {
                foreach (Craft::$app->getSites()->getAllSites() as $site) {
                    Db::insert('{{%navigation_navs_sites}}', [
                        'navId' => $nav['id'],
                        'siteId' => $site->id,
                        'enabled' => true,
                        'dateCreated' => Db::prepareDateForDb(new DateTime()),
                        'dateUpdated' => Db::prepareDateForDb(new DateTime()),
                        'uid' => StringHelper::UUID(),
                    ]);
                }
            }
        }

        return ExitCode::OK;
    }
}
