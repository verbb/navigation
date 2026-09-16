<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation as N;

it('deletes and restores linked content after a menu site is disabled', function(string $propagationMethod) {
    $secondary = F::secondarySite();
    $menu = F::menu(null, $propagationMethod);
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $siteSettings = $menu->getSiteSettings();
    $siteSettings[$secondary->id]->enabled = false;
    $menu->setSiteSettings($siteSettings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->one())->toBeNull();
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->one()?->id)->toBe($node->id);
})->with([
    MenuSettings::PROPAGATION_METHOD_ALL,
    MenuSettings::PROPAGATION_METHOD_SITE_GROUP,
    MenuSettings::PROPAGATION_METHOD_LANGUAGE,
]);

it('restores linked content when a menu locale was disabled after deletion', function() {
    $secondary = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    $siteSettings = $menu->getSiteSettings();
    $siteSettings[$secondary->id]->enabled = false;
    $menu->setSiteSettings($siteSettings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->one()?->id)->toBe($node->id);
});

it('restores each localized linked node after its source is restored', function(string $sourceType, bool $secondaryEnabled) {
    $secondary = F::secondarySite();
    $menu = F::menu();
    $source = $sourceType === 'section' ? F::entrySection() : F::categoryGroup();
    $element = $sourceType === 'section' ? F::entries(1, $source)[0] : F::categories(1, $source)[0];
    $node = F::elementNode($menu, $element);
    $localized = Node::find()->id($node->id)->siteId($secondary->id)->status(null)->one();
    $localized->setEnabledForSite($secondaryEnabled);
    expect(Craft::$app->getElements()->saveElement($localized, true, false))->toBeTrue();
    $configKey = ($sourceType === 'section' ? 'sections.' : 'categoryGroups.') . $source->uid;
    $sourceConfig = Craft::$app->getProjectConfig()->get($configKey);

    $deleted = $sourceType === 'section'
        ? Craft::$app->getEntries()->deleteSection($source)
        : Craft::$app->getCategories()->deleteGroup($source);
    expect($deleted)->toBeTrue();
    foreach ([$node->siteId, $secondary->id] as $siteId) {
        $disabled = Node::find()->id($node->id)->siteId($siteId)->status(null)->one();
        expect($disabled->getEnabledForSite())->toBeFalse();
        expect($disabled->getIsDisabledByLinkedElement())->toBeTrue();
    }

    Craft::$app->getProjectConfig()->set($configKey, $sourceConfig);
    foreach ([$node->siteId => true, $secondary->id => $secondaryEnabled] as $siteId => $enabled) {
        $restored = Node::find()->id($node->id)->siteId($siteId)->status(null)->one();
        expect($restored->enabled)->toBeTrue();
        expect($restored->getEnabledForSite())->toBe($enabled);
        expect($restored->getIsDisabledByLinkedElement())->toBeFalse();
    }
})->with(['section', 'category-group'])->with([true, false]);
