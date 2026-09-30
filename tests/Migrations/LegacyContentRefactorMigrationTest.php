<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\migrations\m231229_000000_content_refactor;
use verbb\navigation\Navigation;

it('runs the content refactor against legacy Navigation 2 table names', function() {
    F::menu();

    $db = Craft::$app->getDb();
    $migration = new m231229_000000_content_refactor();

    expect($db->tableExists('{{%content}}'))->toBeFalse();

    $migration->createTable('{{%content}}', [
        'id' => $migration->primaryKey(),
        'elementId' => $migration->integer()->notNull(),
        'siteId' => $migration->integer()->notNull(),
        'title' => $migration->string(),
    ]);
    $migration->renameTable('{{%navigation_menus}}', '{{%navigation_navs}}');
    $migration->renameColumn('{{%navigation_nodes}}', 'menuId', 'navId');
    $db->getSchema()->refresh();
    Navigation::$plugin->getMenus()->resetCache();

    try {
        expect($migration->safeUp())->toBeTrue();
    } finally {
        if ($db->tableExists('{{%content}}')) {
            $migration->dropTable('{{%content}}');
        }

        if ($db->columnExists('{{%navigation_nodes}}', 'navId')) {
            $migration->renameColumn('{{%navigation_nodes}}', 'navId', 'menuId');
        }

        if ($db->tableExists('{{%navigation_navs}}')) {
            $migration->renameTable('{{%navigation_navs}}', '{{%navigation_menus}}');
        }

        $db->getSchema()->refresh();
        Navigation::$plugin->getMenus()->resetCache();
    }
});
