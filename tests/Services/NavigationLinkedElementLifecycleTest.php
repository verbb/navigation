<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Node;
use verbb\navigation\nodetypes\Dynamic;

function nodeIsVisibleOnSite(Node $node): bool
{
    $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;

    return $isMultiSite ? $node->getEnabledForSite() : $node->enabled;
}

it('soft-deletes linked entry nodes by disabling them', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $node = NavigationFixtureFactory::entryNode($nav, $entry);

    expect(nodeIsVisibleOnSite($node))->toBeTrue();

    Craft::$app->getElements()->deleteElement($entry);

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded)->not->toBeNull();
    expect(nodeIsVisibleOnSite($reloaded))->toBeFalse();
    expect($reloaded->getIsDisabledByLinkedElement())->toBeTrue();
});

it('restores linked entry nodes when the entry is restored', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $node = NavigationFixtureFactory::entryNode($nav, $entry);

    Craft::$app->getElements()->deleteElement($entry);
    Craft::$app->getElements()->restoreElement($entry);

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded)->not->toBeNull();
    expect(nodeIsVisibleOnSite($reloaded))->toBeTrue();
    expect($reloaded->getIsDisabledByLinkedElement())->toBeFalse();
});

it('hard-deletes linked entry nodes when the entry is permanently deleted', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $node = NavigationFixtureFactory::entryNode($nav, $entry);
    $nodeId = $node->id;

    Craft::$app->getElements()->deleteElement($entry, true);

    expect(Node::find()->id($nodeId)->status(null)->one())->toBeNull();
});

it('does not sync overridden node titles when the linked entry title changes', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $node = NavigationFixtureFactory::entryNode($nav, $entry);

    $node->title = 'Custom menu label';
    Craft::$app->getElements()->saveElement($node);

    $entry->title = 'Updated entry title';
    Craft::$app->getElements()->saveElement($entry);

    $reloaded = Node::find()->id($node->id)->status(null)->one();

    expect($reloaded?->title)->toBe('Custom menu label');
});

it('removes dynamic section nodes when the source section is deleted', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $node = NavigationFixtureFactory::dynamicSectionNode($nav, $section);
    $nodeId = $node->id;

    Craft::$app->getEntries()->deleteSection($section);

    expect(Node::find()->id($nodeId)->status(null)->one())->toBeNull();
    expect($node->type)->toBe(Dynamic::class);
});

it('removes dynamic category group nodes when the source group is deleted', function() {
    $nav = NavigationFixtureFactory::menu();
    $group = NavigationFixtureFactory::categoryGroup();
    $node = NavigationFixtureFactory::dynamicCategoryGroupNode($nav, $group);
    $nodeId = $node->id;

    Craft::$app->getCategories()->deleteGroup($group);

    expect(Node::find()->id($nodeId)->status(null)->one())->toBeNull();
});
