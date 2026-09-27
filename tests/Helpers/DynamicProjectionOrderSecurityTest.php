<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\AssetVolumeSettings;
use verbb\navigation\helpers\CategoryGroupSettings;
use verbb\navigation\helpers\EntrySectionSettings;
use verbb\navigation\helpers\ProductTypeSettings;

it('falls back to fixed ordering for unrecognized dynamic projection order values', function() {
    $payload = 'title desc, (select sleep(5))';

    $entryQuery = craft\elements\Entry::find();
    EntrySectionSettings::applyOrderBy($entryQuery, ['orderBy' => $payload], new craft\models\Section([
        'type' => craft\models\Section::TYPE_CHANNEL,
    ]));

    $categoryQuery = craft\elements\Category::find();
    CategoryGroupSettings::applyOrderBy($categoryQuery, ['orderBy' => $payload], new craft\models\CategoryGroup([
        'maxLevels' => 1,
    ]));

    $assetQuery = craft\elements\Asset::find();
    AssetVolumeSettings::applyOrderBy($assetQuery, ['orderBy' => $payload]);

    $productQuery = Node::find();
    ProductTypeSettings::applyOrderBy($productQuery, ['orderBy' => $payload]);

    expect($entryQuery->orderBy)->toBe(['postDate' => SORT_DESC])
        ->and($categoryQuery->orderBy)->toBe(['title' => SORT_ASC])
        ->and($assetQuery->orderBy)->toBe(['title' => SORT_ASC])
        ->and($productQuery->orderBy)->toBe(['title' => SORT_ASC]);
});

it('preserves every supported dynamic projection order value', function() {
    $structureSection = new craft\models\Section(['type' => craft\models\Section::TYPE_STRUCTURE]);
    $channelSection = new craft\models\Section(['type' => craft\models\Section::TYPE_CHANNEL]);
    $structuredGroup = new craft\models\CategoryGroup(['maxLevels' => 2]);
    $flatGroup = new craft\models\CategoryGroup(['maxLevels' => 1]);

    expect(EntrySectionSettings::normalizeOrderBy(EntrySectionSettings::ORDER_STRUCTURE, $structureSection))
        ->toBe(EntrySectionSettings::ORDER_STRUCTURE)
        ->and(EntrySectionSettings::normalizeOrderBy(EntrySectionSettings::ORDER_STRUCTURE, $channelSection))
        ->toBe(EntrySectionSettings::ORDER_DEFAULT)
        ->and(EntrySectionSettings::normalizeOrderBy(EntrySectionSettings::ORDER_TITLE_ASC, $channelSection))
        ->toBe(EntrySectionSettings::ORDER_TITLE_ASC)
        ->and(EntrySectionSettings::normalizeOrderBy(EntrySectionSettings::ORDER_TITLE_DESC, $channelSection))
        ->toBe(EntrySectionSettings::ORDER_TITLE_DESC)
        ->and(EntrySectionSettings::normalizeOrderBy(EntrySectionSettings::ORDER_POST_DATE_ASC, $channelSection))
        ->toBe(EntrySectionSettings::ORDER_POST_DATE_ASC)
        ->and(EntrySectionSettings::normalizeOrderBy(EntrySectionSettings::ORDER_POST_DATE_DESC, $channelSection))
        ->toBe(EntrySectionSettings::ORDER_POST_DATE_DESC)
        ->and(CategoryGroupSettings::normalizeOrderBy(CategoryGroupSettings::ORDER_STRUCTURE, $structuredGroup))
        ->toBe(CategoryGroupSettings::ORDER_STRUCTURE)
        ->and(CategoryGroupSettings::normalizeOrderBy(CategoryGroupSettings::ORDER_STRUCTURE, $flatGroup))
        ->toBe(CategoryGroupSettings::ORDER_DEFAULT)
        ->and(CategoryGroupSettings::normalizeOrderBy(CategoryGroupSettings::ORDER_TITLE_DESC, $flatGroup))
        ->toBe(CategoryGroupSettings::ORDER_TITLE_DESC)
        ->and(CategoryGroupSettings::normalizeOrderBy(CategoryGroupSettings::ORDER_DATE_CREATED_ASC, $flatGroup))
        ->toBe(CategoryGroupSettings::ORDER_DATE_CREATED_ASC)
        ->and(CategoryGroupSettings::normalizeOrderBy(CategoryGroupSettings::ORDER_DATE_CREATED_DESC, $flatGroup))
        ->toBe(CategoryGroupSettings::ORDER_DATE_CREATED_DESC)
        ->and(AssetVolumeSettings::normalizeOrderBy(AssetVolumeSettings::ORDER_TITLE_DESC))
        ->toBe(AssetVolumeSettings::ORDER_TITLE_DESC)
        ->and(AssetVolumeSettings::normalizeOrderBy(AssetVolumeSettings::ORDER_DATE_MODIFIED_ASC))
        ->toBe(AssetVolumeSettings::ORDER_DATE_MODIFIED_ASC)
        ->and(AssetVolumeSettings::normalizeOrderBy(AssetVolumeSettings::ORDER_DATE_MODIFIED_DESC))
        ->toBe(AssetVolumeSettings::ORDER_DATE_MODIFIED_DESC)
        ->and(ProductTypeSettings::normalizeOrderBy(ProductTypeSettings::ORDER_TITLE_DESC))
        ->toBe(ProductTypeSettings::ORDER_TITLE_DESC)
        ->and(ProductTypeSettings::normalizeOrderBy(ProductTypeSettings::ORDER_DATE_CREATED_ASC))
        ->toBe(ProductTypeSettings::ORDER_DATE_CREATED_ASC)
        ->and(ProductTypeSettings::normalizeOrderBy(ProductTypeSettings::ORDER_DATE_CREATED_DESC))
        ->toBe(ProductTypeSettings::ORDER_DATE_CREATED_DESC);
});

it('normalizes unsafe dynamic projection ordering when a node is saved', function() {
    $menu = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $node = NavigationFixtureFactory::dynamicSectionNode($menu, $section, null, [
        'orderBy' => 'extractvalue(1, concat(0x7e, version()))',
    ]);

    expect($node->data['orderBy'])->toBe(EntrySectionSettings::ORDER_DEFAULT);
});
