<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;

it('preserves disabled menu links across linked source status cycles', function(string $type, string $scope, bool $propagate) {
    $secondary = $scope === 'single' ? null : F::secondarySite();
    $menu = F::menu(null, $scope === 'single' ? MenuSettings::PROPAGATION_METHOD_NONE : MenuSettings::PROPAGATION_METHOD_ALL);
    $source = $type === 'category' ? F::categories(1)[0] : F::entries(1)[0];
    $following = F::elementNode($menu, $source);
    $disabled = F::elementNode($menu, $source);
    $siteId = $secondary?->id ?? $source->siteId;
    $read = static fn(int $id, int $site) => Node::find()->id($id)->siteId($site)->status(null)->one();
    $visible = static fn(Node $node) => $node->enabled && $node->getEnabledForSite();

    foreach ([$following, $disabled] as $node) {
        $localized = $read($node->id, $siteId);
        $localized->setElementSiteId($siteId);
        if ($node->id === $disabled->id) {
            if ($secondary) $localized->setEnabledForSite(false);
            else $localized->enabled = false;
        }
        expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    }

    if ($scope === 'localized') {
        $source = $source::find()->id($source->id)->siteId($siteId)->status(null)->one();
    }

    for ($cycle = 0; $cycle < 2; $cycle++) {
        if ($scope === 'localized') $source->setEnabledForSite(false);
        else $source->enabled = false;
        expect(Craft::$app->elements->saveElement($source, true, $propagate))->toBeTrue();
        expect($visible($read($following->id, $siteId)))->toBeFalse();
        expect($visible($read($disabled->id, $siteId)))->toBeFalse();
        if ($scope === 'localized') {
            expect($visible($read($following->id, Craft::$app->sites->getPrimarySite()->id)))->toBeTrue();
        }

        // Deletion has its own restore state and must not erase the status override.
        if ($cycle === 1) {
            expect(Craft::$app->elements->deleteElement($source))->toBeTrue();
            expect(Craft::$app->elements->restoreElement($source))->toBeTrue();
        }
        $source->title = 'Edited while disabled';
        expect(Craft::$app->elements->saveElement($source, true, $propagate))->toBeTrue();
        if ($scope === 'localized') $source->setEnabledForSite(true);
        else $source->enabled = true;
        expect(Craft::$app->elements->saveElement($source, true, $propagate))->toBeTrue();
        expect($visible($read($following->id, $siteId)))->toBeTrue();
        expect($visible($read($disabled->id, $siteId)))->toBeFalse();
    }
})->with(['category', 'entry'])->with(['single', 'global', 'localized'])->with([false, true]);

it('preserves globally disabled nodes and does not export internal status tracking', function() {
    F::secondarySite();
    $menu = F::menu();
    $source = F::categories(1)[0];
    $node = F::elementNode($menu, $source);
    $node->enabled = false;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $source->enabled = false;
    expect(Craft::$app->elements->saveElement($source))->toBeTrue();
    $export = verbb\navigation\helpers\ImportExportHelper::generateMenuExport($menu);
    expect($export['nodes'][0]['data'])->not->toHaveKey(Node::LINKED_ELEMENT_STATUS_OVERRIDE_DATA_KEY);
    $source->enabled = true;
    expect(Craft::$app->elements->saveElement($source))->toBeTrue();
    foreach (Node::find()->id($node->id)->site('*')->status(null)->all() as $localized) {
        expect($localized->enabled)->toBeFalse();
        expect($localized->data)->not->toHaveKey(Node::LINKED_ELEMENT_STATUS_OVERRIDE_DATA_KEY);
    }
});

it('retains a later explicit enable while the linked source is disabled', function() {
    $menu = F::menu();
    $source = F::categories(1)[0];
    $node = F::elementNode($menu, $source);
    $node->enabled = false;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $source->enabled = false;
    expect(Craft::$app->elements->saveElement($source))->toBeTrue();
    $node = Node::find()->id($node->id)->status(null)->one();
    $node->enabled = true;
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $source->enabled = true;
    expect(Craft::$app->elements->saveElement($source))->toBeTrue();
    expect(Node::find()->id($node->id)->exists())->toBeTrue();
});
