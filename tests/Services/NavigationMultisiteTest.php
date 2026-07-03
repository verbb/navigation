<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use craft\base\Field;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation;
use verbb\navigation\variables\NavigationVariable;

it('stores custom urls per site for propagated nodes', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();

    $node = NavigationFixtureFactory::customNode($nav, 'Item', '/primary-url', null, $primarySite->id);
    $secondaryNode = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect($secondaryNode)->not->toBeNull();

    $secondaryNode->setUrl('/secondary-url');
    Craft::$app->getElements()->saveElement($secondaryNode);

    $primaryReloaded = Node::find()->id($node->id)->siteId($primarySite->id)->status(null)->one();
    $secondaryReloaded = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect($primaryReloaded?->getUrl(false))->toBe('/primary-url');
    expect($secondaryReloaded?->getUrl(false))->toBe('/secondary-url');
});

it('auto-enables existing menus when a new site is created', function() {
    if (!Craft::$app->getSites()->getRemainingSites()) {
        expect(true)->toBeTrue();

        return;
    }

    Navigation::$plugin->getSettings()->autoEnableNewSites = true;

    $nav = NavigationFixtureFactory::menu();
    $newSite = NavigationFixtureFactory::secondarySite('navigationAutoEnableSite' . uniqid());

    $reloaded = Navigation::$plugin->getMenus()->getMenuByHandle($nav->handle);
    $siteSettings = $reloaded->getSiteSettings();

    expect($siteSettings)->toHaveKey($newSite->id);
    expect($siteSettings[$newSite->id]->enabled)->toBeTrue();
});

it('copies a node to another site when propagation is none', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);

    $node = NavigationFixtureFactory::customNode($nav, 'Primary Item', '/primary-item', null, $primarySite->id);
    $duplicate = Navigation::$plugin->getNodes()->copyNodeToSite($node, $secondarySite->id);

    expect($duplicate)->not->toBeNull();
    expect($duplicate->id)->not->toBe($node->id);
    expect($duplicate->siteId)->toBe($secondarySite->id);
    expect($duplicate->title)->toBe('Primary Item');

    $secondaryNodes = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle, 'siteId' => $secondarySite->id])
        ->all();

    expect(array_map(static fn(Node $node): string => $node->title, $secondaryNodes))
        ->toBe(['Primary Item']);
});

it('creates matching node titles on propagated sites', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();

    $node = NavigationFixtureFactory::customNode($nav, 'Shared title', '/item', null, $primarySite->id);
    $secondaryNode = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect($secondaryNode)->not->toBeNull();
    expect($secondaryNode->title)->toBe('Shared title');
    expect($secondaryNode->id)->toBe($node->id);
});

it('limits language-propagated nodes to sites sharing the same language', function() {
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu(null, MenuSettings::PROPAGATION_METHOD_LANGUAGE);
    $node = NavigationFixtureFactory::customNode($nav, 'Lang item', '/lang-item', null, $primarySite->id);

    expect($node->getSupportedSites())->not->toBeEmpty();

    foreach ($node->getSupportedSites() as $siteId) {
        $site = Craft::$app->getSites()->getSiteById((int)$siteId);

        expect($site)->not->toBeNull();
        expect($site->language)->toBe($primarySite->language);
    }
});

it('propagates node title overrides to sites in the same site group', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();

    $nav->titleTranslationMethod = Field::TRANSLATION_METHOD_SITE_GROUP;
    expect(Navigation::$plugin->getMenus()->saveMenu($nav))->toBeTrue();

    $node = NavigationFixtureFactory::customNode($nav, 'Original title', '/item', null, $primarySite->id);
    $node->title = 'Shared override';
    expect(Craft::$app->getElements()->saveElement($node, true))->toBeTrue();

    $secondaryNode = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect($secondaryNode)->not->toBeNull();
    expect($secondaryNode->title)->toBe('Shared override');
});

it('keeps node title overrides per site when title translation is site-specific', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();

    $nav->titleTranslationMethod = Field::TRANSLATION_METHOD_SITE;
    expect(Navigation::$plugin->getMenus()->saveMenu($nav))->toBeTrue();

    $node = NavigationFixtureFactory::customNode($nav, 'Original title', '/item', null, $primarySite->id);
    $node->title = 'Primary-only override';
    expect(Craft::$app->getElements()->saveElement($node, true))->toBeTrue();

    $secondaryNode = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect($secondaryNode)->not->toBeNull();
    expect($secondaryNode->title)->toBe('Original title');
});
