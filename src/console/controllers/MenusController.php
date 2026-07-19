<?php
namespace verbb\navigation\console\controllers;

use verbb\navigation\helpers\MenuElementCollisionRepair;

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
class MenusController extends Controller
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
            ->from('{{%navigation_menus}}')
            ->all();

        foreach ($navs as $nav) {
            $navSite = (new Query())
                ->select(['*'])
                ->from('{{%navigation_menus_sites}}')
                ->where(['menuId' => $nav['id']])
                ->all();

            if (!$navSite) {
                foreach (Craft::$app->getSites()->getAllSites() as $site) {
                    Db::insert('{{%navigation_menus_sites}}', [
                        'menuId' => $nav['id'],
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

    /**
     * One-off repair for beta sites where Menu elements reclaimed Entry/User/Node ids.
     *
     * Not applied automatically — only the few beta installs that hit m260627 collisions need it.
     */
    public function actionFixMenuElementCollisions(): int
    {
        $this->stdout("Repairing Menu element id collisions…\n", Console::FG_YELLOW);

        $result = MenuElementCollisionRepair::run();

        $this->stdout("Restored foreign elements mistyped as Menu: {$result['restoredForeign']}\n");
        $this->stdout("Remapped colliding menus onto exclusive elements: {$result['remappedMenus']}\n");
        $this->stdout("Backfilled missing Menu elements: {$result['backfilledMenus']}\n");
        $this->stdout("Restored empty linked node titles from entries: {$result['restoredNodeTitles']}\n");
        $this->stdout("Done.\n", Console::FG_GREEN);

        return ExitCode::OK;
    }
}
