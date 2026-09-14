<?php

use craft\db\Query;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\migrations\m260627_000000_nodes_sites_and_menu_elements;

it('resumes a partial legacy site backfill without replacing completed site rows', function() {
    $secondary = (int)F::existingSecondarySite()->id;
    $menu = F::menu();
    $node = F::customNode($menu, 'Legacy cross-site link', '/new-url');
    $primary = (int)$node->siteId;
    $db = Craft::$app->getDb();
    $db->createCommand()->update('{{%navigation_nodes}}', ['url' => '/legacy-url', 'urlSuffix' => '?legacy=1'], ['id' => $node->id])->execute();
    $db->createCommand()->update('{{%elements_sites}}', ['slug' => (string)$primary], ['elementId' => $node->id, 'siteId' => $secondary])->execute();
    $db->createCommand()->delete('{{%navigation_nodes_sites}}', ['nodeId' => $node->id, 'siteId' => $secondary])->execute();
    $rows = fn() => (new Query())->from('{{%navigation_nodes_sites}}')->where(['nodeId' => $node->id])->orderBy('siteId')->all();
    $existing = $rows();
    expect($existing)->toHaveCount(1);
    $migration = new m260627_000000_nodes_sites_and_menu_elements();
    $backfill = new ReflectionMethod($migration, '_migrateNodeSiteRows');
    $backfill->invoke($migration);
    $complete = $rows();
    expect($complete)->toHaveCount(2);
    expect($complete[0])->toBe($existing[0]);
    expect($complete[1]['url'])->toBe('/legacy-url');
    expect($complete[1]['urlSuffix'])->toBe('?legacy=1');
    expect((int)$complete[1]['linkedElementSiteId'])->toBe($primary);
    $backfill->invoke($migration);
    expect($rows())->toBe($complete);
});
