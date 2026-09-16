<?php

use craft\elements\Entry;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Fixtures\PluginMigrationFixture as Source;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\Navigation as N;

function secondaryOnlyImportEntry(int $siteId): Entry
{
    $section = F::entrySection();
    $section->setSiteSettings([$section->getSiteSettings()[$siteId]]);
    expect(Craft::$app->getEntries()->saveSection($section))->toBeTrue();
    $entry = new Entry([
        'sectionId' => $section->id,
        'typeId' => $section->getEntryTypes()[0]->id,
        'siteId' => $siteId,
        'title' => 'Secondary-only linked entry',
        'slug' => 'secondary-only',
        'enabled' => true,
    ]);
    expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue(json_encode($entry->getErrors()));
    expect(Entry::find()->id($entry->id)->siteId(Craft::$app->getSites()->getPrimarySite()->id)->status(null)->one())->toBeNull();

    return $entry;
}

it('round trips a linked element that exists only on the exported site', function() {
    $site = F::secondarySite();
    $entry = secondaryOnlyImportEntry($site->id);
    $menu = F::menu();
    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => $site->id,
        'type' => verbb\navigation\nodetypes\Entry::class,
        'elementId' => $entry->id,
        'title' => $entry->title,
    ]);
    $node->setLinkedElementSiteId($site->id);
    expect(Craft::$app->getElements()->saveElement($node))->toBeTrue();
    $payload = ImportExportHelper::generateMenuExport($menu, $site->id);
    expect($payload['nodes'][0]['linkedElementUid'] ?? null)->toBe($entry->uid);
    $result = ImportExportHelper::importMenuFromJson($payload);
    expect($result->hasImportErrors())->toBeFalse(json_encode($result->errors));
    $imported = Node::find()->menuId($result->menu->id)->siteId($site->id)->one();
    expect($imported?->elementId)->toBe($entry->id);
    expect($imported?->getUrl())->toBe($entry->getUrl());
});

it('retains MenuBuilder element links when their content exists only on another site', function() {
    Source::with('menu-builder', function($fixture) {
        $entry = secondaryOnlyImportEntry($fixture['secondary']->id);
        $db = Craft::$app->getDb();
        $db->createCommand()->update('{{%menubuilder_groups}}', [
            'settings' => json_encode(['siteIds' => [$fixture['secondary']->id]]),
        ], ['handle' => $fixture['handle']])->execute();
        $db->createCommand()->update('{{%menubuilder_items}}', ['elementId' => $entry->id], ['elementId' => $fixture['linked']->id])->execute();
        $class = N::$plugin->getMigrations()->getMigratorClass('menu-builder');
        $result = N::$plugin->createMigrator($class, ['handle' => $fixture['handle']])->run();
        expect($result->ok)->toBeTrue(json_encode($result->lines));
        $menu = N::$plugin->getMenus()->getMenuByHandle($fixture['handle']);
        $node = Node::find()->menuId($menu->id)->siteId($fixture['secondary']->id)->title('Provider node 2')->one();
        expect($node?->elementId)->toBe($entry->id);
        expect($node?->getUrl())->toBe($entry->getUrl());
    });
});
