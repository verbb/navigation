<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use craft\base\Field;
use craft\helpers\StringHelper;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\Navigation;
use verbb\navigation\nodetypes\Custom;
use verbb\navigation\variables\NavigationVariable;

function nodeIsEnabledOnSite(Node $node): bool
{
    $isMultiSite = Craft::$app->getIsMultiSite() && count($node->getSupportedSites()) > 1;

    return $isMultiSite ? (bool)$node->getEnabledForSite() : (bool)$node->enabled;
}

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
        $this->markTestSkipped('No remaining site capacity; run against a fresh isolated test database.');
    }

    Navigation::$plugin->getSettings()->autoEnableNewSites = true;

    $nav = NavigationFixtureFactory::menu();
    $newSite = NavigationFixtureFactory::secondarySite('navigationAutoEnableSite' . uniqid());

    $reloaded = Navigation::$plugin->getMenus()->getMenuByHandle($nav->handle);
    $siteSettings = $reloaded->getSiteSettings();

    expect($siteSettings)->toHaveKey($newSite->id);
    expect($siteSettings[$newSite->id]->enabled)->toBeTrue();
});

it('does not rewrite menu config while applying a new site in read-only mode', function() {
    if (!Craft::$app->getSites()->getRemainingSites()) {
        $this->markTestSkipped('No remaining site capacity; run against a fresh isolated test database.');
    }

    Navigation::$plugin->getSettings()->autoEnableNewSites = true;

    $menu = NavigationFixtureFactory::menu();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $siteUid = StringHelper::UUID();
    $siteHandle = 'navigationAutoEnableSite' . uniqid();
    $projectConfig = Craft::$app->getProjectConfig();
    $config = $projectConfig->get();
    $config['sites'][$siteUid] = [
        'baseUrl' => "https://{$siteHandle}.test/",
        'enabled' => true,
        'handle' => $siteHandle,
        'hasUrls' => true,
        'language' => 'en-US',
        'name' => 'Read-only deployment site',
        'primary' => false,
        'siteGroup' => $primarySite->getGroup()->uid,
        'sortOrder' => count(Craft::$app->getSites()->getAllSites()) + 1,
    ];
    $config['navigation']['menus'][$menu->uid]['instructions'] = 'Applied with the new site';
    $config['navigation']['menus'][$menu->uid]['siteSettings'][$siteUid] = ['enabled' => true];

    $oldReadOnly = $projectConfig->readOnly;
    $projectConfig->readOnly = true;

    try {
        $projectConfig->applyConfigChanges($config);
    } finally {
        $projectConfig->readOnly = $oldReadOnly;
    }

    $newSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);
    $reloaded = Navigation::$plugin->getMenus()->getMenuById($menu->id);

    expect($newSite)->not->toBeNull();
    expect($reloaded->instructions)->toBe('Applied with the new site');
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

it('remaps linked element site when copying entry nodes to another site', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);

    $entry = NavigationFixtureFactory::entries(1)[0];
    $node = NavigationFixtureFactory::entryNode($nav, $entry);

    expect($node->getElementSiteId())->toBe($primarySite->id);

    $withoutRemap = Navigation::$plugin->getNodes()->copyNodesToSite(
        $nav->id,
        $primarySite->id,
        [$node->id],
        $secondarySite->id,
        false,
        false,
    );

    expect($withoutRemap['successCount'])->toBe(1);

    $copiedWithoutRemap = Node::find()
        ->id($withoutRemap['copiedNodeIds'][0])
        ->siteId($secondarySite->id)
        ->status(null)
        ->one();

    expect($copiedWithoutRemap)->not->toBeNull();
    expect(Navigation::$plugin->getNodeSites()->getSettings($copiedWithoutRemap->id, $secondarySite->id)?->linkedElementSiteId)
        ->toBe($primarySite->id);

    $secondEntry = NavigationFixtureFactory::entries(1)[0];
    $secondNode = NavigationFixtureFactory::entryNode($nav, $secondEntry);

    $withRemap = Navigation::$plugin->getNodes()->copyNodesToSite(
        $nav->id,
        $primarySite->id,
        [$secondNode->id],
        $secondarySite->id,
        false,
        true,
    );

    expect($withRemap['successCount'])->toBe(1);
    expect($withRemap['remappedLinkedElementCount'])->toBe(1);

    $copiedWithRemap = Node::find()
        ->id($withRemap['copiedNodeIds'][0])
        ->siteId($secondarySite->id)
        ->status(null)
        ->one();

    expect($copiedWithRemap)->not->toBeNull();
    expect(Navigation::$plugin->getNodeSites()->getSettings($copiedWithRemap->id, $secondarySite->id)?->linkedElementSiteId)
        ->toBe($secondarySite->id);
});

it('preserves hierarchy when copying nodes to another site with descendants', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);

    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent', null, $primarySite->id);
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent, $primarySite->id);

    $result = Navigation::$plugin->getNodes()->copyNodesToSite(
        $nav->id,
        $primarySite->id,
        [$parent->id],
        $secondarySite->id,
        true,
    );

    expect($result['successCount'])->toBe(2);
    expect($result['copiedNodeIds'])->toHaveCount(2);

    $copiedParent = Node::find()->id($result['copiedNodeIds'][0])->siteId($secondarySite->id)->status(null)->one();
    $copiedChild = Node::find()->id($result['copiedNodeIds'][1])->siteId($secondarySite->id)->status(null)->one();

    expect($copiedParent)->not->toBeNull();
    expect($copiedChild)->not->toBeNull();
    expect($copiedChild->getParent()?->id)->toBe($copiedParent->id);

    $roots = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle, 'siteId' => $secondarySite->id])
        ->level(1)
        ->all();

    expect($roots)->toHaveCount(1);
    expect($roots[0]->getChildren())->toHaveCount(1);
    expect($roots[0]->getChildren()[0]->title)->toBe('Child');

    WebRequestSimulator::withAbsoluteUrl(
        $secondarySite->getBaseUrl(),
        function() use ($nav, $secondarySite) {
            $roots = (new NavigationVariable())
                ->nodes(['handle' => $nav->handle, 'siteId' => $secondarySite->id])
                ->level(1)
                ->all();

            expect($roots)->toHaveCount(1);
            expect($roots[0]->getChildren())->toHaveCount(1);
            expect($roots[0]->children->count())->toBe(1);
        },
    );
});

it('remaps parent relationships when copying a parent-child selection to another site', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);

    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent', null, $primarySite->id);
    $child = NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent, $primarySite->id);

    $result = Navigation::$plugin->getNodes()->copyNodesToSite(
        $nav->id,
        $primarySite->id,
        [$parent->id, $child->id],
        $secondarySite->id,
    );

    expect($result['successCount'])->toBe(2);

    $copiedParentId = null;

    foreach ($result['copiedNodeIds'] as $copiedNodeId) {
        $copiedNode = Node::find()->id($copiedNodeId)->siteId($secondarySite->id)->status(null)->one();

        if ($copiedNode?->title === 'Parent') {
            $copiedParentId = $copiedNode->id;
            expect($copiedNode->getParent())->toBeNull();
        }
    }

    expect($copiedParentId)->not->toBeNull();

    $copiedChild = Node::find()
        ->menuId($nav->id)
        ->siteId($secondarySite->id)
        ->title('Child')
        ->status(null)
        ->one();

    expect($copiedChild)->not->toBeNull();
    expect($copiedChild->getParent()?->id)->toBe($copiedParentId);
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

it('disables propagated site copies when enabledForPropagatedSites is false', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();

    $node = new Node([
        'menuId' => $nav->id,
        'siteId' => $primarySite->id,
        'type' => Custom::class,
        'title' => 'Site-only branch',
        'url' => '/site-only',
        'enabled' => true,
    ]);
    $node->setEnabledForPropagatedSitesPreference(false);

    expect(Craft::$app->getElements()->saveElement($node, true))->toBeTrue();

    $primaryReloaded = Node::find()->id($node->id)->siteId($primarySite->id)->status(null)->one();
    $secondaryReloaded = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect($primaryReloaded)->not->toBeNull();
    expect($secondaryReloaded)->not->toBeNull();
    expect(nodeIsEnabledOnSite($primaryReloaded))->toBeTrue();
    expect(nodeIsEnabledOnSite($secondaryReloaded))->toBeFalse();
});

it('uses the menu default when enabledForPropagatedSites is not set on the node', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();
    $nav->defaultEnabledForPropagatedSites = false;

    expect(Navigation::$plugin->getMenus()->saveMenu($nav))->toBeTrue();

    $node = new Node([
        'menuId' => $nav->id,
        'siteId' => $primarySite->id,
        'type' => Custom::class,
        'title' => 'Menu default disabled',
        'url' => '/menu-default-disabled',
        'enabled' => true,
    ]);

    expect(Craft::$app->getElements()->saveElement($node, true))->toBeTrue();

    $secondaryReloaded = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect($secondaryReloaded)->not->toBeNull();
    expect(nodeIsEnabledOnSite($secondaryReloaded))->toBeFalse();
});

it('keeps propagated site copies disabled when publishing a staged node with enabledForPropagatedSites false', function() {
    Navigation::$plugin->getSettings()->builderLiveStructure = false;

    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu();

    $node = new Node([
        'menuId' => $nav->id,
        'siteId' => $primarySite->id,
        'type' => Custom::class,
        'title' => 'Staged site-only branch',
        'url' => '/staged-site-only',
        'enabled' => true,
    ]);
    $node->setEnabledForPropagatedSitesPreference(false);
    $node->setPendingPublish(true);

    expect(Craft::$app->getElements()->saveElement($node, true))->toBeTrue();

    $secondaryBeforePublish = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();
    expect(nodeIsEnabledOnSite($secondaryBeforePublish))->toBeFalse();

    $reloaded = Node::find()->id($node->id)->siteId($primarySite->id)->status(null)->one();
    $reloaded->publishPendingAdd();
    expect(Craft::$app->getElements()->saveElement($reloaded))->toBeTrue();

    $primaryReloaded = Node::find()->id($node->id)->siteId($primarySite->id)->status(null)->one();
    $secondaryReloaded = Node::find()->id($node->id)->siteId($secondarySite->id)->status(null)->one();

    expect(nodeIsEnabledOnSite($primaryReloaded))->toBeTrue();
    expect(nodeIsEnabledOnSite($secondaryReloaded))->toBeFalse();
});

it('limits language-propagated nodes to sites sharing the same language', function() {
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $sameLanguage = NavigationFixtureFactory::secondarySite('navigationTestSameLanguage');
    $otherLanguage = NavigationFixtureFactory::secondarySite('navigationTestOtherLanguage');
    $sameLanguage->language = $primarySite->language;
    $otherLanguage->language = $primarySite->language === 'fr' ? 'de' : 'fr';
    expect(Craft::$app->getSites()->saveSite($sameLanguage))->toBeTrue();
    expect(Craft::$app->getSites()->saveSite($otherLanguage))->toBeTrue();

    $nav = NavigationFixtureFactory::menu(null, MenuSettings::PROPAGATION_METHOD_LANGUAGE);
    $node = NavigationFixtureFactory::customNode($nav, 'Lang item', '/lang-item', null, $primarySite->id);
    $supported = array_map('intval', $node->getSupportedSites());

    expect($supported)->toContain((int)$primarySite->id, (int)$sameLanguage->id)
        ->not->toContain((int)$otherLanguage->id);
    expect(Node::find()->id($node->id)->siteId($sameLanguage->id)->status(null)->one())->not->toBeNull();
    expect(Node::find()->id($node->id)->siteId($otherLanguage->id)->status(null)->one())->toBeNull();
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
