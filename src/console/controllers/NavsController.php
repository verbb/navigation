<?php
namespace verbb\navigation\console\controllers;

use Craft;
use craft\db\Query;
use craft\helpers\Db;
use craft\helpers\StringHelper;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

use DateTime;

class NavsController extends Controller
{
    // Public Methods
    // =========================================================================

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
