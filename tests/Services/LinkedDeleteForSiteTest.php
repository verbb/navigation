<?php

use craft\base\Element;
use craft\elements\Entry;
use craft\enums\PropagationMethod;
use craft\events\ModelEvent;
use yii\base\Event;
use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;

function linkedDeleteForSiteNode(Node $node, int $siteId): ?Node
{
    return Node::find()->id($node->id)->siteId($siteId)->status(null)->one();
}

function linkedDeleteForSiteEntry(int $entryId, int $siteId): ?Entry
{
    return Entry::find()->id($entryId)->siteId($siteId)->status(null)->one();
}

// An entry in a section that lets each entry choose its sites, linked from a menu on every site.
function linkedDeleteForSiteSetup(int $count = 1): array
{
    $section = F::entrySection();
    $entries = F::entries($count, $section);
    $section->propagationMethod = PropagationMethod::Custom;
    expect(Craft::$app->getEntries()->saveSection($section))->toBeTrue();

    $menu = F::menu();
    $nodes = array_map(fn(Entry $entry) => F::entryNode($menu, $entry), $entries);

    return [$entries, $nodes];
}

function linkedDeleteForSiteSaveBack(int $entryId, bool $enabledOnSecondary): void
{
    $primary = Craft::$app->getSites()->getPrimarySite();
    $entry = linkedDeleteForSiteEntry($entryId, $primary->id);
    $entry->setEnabledForSite([$primary->id => true, F::secondarySite()->id => $enabledOnSecondary]);
    expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue();
    expect(linkedDeleteForSiteEntry($entryId, F::secondarySite()->id))->not->toBeNull();
}

function linkedDeleteForSiteVisible(Node $node, int $siteId): bool
{
    $localized = linkedDeleteForSiteNode($node, $siteId);

    return $localized && $localized->enabled && $localized->getEnabledForSite();
}

it('disables linked nodes on a site the linked entry is deleted from, and restores them when it returns', function() {
    $secondary = F::secondarySite();
    $primary = Craft::$app->getSites()->getPrimarySite();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];

    // Entries in sections that let each entry choose its sites can be deleted for one site.
    $section->propagationMethod = PropagationMethod::Custom;
    expect(Craft::$app->getEntries()->saveSection($section))->toBeTrue();

    $menu = F::menu();
    $node = F::entryNode($menu, $entry);
    $localizedEntry = Entry::find()->id($entry->id)->siteId($secondary->id)->status(null)->one();
    expect($localizedEntry)->not->toBeNull();
    expect(linkedDeleteForSiteNode($node, $secondary->id)->getElementSiteId())->toBe($secondary->id);
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeTrue();

    Craft::$app->getElements()->deleteElementsForSite([$localizedEntry]);
    expect(Entry::find()->id($entry->id)->siteId($secondary->id)->status(null)->exists())->toBeFalse();

    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();
    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeTrue();
    expect(Node::find()->menuId($menu->id)->siteId($secondary->id)->ids())->toBe([]);

    // Saving the entry back to that site brings its link back, as restoring a deleted entry does.
    $entry = Entry::find()->id($entry->id)->siteId($primary->id)->status(null)->one();
    $entry->setEnabledForSite([$primary->id => true, $secondary->id => true]);
    expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue();
    $localizedEntry = Entry::find()->id($entry->id)->siteId($secondary->id)->status(null)->one();
    expect($localizedEntry)->not->toBeNull();

    $restored = linkedDeleteForSiteNode($node, $secondary->id);
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeTrue();
    expect($restored->getIsDisabledByLinkedElement())->toBeFalse();
    expect($restored->getUrl())->toBe($localizedEntry->getUrl());
    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeTrue();
});

it('keeps a link that was already disabled on that site disabled after the entry returns', function() {
    $secondary = F::secondarySite();
    $primary = Craft::$app->getSites()->getPrimarySite();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $section->propagationMethod = PropagationMethod::Custom;
    expect(Craft::$app->getEntries()->saveSection($section))->toBeTrue();

    $menu = F::menu();
    $node = F::entryNode($menu, $entry);
    $localizedNode = linkedDeleteForSiteNode($node, $secondary->id);
    $localizedNode->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localizedNode))->toBeTrue();

    $localizedEntry = Entry::find()->id($entry->id)->siteId($secondary->id)->status(null)->one();
    Craft::$app->getElements()->deleteElementsForSite([$localizedEntry]);
    $entry = Entry::find()->id($entry->id)->siteId($primary->id)->status(null)->one();
    $entry->setEnabledForSite([$primary->id => true, $secondary->id => true]);
    expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue();

    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();
    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeTrue();
});

it('keeps a site it was deleted from disabled when the entry is later trashed and restored', function() {
    $secondary = F::secondarySite();
    $primary = Craft::$app->getSites()->getPrimarySite();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $section->propagationMethod = PropagationMethod::Custom;
    expect(Craft::$app->getEntries()->saveSection($section))->toBeTrue();

    $menu = F::menu();
    $node = F::entryNode($menu, $entry);
    $localizedEntry = Entry::find()->id($entry->id)->siteId($secondary->id)->status(null)->one();
    Craft::$app->getElements()->deleteElementsForSite([$localizedEntry]);

    $entry = Entry::find()->id($entry->id)->siteId($primary->id)->status(null)->one();
    expect(Craft::$app->getElements()->deleteElement($entry))->toBeTrue();
    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeFalse();

    $entry = Entry::find()->id($entry->id)->siteId($primary->id)->status(null)->trashed()->one();
    expect(Craft::$app->getElements()->restoreElement($entry))->toBeTrue();

    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeTrue();
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();
    expect(linkedDeleteForSiteNode($node, $secondary->id)->getIsDisabledByLinkedElement())->toBeTrue();
});

it('keeps the link hidden when the entry comes back disabled on that site, until the entry is enabled there', function() {
    $secondary = F::secondarySite();
    [[$entry], [$node]] = linkedDeleteForSiteSetup();

    Craft::$app->getElements()->deleteElementsForSite([linkedDeleteForSiteEntry($entry->id, $secondary->id)]);
    linkedDeleteForSiteSaveBack($entry->id, false);

    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();
    expect(linkedDeleteForSiteNode($node, $secondary->id)->getIsDisabledByLinkedElement())->toBeFalse();

    $localizedEntry = linkedDeleteForSiteEntry($entry->id, $secondary->id);
    $localizedEntry->setEnabledForSite(true);
    expect(Craft::$app->getElements()->saveElement($localizedEntry))->toBeTrue();
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeTrue();
});

it('shows the link when an entry that was disabled on that site comes back enabled', function() {
    $secondary = F::secondarySite();
    [[$entry], [$node]] = linkedDeleteForSiteSetup();

    $localizedEntry = linkedDeleteForSiteEntry($entry->id, $secondary->id);
    $localizedEntry->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localizedEntry))->toBeTrue();
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();

    Craft::$app->getElements()->deleteElementsForSite([linkedDeleteForSiteEntry($entry->id, $secondary->id)]);
    linkedDeleteForSiteSaveBack($entry->id, true);

    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeTrue();
});

it('keeps an author-disabled link disabled when an entry that was disabled on that site comes back enabled', function() {
    $secondary = F::secondarySite();
    [[$entry], [$node]] = linkedDeleteForSiteSetup();

    $localizedNode = linkedDeleteForSiteNode($node, $secondary->id);
    $localizedNode->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localizedNode))->toBeTrue();

    $localizedEntry = linkedDeleteForSiteEntry($entry->id, $secondary->id);
    $localizedEntry->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localizedEntry))->toBeTrue();

    Craft::$app->getElements()->deleteElementsForSite([linkedDeleteForSiteEntry($entry->id, $secondary->id)]);
    linkedDeleteForSiteSaveBack($entry->id, true);

    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();
});

it('leaves a link its author disabled while the entry was disabled unpublished through the delete and return', function() {
    $secondary = F::secondarySite();
    $primary = Craft::$app->getSites()->getPrimarySite();
    [[$entry], [$node]] = linkedDeleteForSiteSetup();

    $localizedEntry = linkedDeleteForSiteEntry($entry->id, $secondary->id);
    $localizedEntry->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localizedEntry))->toBeTrue();

    $disabled = linkedDeleteForSiteNode($node, $primary->id);
    $disabled->enabled = false;
    expect(Craft::$app->getElements()->saveElement($disabled))->toBeTrue();

    Craft::$app->getElements()->deleteElementsForSite([linkedDeleteForSiteEntry($entry->id, $secondary->id)]);
    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeFalse();
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();

    linkedDeleteForSiteSaveBack($entry->id, true);
    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeFalse();
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();
});

it('keeps an author-disabled link disabled when one save enables the entry and adds it back to that site', function() {
    $secondary = F::secondarySite();
    $primary = Craft::$app->getSites()->getPrimarySite();
    [[$entry], [$node]] = linkedDeleteForSiteSetup();

    $localizedNode = linkedDeleteForSiteNode($node, $secondary->id);
    $localizedNode->setEnabledForSite(false);
    expect(Craft::$app->getElements()->saveElement($localizedNode))->toBeTrue();

    $entry = linkedDeleteForSiteEntry($entry->id, $primary->id);
    $entry->enabled = false;
    expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue();

    Craft::$app->getElements()->deleteElementsForSite([linkedDeleteForSiteEntry($entry->id, $secondary->id)]);

    $entry = linkedDeleteForSiteEntry($entry->id, $primary->id);
    $entry->enabled = true;
    $entry->setEnabledForSite([$primary->id => true, $secondary->id => true]);
    expect(Craft::$app->getElements()->saveElement($entry))->toBeTrue();
    expect(linkedDeleteForSiteEntry($entry->id, $secondary->id))->not->toBeNull();

    expect(linkedDeleteForSiteVisible($node, $primary->id))->toBeTrue();
    expect(linkedDeleteForSiteVisible($node, $secondary->id))->toBeFalse();
});

it('still disables the other entries\' links when one node fails to save during a bulk delete for site', function() {
    $secondary = F::secondarySite();
    [[$first, $second], [$failing, $other]] = linkedDeleteForSiteSetup(2);

    $handler = function(ModelEvent $event) use ($failing) {
        if ($event->sender->id === $failing->id) {
            $event->isValid = false;
        }
    };
    Event::on(Node::class, Element::EVENT_BEFORE_SAVE, $handler);

    try {
        Craft::$app->getElements()->deleteElementsForSite([
            linkedDeleteForSiteEntry($first->id, $secondary->id),
            linkedDeleteForSiteEntry($second->id, $secondary->id),
        ]);
    } finally {
        Event::off(Node::class, Element::EVENT_BEFORE_SAVE, $handler);
    }

    expect(linkedDeleteForSiteEntry($second->id, $secondary->id))->toBeNull();
    expect(linkedDeleteForSiteVisible($other, $secondary->id))->toBeFalse();
});
