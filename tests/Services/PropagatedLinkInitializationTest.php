<?php

use craft\base\Field;
use craft\elements\Entry;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\Navigation;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\nodetypes\Entry as EntryNode;

it('initializes propagated link settings without overwriting later site edits', function(string $url, string $suffix) {
    $site = F::secondarySite();
    $menu = F::menu();
    $primary = Craft::$app->sites->getPrimarySite();
    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => $primary->id,
        'type' => Custom::class,
        'title' => 'Shared destination',
        'url' => $url,
        'urlSuffix' => $suffix,
    ]);
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    $read = static fn(int $siteId) => Node::find()->id($node->id)->siteId($siteId)->status(null)->one();
    $localized = $read($site->id);
    expect($localized)->not->toBeNull();
    expect($localized->getEnabledForSite())->toBeTrue();
    expect([$localized->getRawUrl(), $localized->urlSuffix])->toBe([$url, $suffix]);

    $localized->setUrl('/localized');
    $localized->urlSuffix = '#local';
    expect(Craft::$app->elements->saveElement($localized))->toBeTrue();
    expect([$read($primary->id)->getRawUrl(), $read($primary->id)->urlSuffix])->toBe([$url, $suffix]);
    $node = $read($primary->id);
    $node->setUrl('/changed-primary');
    $node->urlSuffix = '#primary';
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();
    expect([$read($site->id)->getRawUrl(), $read($site->id)->urlSuffix])->toBe(['/localized', '#local']);

    foreach (['', null] as $empty) {
        $localized = $read($site->id);
        $localized->setUrl($empty);
        $localized->urlSuffix = $empty;
        expect(Craft::$app->elements->saveElement($localized))->toBeTrue();
        $node = $read($primary->id);
        $node->title = 'Updated primary title';
        expect(Craft::$app->elements->saveElement($node))->toBeTrue();
        expect([$read($site->id)->getRawUrl(), $read($site->id)->urlSuffix])->toBe([null, $empty]);
    }
})->with([['/shared', '?campaign=nav'], ['0', '0']]);

it('initializes a propagated linked node with the destination site identity', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $localizedEntry = craft\elements\Entry::find()->id($entry->id)->siteId($site->id)->one();
    expect($localizedEntry)->not->toBeNull();
    $localizedNode = Node::find()->id($node->id)->siteId($site->id)->one();
    $settings = verbb\navigation\Navigation::$plugin->getNodeSites()->getSettings($node->id, $site->id);
    expect($settings)->not->toBeNull();
    expect($settings->linkedElementSiteId)->toBe($site->id);
    expect($localizedNode->getElementSiteId())->toBe($site->id);
    expect($localizedNode->getUrl())->toBe($localizedEntry->getUrl());
});

it('localizes propagated linked titles while preserving authored titles', function(string $translatedTitle, ?string $override) {
    $site = F::secondarySite();
    $site->language = 'de';
    expect(Craft::$app->sites->saveSite($site))->toBeTrue();
    $menu = F::menu();
    $menu->titleTranslationMethod = Field::TRANSLATION_METHOD_SITE;
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();

    $entry = F::entries(1)[0];
    $entry->title = 'About us';
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    $translatedEntry = Entry::find()->id($entry->id)->siteId($site->id)->one();
    $translatedEntry->title = $translatedTitle;
    expect(Craft::$app->elements->saveElement($translatedEntry))->toBeTrue();

    $node = new Node([
        'menuId' => $menu->id,
        'siteId' => $entry->siteId,
        'type' => EntryNode::class,
        'elementId' => $entry->id,
        'elementSiteId' => $entry->siteId,
        'title' => $override ?? $entry->title,
    ]);
    expect(Craft::$app->elements->saveElement($node))->toBeTrue();

    $read = static fn(int $siteId) => Node::find()->id($node->id)->siteId($siteId)->status(null)->one();
    $localized = $read($site->id);
    expect($localized->title)->toBe($override ?? $translatedTitle);
    expect($localized->getElementSiteId())->toBe($site->id);
    expect($localized->hasOverriddenTitle())->toBe($override !== null && $override !== $translatedTitle);
    expect($read($entry->siteId)->title)->toBe($override ?? 'About us');

    // Re-saving the source must preserve the destination's title and linked identity.
    expect(Craft::$app->elements->saveElement($read($entry->siteId)))->toBeTrue();
    expect($read($site->id)->title)->toBe($override ?? $translatedTitle);
    expect($read($site->id)->getElementSiteId())->toBe($site->id);

    $localized = $read($site->id);
    $localized->title = 'Unser Unternehmen';
    expect(Craft::$app->elements->saveElement($localized))->toBeTrue();
    expect(Craft::$app->elements->saveElement($read($entry->siteId)))->toBeTrue();
    expect($read($site->id)->title)->toBe('Unser Unternehmen');
    expect($read($site->id)->hasOverriddenTitle())->toBeTrue();
    expect($read($entry->siteId)->title)->toBe($override ?? 'About us');
})->with([
    'translated title' => ['Über uns', null],
    'identical title' => ['About us', null],
    'source override' => ['Über uns', 'Company'],
    'source override matching the translation' => ['Über uns', 'Über uns'],
]);
