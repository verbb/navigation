<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Custom;

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
