<?php

use craft\db\Query;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\Fixtures\PluginMigrationFixture as Source;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\migrations\plugins\MigrateFromMenu;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\nodetypes\Site;
use verbb\navigation\variables\NavigationVariable;

it('registers Menu migrations and copies nested localized nodes without changing source data', function() {
    Source::with('menu', function($f) {
        $service = Navigation::$plugin->getMigrations();
        expect($service->getSources()['menu']['ready'])->toBeTrue()
            ->and($service->getMigratorClass('menu'))->toBe(MigrateFromMenu::class)
            ->and(array_column($service->getSourceMenus('menu'), 'handle'))->toBe([$f['handle']])
            ->and((new NavigationVariable())->getSettingsNavItems())->toHaveKey('migrate/menu');
        $before = (new Query())->from('{{%menu_nodes}}')->orderBy('id')->all();
        $result = Navigation::$plugin->createMigrator(MigrateFromMenu::class, ['handle'=>$f['handle']])->run();
        expect($result->ok)->toBeTrue(json_encode($result->lines));
        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']);
        expect($menu->instructions)->toBe('Menu migration fixture')->and($menu->maxLevels)->toBe(3);
        $nodes = Node::find()->menuId($menu->id)->siteId($f['primary']->id)->status(null)->all();
        expect($nodes)->toHaveCount(8)
            ->and($nodes[1]->getParent()?->id)->toBe($nodes[0]->id)
            ->and($nodes[0]->getUrl())->toBe('/provider-0?source=menu')
            ->and($nodes[0]->customAttributes)->toContain(['attribute'=>'data-source', 'value'=>'menu'])
            ->and($nodes[0]->classes)->toBe('provider-class')
            ->and($nodes[1]->elementId)->toBe($f['linked']->id)
            ->and($nodes[1]->newWindow)->toBeTrue()
            ->and($nodes[2]->getEnabledForSite())->toBeFalse()
            ->and(Node::find()->id($nodes[2]->id)->siteId($f['secondary']->id)->one())->toBeNull()
            ->and($nodes[3]->type)->toBe(Passive::class)
            ->and($nodes[4]->type)->toBe(Site::class)
            ->and($nodes[4]->getUrl())->toBe(rtrim($f['secondary']->getBaseUrl(), '/') . '/')
            ->and($nodes[5]->type)->toBe(Dynamic::class)
            ->and($nodes[5]->data['sectionId'])->toBe((int)$f['linked']->sectionId)
            ->and($nodes[5]->data['limit'])->toBe(4)
            ->and($nodes[6]->type)->toBe(Custom::class)
            ->and($nodes[6]->getUrl())->toBe('/missing-fallback')
            ->and($nodes[7]->type)->toBe(Passive::class)
            ->and($nodes[7]->data['migratedMenu']['data'])->toBe(['source'=>'unknown']);
        $translated = Node::find()->id($nodes[1]->id)->siteId($f['secondary']->id)->status(null)->one();
        expect($translated->title)->toBe('Translated node 1')->and($translated->getEnabledForSite())->toBeFalse()
            ->and((new Query())->from('{{%menu_nodes}}')->orderBy('id')->all())->toBe($before);
    });
});

it('honours Menu filters skips existing destinations and suffixes conflicting handles', function() {
    Source::with('menu', function($f) {
        $filtered = Navigation::$plugin->createMigrator(MigrateFromMenu::class, ['handle'=>'notThisMenu'])->run();
        expect($filtered->ok)->toBeTrue()->and(Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']))->toBeNull();
        $existing = F::menu($f['handle']);
        $keep = F::customNode($existing, 'Keep this node', '/keep');
        $skipped = Navigation::$plugin->createMigrator(MigrateFromMenu::class, ['handle'=>$f['handle'], 'skipExisting'=>true])->run();
        expect($skipped->ok)->toBeTrue()->and($skipped->stats['menusSkipped'])->toBe(1);
        $copied = Navigation::$plugin->createMigrator(MigrateFromMenu::class, ['handle'=>$f['handle']])->run();
        expect($copied->ok)->toBeTrue(json_encode($copied->lines))
            ->and(Navigation::$plugin->getMenus()->getMenuByHandle($f['handle'] . '1'))->not->toBeNull()
            ->and(Node::find()->id($keep->id)->one()?->title)->toBe('Keep this node');
    });
});

it('imports Menu nodes restricted to a secondary site and excludes deleted menus', function() {
    Source::with('menu', function($f) {
        $db = Craft::$app->getDb();
        $db->createCommand()->delete('{{%menu_menus_sites}}', ['siteId'=>$f['primary']->id])->execute();
        $result = Navigation::$plugin->createMigrator(MigrateFromMenu::class)->run();
        expect($result->ok)->toBeTrue(json_encode($result->lines));
        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']);
        expect($menu->getSiteSettings()[$f['primary']->id]->enabled)->toBeFalse()
            ->and($menu->getSiteSettings()[$f['secondary']->id]->enabled)->toBeTrue();
        $nodes = Node::find()->menuId($menu->id)->siteId($f['secondary']->id)->status(null)->all();
        expect($nodes)->toHaveCount(8)->and($nodes[0]->title)->toBe('Translated node 0');
        $sourceId = (new Query())->select('id')->from('{{%menu_menus}}')->scalar();
        $db->createCommand()->update('{{%elements}}', ['dateDeleted'=>'2026-01-01 00:00:00'], ['id'=>$sourceId])->execute();
        expect(MigrateFromMenu::getMenus())->toBe([]);
        $empty = Navigation::$plugin->createMigrator(MigrateFromMenu::class)->run();
        expect($empty->ok)->toBeTrue()->and($empty->stats['menusMigrated'] ?? 0)->toBe(0);
    });
});

it('reports missing Menu tables without creating a destination', function() {
    expect(MigrateFromMenu::getMenus())->toBe([]);
    $result = Navigation::$plugin->createMigrator(MigrateFromMenu::class)->run();
    expect($result->ok)->toBeFalse()->and($result->stats['errors'])->toBe(1);
});

it('copies Menu custom field layouts and serialized field values', function() {
    Source::with('menu', function($f) {
        $fieldMenu = F::menu();
        $field = F::addPlainTextFieldToMenu($fieldMenu);
        $entryType = $f['parent']->getType();
        $layout = craft\models\FieldLayout::createFromConfig($fieldMenu->getFieldLayout()->getConfig());
        $layout->type = craft\elements\Entry::class;
        $layout->resetUids();
        $entryType->setFieldLayout($layout);
        expect(Craft::$app->getEntries()->saveEntryType($entryType))->toBeTrue();
        $entry = craft\elements\Entry::find()->id($f['parent']->id)->siteId($f['primary']->id)->status(null)->one();
        $entry->setFieldValue($field->handle, 'Copied source field');
        expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue();
        Craft::$app->getDb()->createCommand()->update('{{%menu_menus}}', ['nodeFieldLayoutId'=>$entryType->fieldLayoutId], ['handle'=>$f['handle']])->execute();
        $result = Navigation::$plugin->createMigrator(MigrateFromMenu::class, ['handle'=>$f['handle']])->run();
        expect($result->ok)->toBeTrue(json_encode($result->lines));
        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']);
        $node = Node::find()->menuId($menu->id)->siteId($f['primary']->id)->status(null)->one();
        expect($node->getFieldValue($field->handle))->toBe('Copied source field')
            ->and($menu->getFieldLayout()->id)->not->toBe($entryType->fieldLayoutId)
            ->and($menu->getFieldLayout()->getCustomFields()[0]->uid)->toBe($field->uid);
    });
});

it('keeps a disabled Menu disabled on every site even when its site list is unrestricted', function() {
    Source::with('menu', function($f) {
        $db = Craft::$app->getDb();
        $sourceId = (new Query())->select('id')->from('{{%menu_menus}}')->scalar();
        $db->createCommand()->update('{{%elements}}', ['enabled'=>false], ['id'=>$sourceId])->execute();
        $db->createCommand()->delete('{{%menu_menus_sites}}')->execute();
        $db->createCommand()->update('{{%menu_menus}}', ['maxLevels'=>null], ['id'=>$sourceId])->execute();
        $result = Navigation::$plugin->createMigrator(MigrateFromMenu::class)->run();
        expect($result->ok)->toBeTrue(json_encode($result->lines));
        $menu = Navigation::$plugin->getMenus()->getMenuByHandle($f['handle']);
        expect($menu->maxLevels)->toBe(3);
        foreach ([$f['primary'], $f['secondary']] as $site) {
            expect($menu->getSiteSettings()[$site->id]->enabled)->toBeTrue()
                ->and(Node::find()->menuId($menu->id)->siteId($site->id)->count())->toBe(0)
                ->and((int)Node::find()->menuId($menu->id)->siteId($site->id)->status(null)->count())->toBe(8);
        }
    });
});
