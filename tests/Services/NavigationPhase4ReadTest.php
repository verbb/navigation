<?php

declare(strict_types=1);

use craft\fieldlayoutelements\CustomField;
use craft\fields\PlainText;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\Performance\QueryProfiler;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\elements\Menu;
use verbb\navigation\Navigation;
use verbb\navigation\services\NavigationCache;
use verbb\navigation\variables\NavigationVariable;

it('batch-hydrates menu elements with withMenu', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Item', '/item');
    $siteId = (int)Craft::$app->getSites()->getPrimarySite()->id;

    $field = new PlainText([
        'name' => 'Promo Heading',
        'handle' => 'promoHeading' . uniqid(),
    ]);
    Craft::$app->getFields()->saveField($field);

    $layout = new FieldLayout(['type' => Menu::class]);
    $contentTab = new FieldLayoutTab(['name' => 'Content']);
    $contentTab->setLayout($layout);
    $contentTab->setElements([
        Craft::$app->getFields()->createLayoutElement([
            'type' => CustomField::class,
            'fieldUid' => $field->uid,
        ]),
    ]);
    $layout->setTabs([$contentTab]);
    Craft::$app->getFields()->saveLayout($layout);

    $nav->setMenuFieldLayout($layout);
    Navigation::$plugin->getMenus()->saveMenu($nav);

    $menuElement = Menu::find()->id($nav->id)->siteId($siteId)->status(null)->one();
    $menuElement->setFieldValue($field->handle, 'Summer Sale');
    Craft::$app->getElements()->saveElement($menuElement);

    $nodes = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->withMenu()
        ->all();

    expect($nodes)->toHaveCount(1);
    expect($nodes[0]->getMenu()?->getFieldValue($field->handle))->toBe('Summer Sale');
});

it('skips projected dynamic section children when withProjectedChildren is false', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    NavigationFixtureFactory::entries(2, $section);
    NavigationFixtureFactory::dynamicSectionNode($nav, $section);

    $roots = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->level(1)
        ->withNodeHierarchy()
        ->withProjectedChildren(false)
        ->all();

    expect($roots)->toHaveCount(1);
    expect($roots[0]->getChildren())->toHaveCount(0);
});

it('invalidates cached nav reads when a projected section entry is saved', function() {
    Navigation::$plugin->getSettings()->cacheMode = NavigationCache::MODE_AUTO;

    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entries = NavigationFixtureFactory::entries(1, $section);
    NavigationFixtureFactory::dynamicSectionNode($nav, $section);

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];
    $cache = Navigation::$plugin->getNavigationCache();
    $query = (new NavigationVariable())->nodes($criteria);

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria, $entries, $cache, $query) {
        $variable = new NavigationVariable();
        $variable->nodes($criteria)->all();

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->not->toBeFalse();

        $entry = $entries[0];
        $entry->title = 'Updated Entry Title';
        Craft::$app->getElements()->saveElement($entry);

        expect(Craft::$app->getCache()->get($cache->buildCacheKey($query)))->toBeFalse();
    });
});

it('does not cache node queries that opt into withMenu', function() {
    Navigation::$plugin->getSettings()->cacheMode = NavigationCache::MODE_AUTO;

    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Item', '/item');

    $siteUrl = Craft::$app->getSites()->getPrimarySite()->getBaseUrl();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    WebRequestSimulator::withAbsoluteUrl($siteUrl, function() use ($criteria) {
        $variable = new NavigationVariable();
        $first = QueryProfiler::profile(fn() => $variable->nodes($criteria)->withMenu()->all());
        $second = QueryProfiler::profile(fn() => $variable->nodes($criteria)->withMenu()->all());

        expect($first['resultSize'])->toBe(1);
        expect($first['queries'])->toBeGreaterThan(0);
        expect($second['queries'])->toBeGreaterThan(0);
    });
});

it('projects dynamic category group children at read time', function() {
    $nav = NavigationFixtureFactory::menu();
    $group = NavigationFixtureFactory::categoryGroup();
    NavigationFixtureFactory::categories(2, $group);
    NavigationFixtureFactory::dynamicCategoryGroupNode($nav, $group);

    $roots = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->level(1)
        ->withNodeHierarchy()
        ->all();

    expect($roots)->toHaveCount(1);
    expect($roots[0]->getChildren())->toHaveCount(2);
    expect($roots[0]->getChildren()[0]->getIsProjected())->toBeTrue();
});
