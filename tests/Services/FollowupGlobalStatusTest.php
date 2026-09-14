<?php

use craft\elements\Entry;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;

it('synchronizes global linked entry status across every linked locale', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $localized = Node::find()->id($node->id)->siteId($site->id)->one();
    $localized->setElementSiteId($site->id);
    $localized->title = 'Authored localized label';
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();

    $entry->enabled = false;
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->site('*')->ids())->toBe([]);
    $entry->enabled = true;
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    expect(Node::find()->id($node->id)->site('*')->all())->toHaveCount(2);
    expect(Node::find()->id($node->id)->siteId($site->id)->one()->title)->toBe('Authored localized label');
});

it('keeps localized source status changes within the linked locale', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $entry = F::entries(1)[0];
    $node = F::entryNode($menu, $entry);
    $localized = Node::find()->id($node->id)->siteId($site->id)->one();
    $localized->setElementSiteId($site->id);
    expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    $linked = Entry::find()->id($entry->id)->siteId($site->id)->one();
    $linked->setEnabledForSite(false);
    expect(Craft::$app->elements->saveElement($linked, true, false))->toBeTrue();
    expect(Node::find()->id($node->id)->siteId($site->id)->one())->toBeNull();
    expect(Node::find()->id($node->id)->siteId($entry->siteId)->one()?->id)->toBe($node->id);
});
