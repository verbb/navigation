<?php

declare(strict_types=1);

namespace Tests\Support\Fixtures;

use Craft;
use craft\db\Migration;
use craft\models\Structure;
use ReflectionProperty;
use RuntimeException;

/** Source-table contracts from Navkit 1.0.0, FreeNav 5.1.4, MenuBuilder 1.0.0 and TKA Navigation 1.0. */
final class PluginMigrationFixture
{
    public static function with(string $provider, callable $callback, bool $legacyFreeNav = false): mixed
    {
        if (getenv('ENVIRONMENT') !== 'testing') {
            throw new RuntimeException('Migration fixtures require the isolated test environment.');
        }
        $tables = match ($provider) {
            'navkit' => ['navkit_menus', 'navkit_nodes'],
            'free-nav' => ['freenav_menus', 'freenav_menu_sites', 'freenav_nodes'],
            'menu-builder' => ['menubuilder_groups', 'menubuilder_items'],
            'tka-navigation' => ['tka_navigations', 'tka_navigations_sites'],
        };
        $db = Craft::$app->getDb();
        foreach ($tables as $table) {
            if ($db->tableExists('{{%' . $table . '}}')) {
                throw new RuntimeException("Refusing to replace existing source table: $table");
            }
        }
        $plugins = Craft::$app->getPlugins();
        $plugins->loadPlugins();
        $property = new ReflectionProperty($plugins, '_storedPluginInfo');
        $original = $property->getValue($plugins);
        $migration = new class(['db' => $db]) extends Migration { public function safeUp(): bool { return true; } };
        $migration->compact = true;
        try {
            self::createTables($migration, $provider, $legacyFreeNav);
            // Readiness metadata only. No provider code or importer response is mocked.
            $property->setValue($plugins, $original + [$provider => ['enabled' => false]]);
            return $callback(self::seed($provider, $legacyFreeNav));
        } finally {
            $property->setValue($plugins, $original);
            foreach (array_reverse($tables) as $table) {
                $migration->dropTableIfExists('{{%' . $table . '}}');
            }
            $db->getSchema()->refresh();
        }
    }

    private static function createTables(Migration $m, string $provider, bool $legacy): void
    {
        if ($provider === 'tka-navigation') {
            $m->createTable('{{%tka_navigations}}', ['id'=>$m->integer()->notNull(), 'handle'=>$m->string()->notNull(), 'PRIMARY KEY([[id]])']);
            $m->createTable('{{%tka_navigations_sites}}', ['id'=>$m->primaryKey(), 'elementId'=>$m->integer()->notNull(), 'siteId'=>$m->integer()->notNull(), 'nodes'=>$m->mediumText()]);
            $m->createIndex(null, '{{%tka_navigations_sites}}', ['elementId','siteId'], true);
            return;
        }
        if ($provider === 'navkit') {
            $m->createTable('{{%navkit_menus}}', ['id'=>$m->primaryKey(), 'structureId'=>$m->integer(), 'fieldLayoutId'=>$m->integer(), 'name'=>$m->string()->notNull(), 'handle'=>$m->string()->notNull(), 'settings'=>$m->json()]);
            $m->createTable('{{%navkit_nodes}}', ['id'=>$m->integer()->notNull(), 'menuId'=>$m->integer()->notNull(), 'type'=>$m->string(), 'url'=>$m->text(), 'linkedElementId'=>$m->integer(), 'linkedSiteId'=>$m->integer(), 'target'=>$m->string(), 'classes'=>$m->string(), 'rel'=>$m->string(), 'data'=>$m->json(), 'PRIMARY KEY([[id]])']);
            return;
        }
        if ($provider === 'menu-builder') {
            $m->createTable('{{%menubuilder_groups}}', ['id'=>$m->primaryKey(), 'name'=>$m->string()->notNull(), 'handle'=>$m->string()->notNull(), 'description'=>$m->text(), 'enabled'=>$m->boolean()->notNull()->defaultValue(true), 'sortOrder'=>$m->integer()->notNull()->defaultValue(0), 'maxDepth'=>$m->tinyInteger(), 'cssClass'=>$m->string(), 'htmlAttributes'=>$m->text()->notNull(), 'settings'=>$m->text()->notNull(), 'fieldLayoutId'=>$m->integer()]);
            $m->createTable('{{%menubuilder_items}}', ['id'=>$m->primaryKey(), 'groupId'=>$m->integer()->notNull(), 'parentId'=>$m->integer(), 'type'=>$m->string(20)->notNull(), 'title'=>$m->string()->notNull(), 'handle'=>$m->string(), 'enabled'=>$m->boolean()->notNull()->defaultValue(true), 'sortOrder'=>$m->integer()->notNull()->defaultValue(0), 'clickable'=>$m->boolean()->notNull()->defaultValue(true), 'elementId'=>$m->integer(), 'customUrl'=>$m->text(), 'target'=>$m->string(), 'rel'=>$m->string(), 'cssClass'=>$m->string(), 'htmlId'=>$m->string(), 'htmlAttributes'=>$m->text()->notNull(), 'ariaLabel'=>$m->string(), 'titleAttribute'=>$m->string(), 'icon'=>$m->string(), 'badge'=>$m->string(), 'description'=>$m->text(), 'image'=>$m->integer(), 'featured'=>$m->boolean()->notNull()->defaultValue(false), 'fallbackBehavior'=>$m->string(), 'fallbackUrl'=>$m->text(), 'visibility'=>$m->text()->notNull(), 'metadata'=>$m->text()->notNull(), 'contentId'=>$m->integer()]);
            return;
        }
        $m->createTable('{{%freenav_menus}}', ['id'=>$m->primaryKey(), 'structureId'=>$m->integer(), 'fieldLayoutId'=>$m->integer(), 'name'=>$m->string()->notNull(), 'handle'=>$m->string()->notNull(), 'instructions'=>$m->text(), 'propagationMethod'=>$m->string(), 'maxNodes'=>$m->integer(), 'maxLevels'=>$m->smallInteger(), 'defaultPlacement'=>$m->string(), 'permissions'=>$m->text(), 'sortOrder'=>$m->smallInteger(), 'dateDeleted'=>$m->dateTime()]);
        $m->createTable('{{%freenav_menu_sites}}', ['id'=>$m->primaryKey(), 'menuId'=>$m->integer(), 'siteId'=>$m->integer(), 'enabled'=>$m->boolean()]);
        $columns = ['id'=>$m->integer()->notNull(), 'menuId'=>$m->integer()->notNull(), 'linkedElementId'=>$m->integer(), 'nodeType'=>$m->string(), ($legacy ? 'url' : 'customUrl')=>$m->text(), 'classes'=>$m->string(), 'urlSuffix'=>$m->string(), 'customAttributes'=>$m->text(), 'data'=>$m->text(), 'newWindow'=>$m->boolean(), 'icon'=>$m->string(), 'badge'=>$m->string(), 'visibilityRules'=>$m->text(), 'deletedWithMenu'=>$m->boolean(), 'PRIMARY KEY([[id]])'];
        if ($legacy) $columns['parentId'] = $m->integer();
        $m->createTable('{{%freenav_nodes}}', $columns);
    }

    private static function seed(string $provider, bool $legacy): array
    {
        $db = Craft::$app->getDb();
        $primary = Craft::$app->getSites()->getPrimarySite();
        $secondary = NavigationFixtureFactory::secondarySite();
        $entries = NavigationFixtureFactory::entries(4);
        [$parent, $child, $disabled, $linked] = $entries;
        $structure = new Structure(['maxLevels'=>3]);
        Craft::$app->getStructures()->saveStructure($structure);
        Craft::$app->getStructures()->appendToRoot($structure->id, $parent);
        Craft::$app->getStructures()->append($structure->id, $child, $parent);
        Craft::$app->getStructures()->appendToRoot($structure->id, $disabled);
        $db->createCommand()->update('{{%elements}}', ['enabled'=>false], ['id'=>$disabled->id])->execute();
        // These entries stand in for provider node elements. Seed the providers'
        // elements_sites title contract explicitly: a bare Entry fixture in newer
        // Craft versions can have no native title field in its generated layout.
        foreach ([$parent, $child, $disabled] as $index => $sourceNode) {
            $db->createCommand()->update('{{%elements_sites}}',
                ['title' => 'Provider node ' . ($index + 1)],
                ['elementId' => $sourceNode->id],
            )->execute();
        }
        $handle = 'provider' . bin2hex(random_bytes(5));
        if ($provider === 'menu-builder') {
            $db->createCommand()->insert('{{%menubuilder_groups}}', [
                'name' => 'Provider menu',
                'handle' => $handle,
                'description' => 'MenuBuilder migration fixture',
                'enabled' => true,
                'sortOrder' => 1,
                'maxDepth' => 3,
                'cssClass' => 'source-menu',
                'htmlAttributes' => json_encode(['data-menu' => 'provider']),
                'settings' => json_encode(['siteIds' => [$primary->id]]),
            ])->execute();
            $menuId = (int)$db->getLastInsertID();
            $itemDefaults = [
                'groupId' => $menuId,
                'handle' => null,
                'enabled' => true,
                'clickable' => true,
                'target' => '_self',
                'rel' => null,
                'cssClass' => 'provider-class',
                'htmlId' => null,
                'htmlAttributes' => json_encode([]),
                'ariaLabel' => null,
                'titleAttribute' => null,
                'icon' => null,
                'badge' => null,
                'description' => null,
                'image' => null,
                'featured' => false,
                'fallbackBehavior' => 'hide',
                'fallbackUrl' => null,
                'visibility' => json_encode([]),
                'metadata' => json_encode([]),
                'contentId' => null,
            ];
            $db->createCommand()->insert('{{%menubuilder_items}}', array_merge($itemDefaults, [
                'parentId' => null,
                'type' => 'url',
                'title' => 'Provider node 1',
                'sortOrder' => 0,
                'elementId' => null,
                'customUrl' => '/provider-0',
                'target' => '_blank',
                'rel' => 'nofollow',
                'htmlId' => 'provider-root',
                'htmlAttributes' => json_encode(['data-source' => 'migration']),
                'ariaLabel' => 'Provider root',
                'visibility' => json_encode([['type' => 'loggedIn']]),
            ]))->execute();
            $rootId = (int)$db->getLastInsertID();
            $db->createCommand()->insert('{{%menubuilder_items}}', array_merge($itemDefaults, [
                'parentId' => $rootId,
                'type' => 'entry',
                'title' => 'Provider node 2',
                'sortOrder' => 0,
                'elementId' => $linked->id,
                'customUrl' => null,
                'target' => '_blank',
            ]))->execute();
            $db->createCommand()->insert('{{%menubuilder_items}}', array_merge($itemDefaults, [
                'parentId' => null,
                'type' => 'url',
                'title' => 'Provider node 3',
                'enabled' => false,
                'sortOrder' => 1,
                'elementId' => null,
                'customUrl' => '/provider-2',
            ]))->execute();
            $db->createCommand()->insert('{{%menubuilder_items}}', array_merge($itemDefaults, [
                'parentId' => null,
                'type' => 'dynamic',
                'title' => 'Provider dynamic',
                'sortOrder' => 2,
                'elementId' => null,
                'customUrl' => null,
                'metadata' => json_encode([
                    'dynamicSource' => [
                        'sourceType' => 'entries',
                        'sourceId' => $linked->sectionId,
                        'limit' => 5,
                        'orderBy' => 'title asc',
                    ],
                    'mobile' => ['visibility' => 'both'],
                ]),
            ]))->execute();
            foreach ([
                ['type' => 'anchor', 'title' => 'Provider anchor', 'handle' => 'details', 'customUrl' => null],
                ['type' => 'url', 'title' => 'Provider heading', 'clickable' => false, 'customUrl' => '/not-clickable'],
                ['type' => 'separator', 'title' => '', 'clickable' => false, 'customUrl' => null],
                ['type' => 'entry', 'title' => 'Provider fallback', 'elementId' => 99999998, 'customUrl' => null, 'fallbackBehavior' => 'fallbackUrl', 'fallbackUrl' => '/fallback'],
                ['type' => 'entry', 'title' => 'Provider hidden', 'elementId' => 99999999, 'customUrl' => null, 'fallbackBehavior' => 'hide'],
            ] as $offset => $item) {
                $db->createCommand()->insert('{{%menubuilder_items}}', array_merge($itemDefaults, [
                    'parentId' => null,
                    'sortOrder' => $offset + 3,
                    'elementId' => null,
                ], $item))->execute();
            }
        } elseif ($provider === 'tka-navigation') {
            $db->createCommand()->insert('{{%tka_navigations}}', ['id'=>$parent->id, 'handle'=>$handle])->execute();
            $tree = [['type'=>'url','customLabel'=>'Provider root','url'=>'/provider-root','children'=>[['type'=>'anchor','customLabel'=>'Linked child','entryId'=>$linked->id,'anchor'=>'details','newTab'=>true]]]];
            foreach ([$primary, $secondary] as $site) {
                $db->createCommand()->insert('{{%tka_navigations_sites}}', ['elementId'=>$parent->id,'siteId'=>$site->id,'nodes'=>json_encode($tree)])->execute();
            }
        } else {
            $table = $provider === 'navkit' ? 'navkit_menus' : 'freenav_menus';
            $row = ['name'=>'Provider menu','handle'=>$handle,'structureId'=>$structure->id];
            if ($provider === 'navkit') {
                $row['settings'] = json_encode(['propagationMethod'=>'all','maxLevels'=>3]);
            } else {
                $row += ['propagationMethod'=>'all','maxLevels'=>3,'defaultPlacement'=>'end','sortOrder'=>1];
            }
            $db->createCommand()->insert('{{%' . $table . '}}', $row)->execute();
            $menuId = (int)$db->getLastInsertID();
            if ($provider === 'free-nav') {
                foreach ([$primary,$secondary] as $site) {
                    $db->createCommand()->insert('{{%freenav_menu_sites}}', ['menuId'=>$menuId,'siteId'=>$site->id,'enabled'=>true])->execute();
                }
            }
            foreach ([$parent,$child,$disabled] as $index=>$element) {
                $isLinked = $index === 1;
                $node = ['id'=>$element->id,'menuId'=>$menuId,'linkedElementId'=>$isLinked ? $linked->id : null,'classes'=>'provider-class','data'=>json_encode(['source'=>'fixture'])];
                if ($provider === 'navkit') {
                    $node += ['type'=>$isLinked?'entry':'url','url'=>$isLinked?null:'/provider-' . $index,'linkedSiteId'=>$secondary->id,'target'=>'_blank','rel'=>'nofollow'];
                } else {
                    $node += ['nodeType'=>$isLinked?'entry':'custom',($legacy?'url':'customUrl')=>$isLinked?null:'/provider-' . $index,'urlSuffix'=>'?source=migration','newWindow'=>true,'customAttributes'=>json_encode([['attribute'=>'rel','value'=>'nofollow']])];
                    // Historical FreeNav parentId was unused/null; hierarchy lives in Craft.
                    if ($legacy) $node['parentId'] = null;
                }
                $db->createCommand()->insert('{{%' . ($provider === 'navkit' ? 'navkit_nodes' : 'freenav_nodes') . '}}', $node)->execute();
            }
        }
        return compact('handle','primary','secondary','linked','parent','child');
    }
}
