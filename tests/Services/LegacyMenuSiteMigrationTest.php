<?php

use craft\db\Query;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;

it('does not create foreign node site rows while migrating colliding legacy menus', function() {
    $secondary = F::existingSecondarySite();
    $owner = F::menu(null, 'none');
    $node = F::customNode($owner, 'Primary only', '/primary-only');
    $menu = F::menu();
    $db = Craft::$app->getDb();
    $transaction = $db->beginTransaction();

    try {
        $row = (new Query())->from('{{%navigation_menus}}')->where(['id' => $menu->id])->one();
        $row['id'] = $node->id;
        $db->createCommand()->insert('{{%navigation_menus}}', $row)->execute();
        $db->createCommand()->update('{{%navigation_menus_sites}}', ['menuId' => $node->id], ['menuId' => $menu->id])->execute();
        $db->createCommand()->delete('{{%navigation_menus}}', ['id' => $menu->id])->execute();
        $db->createCommand()->delete('{{%elements}}', ['id' => $menu->id])->execute();

        $siteRows = fn() => (int)(new Query())->from('{{%elements_sites}}')->where(['elementId' => $node->id, 'siteId' => $secondary->id])->count();
        expect($siteRows())->toBe(0);
        $migration = new verbb\navigation\migrations\m260627_000000_nodes_sites_and_menu_elements();
        (new ReflectionMethod($migration, '_migrateMenuElements'))->invoke($migration);
        expect($siteRows())->toBe(0);
        expect((new Query())->select('type')->from('{{%elements}}')->where(['id' => $node->id])->scalar())->toBe(Node::class);
    } finally {
        $transaction->rollBack();
    }
});
