<?php
namespace verbb\navigation\console\controllers;

use verbb\navigation\helpers\MenuElementCollisionRepair;
use verbb\navigation\helpers\MenuSiteAssociationRepair;

use craft\console\Controller;
use craft\helpers\Console;

use yii\console\ExitCode;

/**
 * Manages Navigations.
 */
class MenusController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Repair menus with no site associations using project config or Menu element sites.
     */
    public function actionFixSites(): int
    {
        $this->stdout("Repairing missing menu site associations…\n", Console::FG_YELLOW);

        $result = MenuSiteAssociationRepair::run();

        $this->stdout("Repaired menus: {$result['repairedMenus']}\n");
        $this->stdout("Created site associations: {$result['insertedRows']}\n");
        $this->stdout("Recovered from project config: {$result['sources']['projectConfig']}\n");
        $this->stdout("Recovered from Menu elements: {$result['sources']['elements']}\n");
        $this->stdout("Recovered with the single-site fallback: {$result['sources']['singleSite']}\n");

        if ($result['unresolvedMenus'] > 0) {
            $this->stderr("Skipped ambiguous multisite menus: {$result['unresolvedMenus']}\n", Console::FG_YELLOW);
        }

        $this->stdout("Done.\n", Console::FG_GREEN);

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
