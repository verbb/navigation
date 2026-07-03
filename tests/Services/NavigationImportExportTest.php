<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\Navigation;

it('round-trips a menu export and import with nested nodes', function() {
    $menu = NavigationFixtureFactory::menu('importExportRoundTrip');

    $parent = NavigationFixtureFactory::customNode($menu, 'About', '/about');
    NavigationFixtureFactory::customNode($menu, 'Team', '/about/team', $parent);
    NavigationFixtureFactory::customNode($menu, 'Contact', '/contact');

    $export = ImportExportHelper::generateMenuExport($menu);
    $import = ImportExportHelper::importMenuFromJson($export, 'create');

    expect($import->hasImportErrors())->toBeFalse()
        ->and($import->nodesCreated)->toBe(3)
        ->and($import->menu)->not->toBeNull()
        ->and($import->menu->handle)->toBe('importExportRoundTrip1');

    $importedNodes = Node::find()
        ->menuId($import->menu->id)
        ->level(1)
        ->all();

    expect($importedNodes)->toHaveCount(2);

    $about = null;

    foreach ($importedNodes as $node) {
        if ($node->title === 'About') {
            $about = $node;
            break;
        }
    }

    expect($about)->not->toBeNull()
        ->and($about->children)->toHaveCount(1)
        ->and($about->children[0]->title)->toBe('Team');
});

it('imports with update action replacing an existing menu tree', function() {
    $menu = NavigationFixtureFactory::menu('importExportUpdate');
    NavigationFixtureFactory::customNode($menu, 'Old link', '/old');

    $export = ImportExportHelper::generateMenuExport($menu);
    $export['nodes'] = [[
        'title' => 'New link',
        'type' => Custom::class,
        'url' => '/new',
        'newWindow' => false,
        'customAttributes' => [],
        'data' => [],
        'enabled' => true,
        'enabledForSite' => true,
        'children' => [],
    ]];

    $import = ImportExportHelper::importMenuFromJson($export, 'update');

    expect($import->hasImportErrors())->toBeFalse()
        ->and($import->menu->handle)->toBe('importExportUpdate')
        ->and($import->nodesCreated)->toBe(1);

    $nodes = Node::find()->menuId($menu->id)->all();

    expect($nodes)->toHaveCount(1)
        ->and($nodes[0]->title)->toBe('New link');
});

it('warns when linked element uids cannot be resolved', function() {
    $menu = NavigationFixtureFactory::menu('importExportMissingElement');
    $export = ImportExportHelper::generateMenuExport($menu);
    $export['nodes'] = [[
        'title' => 'Missing entry',
        'type' => Custom::class,
        'linkedElementUid' => '00000000-0000-0000-0000-000000000000',
        'linkedElementType' => 'craft\\elements\\Entry',
        'newWindow' => false,
        'customAttributes' => [],
        'data' => [],
        'enabled' => true,
        'enabledForSite' => true,
        'children' => [],
    ]];

    $import = ImportExportHelper::importMenuFromJson($export, 'create');

    expect($import->hasImportErrors())->toBeFalse()
        ->and($import->warnings)->not->toBeEmpty()
        ->and($import->nodesCreated)->toBe(1);
});

it('accepts legacy element type aliases on import', function() {
    $menu = NavigationFixtureFactory::menu('importExportLegacyType');
    $export = ImportExportHelper::generateMenuExport($menu);
    $export['nodes'] = [[
        'title' => 'Passive',
        'type' => 'verbb\\navigation\\nodetypes\\PassiveType',
        'newWindow' => false,
        'customAttributes' => [],
        'data' => [],
        'enabled' => true,
        'enabledForSite' => true,
        'children' => [],
    ]];

    $import = ImportExportHelper::importMenuFromJson($export, 'create');

    expect($import->hasImportErrors())->toBeFalse()
        ->and($import->nodesCreated)->toBe(1);

    $node = Node::find()->menuId($import->menu->id)->one();

    expect($node->type)->toBe(Passive::class);
});

it('rejects invalid import filenames for cp uploads', function() {
    expect(ImportExportHelper::resolveImportFileLocation('../etc/passwd'))->toBeNull()
        ->and(ImportExportHelper::resolveImportFileLocation('navigation-import-260703_101530.json'))->toBeNull();
});
