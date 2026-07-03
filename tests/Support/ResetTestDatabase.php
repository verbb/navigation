<?php

declare(strict_types=1);

namespace Tests\Support;

use Craft;
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
    }
}
