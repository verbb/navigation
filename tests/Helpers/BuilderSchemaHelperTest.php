<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\dynamic\sources\EntrySectionDynamicSource;
use verbb\navigation\helpers\BuilderSchemaHelper;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Dynamic;
use verbb\navigation\nodetypes\Entry;

test('add node schema returns flat plugin-kit field definitions', function () {
    $compiled = BuilderSchemaHelper::compileAddNodeSchema([
        'category' => 'nodeType',
        'type' => Custom::class,
    ], true, [
        ['label' => '', 'value' => 0],
        ['label' => 'Home', 'value' => 1],
    ]);

    expect($compiled['schema'])->toHaveCount(4);

    foreach ($compiled['schema'] as $field) {
        expect($field)->toHaveKey('$field');
        expect($field)->toHaveKey('name');
        expect($field)->not->toHaveKey('$el');
    }

    expect($compiled['fieldEntries'])->toHaveCount(4);
    expect($compiled['fieldEntries'][0]['path'])->toBe('parentId');
    expect($compiled['fieldEntries'][0]['field']['$field'])->toBe('combobox');
    expect($compiled['fieldEntries'][0]['field']['placeholder'])->toBe('Top level');
    expect($compiled['fieldEntries'][0]['field']['options'])->toHaveCount(1);
    expect($compiled['fieldEntries'][1]['path'])->toBe('newWindow');
});

test('add node schema omits parent field when max levels is one', function () {
    $compiled = BuilderSchemaHelper::compileAddNodeSchema([
        'category' => 'element',
        'type' => Entry::class,
    ], false, []);

    expect($compiled['schema'])->toHaveCount(1);
    expect($compiled['schema'][0]['$field'])->toBe('lightswitch');
    expect($compiled['fieldEntries'])->toHaveCount(1);
    expect($compiled['fieldEntries'][0]['path'])->toBe('newWindow');
});

test('add node schema includes conditional provider fields for dynamic nodes', function () {
    $section = F::entrySection();
    $compiled = BuilderSchemaHelper::compileAddNodeSchema([
        'category' => 'nodeType',
        'type' => Dynamic::class,
    ], false, []);

    expect($compiled['fieldEntries'])->toHaveCount(5);
    expect($compiled['fieldEntries'][0]['path'])->toBe('data.dynamicSource');
    expect($compiled['fieldEntries'][0]['field']['$field'])->toBe('select');
    expect($compiled['fieldEntries'][1]['path'])->toBe('data.sectionId');
    expect($compiled['fieldEntries'][1]['field']['if'])->toBe('data.dynamicSource == "entrySection"');
    expect(array_column($compiled['fieldEntries'][1]['field']['options'], 'value'))->toContain((string)$section->id);
    expect($compiled['fieldEntries'][2]['path'])->toBe('data.groupId');
    expect($compiled['fieldEntries'][2]['field']['if'])->toBe('data.dynamicSource == "categoryGroup"');
    expect($compiled['fieldEntries'][3]['path'])->toBe('data.volumeId');
    expect($compiled['fieldEntries'][3]['field']['if'])->toBe('data.dynamicSource == "assetVolume"');
    expect($compiled['fieldEntries'][4]['path'])->toBe('title');
});

test('node types provide quick-add default data', function () {
    expect(Dynamic::getAddNodeDefaultData())->toMatchArray([
        'dynamicSource' => EntrySectionDynamicSource::handle(),
        'sectionId' => '',
        'groupId' => '',
        'volumeId' => '',
    ]);
    expect(\verbb\navigation\nodetypes\Site::getAddNodeDefaultData())->toBe(['siteId' => '']);
    expect(Custom::getAddNodeDefaultData())->toBe([]);
});
