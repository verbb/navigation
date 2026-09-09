<?php

declare(strict_types=1);

namespace Tests\Support;

use Craft;
use craft\models\Site;
use verbb\navigation\elements\Node;
use verbb\navigation\records\Menu as MenuRecord;

class ResetTestDatabase
{
    public static function resetNavigationData(): void
    {
        $db = Craft::$app->getDb();

        if ($db->driverName === 'mysql') {
            $db->createCommand('SET FOREIGN_KEY_CHECKS = 0')->execute();
        } elseif ($db->driverName === 'sqlite') {
            $db->createCommand('PRAGMA foreign_keys = OFF')->execute();
        }

        try {
            foreach (Node::find()->status(null)->site('*')->unique()->all() as $node) {
                Craft::$app->getElements()->deleteElement($node, true);
            }

            foreach (MenuRecord::find()->all() as $nav) {
                $nav->delete();
            }
        } finally {
            if ($db->driverName === 'mysql') {
                $db->createCommand('SET FOREIGN_KEY_CHECKS = 1')->execute();
            } elseif ($db->driverName === 'sqlite') {
                $db->createCommand('PRAGMA foreign_keys = ON')->execute();
            }
        }

        self::pruneFixtureSites();
    }

    /**
     * Remove ephemeral Craft sites left by multisite fixtures so the test DB
     * does not accumulate toward Craft's site limit across suite runs.
     * Never deletes the primary site; keeps the shared navigationTestSecondary if present
     * only when it is the sole non-primary match — unique handles are always pruned.
     */
    public static function pruneFixtureSites(): void
    {
        $sitesService = Craft::$app->getSites();
        $primaryId = (int)$sitesService->getPrimarySite()->id;

        foreach ($sitesService->getAllSites(true) as $site) {
            if (!$site instanceof Site || (int)$site->id === $primaryId) {
                continue;
            }

            $handle = (string)$site->handle;

            // Shared secondary site is reused across tests — leave it.
            if ($handle === 'navigationTestSecondary') {
                continue;
            }

            $isFixture =
                str_starts_with($handle, 'navigationAutoEnableSite')
                || str_starts_with($handle, 'navigationTest');

            if (!$isFixture) {
                continue;
            }

            $sitesService->deleteSiteById((int)$site->id);
        }
    }
}
