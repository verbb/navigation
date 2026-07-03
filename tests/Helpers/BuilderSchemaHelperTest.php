<?php

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

test('add node schema includes dynamic source and title fields for dynamic nodes', function () {
    $compiled = BuilderSchemaHelper::compileAddNodeSchema([
        'category' => 'nodeType',
        'type' => Dynamic::class,
    ], false, []);

    expect($compiled['fieldEntries'])->toHaveCount(2);
    expect($compiled['fieldEntries'][0]['path'])->toBe('data.dynamicSource');
    expect($compiled['fieldEntries'][0]['field']['$field'])->toBe('select');
    expect($compiled['fieldEntries'][1]['path'])->toBe('title');
});

test('node types provide quick-add default data', function () {
    expect(Dynamic::getAddNodeDefaultData())->toMatchArray([
        'dynamicSource' => EntrySectionDynamicSource::handle(),
    ]);
    expect(\verbb\navigation\nodetypes\Site::getAddNodeDefaultData())->toBe(['siteId' => '']);
    expect(Custom::getAddNodeDefaultData())->toBe([]);
});
