<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\Navigation as N;

function changeInactiveAuditMenuSite(int $menuId, int $siteId, bool $enabled): void
{
    $menu = N::$plugin->getMenus()->getMenuById($menuId);
    $settings = $menu->getSiteSettings();
    $settings[$siteId]->enabled = $enabled;
    $menu->setSiteSettings($settings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
}

it('keeps links to deleted content hidden when their menu was already disabled', function(bool $originallyEnabled) {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $node->enabled = $originallyEnabled;
    expect(Craft::$app->getElements()->saveElement($node))->toBeTrue();
    changeInactiveAuditMenuSite($menu->id, $node->siteId, false);
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    changeInactiveAuditMenuSite($menu->id, $node->siteId, true);

    expect(Node::find()->id($node->id)->ids())->toBe([]);
    $inactive = Node::find()->id($node->id)->status(null)->one();
    expect($inactive->getIsDisabledByLinkedElement())->toBeTrue();
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->ids())->toBe($originallyEnabled ? [$node->id] : []);
})->with([true, false]);

it('can retry a rejected menu-site reactivation without stale project configuration', function() {
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    changeInactiveAuditMenuSite($menu->id, $node->siteId, false);
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();
    $before = Craft::$app->getProjectConfig()->get('navigation.menus.' . $menu->uid);
    $veto = static function($event) use ($node) {
        if ($event->sender->id === $node->id) {
            $event->isValid = false;
        }
    };
    yii\base\Event::on(Node::class, Node::EVENT_BEFORE_SAVE, $veto);
    try {
        expect(fn() => changeInactiveAuditMenuSite($menu->id, $node->siteId, true))->toThrow(yii\base\UserException::class);
    } finally {
        yii\base\Event::off(Node::class, Node::EVENT_BEFORE_SAVE, $veto);
    }
    expect(Craft::$app->getProjectConfig()->get('navigation.menus.' . $menu->uid))->toBe($before);
    changeInactiveAuditMenuSite($menu->id, $node->siteId, true);
    expect(Node::find()->id($node->id)->ids())->toBe([$node->id]);
});

it('reconciles a bulk source deletion after every menu site was disabled', function(string $sourceType) {
    $secondary = F::secondarySite();
    $menu = F::menu();
    $source = $sourceType === 'section' ? F::entrySection() : F::categoryGroup();
    $element = $sourceType === 'section' ? F::entries(1, $source)[0] : F::categories(1, $source)[0];
    $node = F::elementNode($menu, $element);
    $localized = Node::find()->id($node->id)->siteId($secondary->id)->status(null)->one();
    $localized->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localized, true, false))->toBeTrue();
    $configKey = ($sourceType === 'section' ? 'sections.' : 'categoryGroups.') . $source->uid;
    $sourceConfig = Craft::$app->getProjectConfig()->get($configKey);
    $settings = $menu->getSiteSettings();
    foreach ($settings as $setting) {
        $setting->enabled = false;
    }
    $menu->setSiteSettings($settings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $deleted = $sourceType === 'section'
        ? Craft::$app->getEntries()->deleteSection($source)
        : Craft::$app->getCategories()->deleteGroup($source);
    expect($deleted)->toBeTrue();
    foreach ($settings as $setting) {
        $setting->enabled = true;
    }
    $menu->setSiteSettings($settings);
    expect(N::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    foreach ([$node->siteId, $secondary->id] as $siteId) {
        expect(Node::find()->id($node->id)->siteId($siteId)->ids())->toBe([]);
        expect(Node::find()->id($node->id)->siteId($siteId)->status(null)->one()->getIsDisabledByLinkedElement())->toBeTrue();
    }
    Craft::$app->getProjectConfig()->set($configKey, $sourceConfig);
    expect(Node::find()->id($node->id)->siteId($node->siteId)->ids())->toBe([$node->id]);
    expect(Node::find()->id($node->id)->siteId($secondary->id)->ids())->toBe([]);
    expect(Node::find()->id($node->id)->siteId($secondary->id)->status(null)->one()->getIsDisabledByLinkedElement())->toBeFalse();
})->with(['section', 'category-group']);
