<?php

declare(strict_types=1);

use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\elements\conditions\TitleConditionRule;
use craft\elements\conditions\assets\AssetCondition;
use craft\elements\conditions\categories\CategoryCondition;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\conditions\users\UserCondition;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\AssetVolumeSettings;
use verbb\navigation\helpers\CategoryGroupSettings;
use verbb\navigation\helpers\ElementPickerHelper;
use verbb\navigation\helpers\EntryPickerSettings;
use verbb\navigation\helpers\EntrySectionSettings;
use verbb\navigation\nodetypes\Entry as EntryNodeType;

it('locks dynamic condition classes and element types to their providers', function() {
    $malicious = [
        'class' => UserCondition::class,
        'elementType' => User::class,
        'conditionRules' => [[
            'class' => TitleConditionRule::class,
            'operator' => '**',
            'value' => 'Keep',
        ]],
    ];

    $entryCondition = EntrySectionSettings::getEntryCondition(new Node([
        'data' => ['entryCondition' => $malicious],
    ]));
    $categoryCondition = CategoryGroupSettings::getCategoryCondition(new Node([
        'data' => ['categoryCondition' => $malicious],
    ]));
    $assetCondition = AssetVolumeSettings::getAssetCondition(new Node([
        'data' => ['assetCondition' => $malicious],
    ]));

    expect($entryCondition)->toBeInstanceOf(EntryCondition::class)
        ->and($entryCondition->elementType)->toBe(Entry::class)
        ->and($categoryCondition)->toBeInstanceOf(CategoryCondition::class)
        ->and($categoryCondition->elementType)->toBe(Category::class)
        ->and($assetCondition)->toBeInstanceOf(AssetCondition::class)
        ->and($assetCondition->elementType)->toBe(Asset::class);

    foreach ([$entryCondition, $categoryCondition, $assetCondition] as $condition) {
        expect($condition->getConditionRules())->toHaveCount(1)
            ->and($condition->getConditionRules()[0])->toBeInstanceOf(TitleConditionRule::class);
    }
});

it('locks entry picker conditions while preserving nested condition rules', function() {
    $malicious = [
        'class' => UserCondition::class,
        'elementType' => User::class,
        'conditionRules' => [[
            'class' => TitleConditionRule::class,
            'operator' => '**',
            'value' => 'Keep',
        ]],
    ];

    $condition = EntryPickerSettings::resolveSelectionCondition($malicious);
    $normalized = EntryPickerSettings::normalizePermissionSettings([
        'selectionCondition' => $malicious,
    ]);
    $picker = ElementPickerHelper::getPickerConfig([
        EntryNodeType::class => [
            'enabled' => true,
            'permissions' => '*',
            'selectionCondition' => $malicious,
        ],
    ], EntryNodeType::class, Entry::class);

    expect($condition)->toBeInstanceOf(EntryCondition::class)
        ->and($condition->elementType)->toBe(Entry::class)
        ->and($condition->getConditionRules()[0])->toBeInstanceOf(TitleConditionRule::class)
        ->and($normalized['selectionCondition']['class'])->toBe(EntryCondition::class)
        ->and($normalized['selectionCondition']['elementType'])->toBe(Entry::class)
        ->and($normalized['selectionCondition']['conditionRules'][0]['class'])->toBe(TitleConditionRule::class)
        ->and($picker['condition']['class'])->toBe(EntryCondition::class)
        ->and($picker['condition']['elementType'])->toBe(Entry::class)
        ->and($picker['condition']['conditionRules'][0]['class'])->toBe(TitleConditionRule::class);
});
