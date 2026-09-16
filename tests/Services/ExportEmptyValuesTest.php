<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\ImportExportHelper;

it('preserves an explicitly cleared localized URL and suffix through export', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $node = F::customNode($menu, 'Localized link', '/primary');
    $node->urlSuffix = '?primary=1';
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $localized = Node::find()->id($node->id)->siteId($site->id)->one();
    $localized->setUrl('');
    $localized->urlSuffix = '';
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    $original = Node::find()->id($node->id)->siteId($site->id)->one();
    expect($original->getRawUrl())->toBeNull();
    expect($original->urlSuffix)->toBe('');
    $payload = ImportExportHelper::generateMenuExport($menu);
    $result = ImportExportHelper::importMenuFromJson($payload);
    expect($result->errors)->toBe([]);
    $loaded = Node::find()->menuId($result->menu->id)->siteId($site->id)->one();
    expect($loaded->getRawUrl())->toBeNull();
    expect($loaded->urlSuffix ?? '')->toBe('');
    expect($loaded->getUrl())->toBe($original->getUrl());
});

it('preserves a numeric-looking relative URL through export', function() {
    $menu = F::menu();
    F::customNode($menu, 'Zero link', '0');
    $result = ImportExportHelper::importMenuFromJson(ImportExportHelper::generateMenuExport($menu));
    expect($result->errors)->toBe([]);
    expect(Node::find()->menuId($result->menu->id)->one()->getRawUrl())->toBe('0');
});
