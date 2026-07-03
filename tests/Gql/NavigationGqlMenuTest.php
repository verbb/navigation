<?php

declare(strict_types=1);

use craft\helpers\Gql as CraftGql;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\elements\Menu;
use verbb\navigation\gql\queries\MenuQuery;

afterEach(function (): void {
    Craft::$app->getGql()->setActiveSchema(null);
});

it('registers a handle_Menu GraphQL query for each saved menu', function() {
    $nav = NavigationFixtureFactory::menu();
    $menuElement = Menu::find()->id($nav->id)->status(null)->one();

    expect($menuElement)->not->toBeNull();

    $queries = MenuQuery::getQueries(false);

    expect($queries)->toHaveKey($nav->handle . '_Menu');
    expect($queries[$nav->handle . '_Menu']['args'])->toHaveKey('site');
});

it('resolves menu queries by site handle instead of language', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $nav = NavigationFixtureFactory::menu();
    $menuElement = Menu::find()->id($nav->id)->status(null)->one();

    expect($menuElement)->not->toBeNull();

    $queries = MenuQuery::getQueries(false);
    $resolve = $queries[$nav->handle . '_Menu']['resolve'];
    $result = $resolve(null, ['site' => $secondarySite->handle]);

    expect($result)->toBeInstanceOf(Menu::class);
    expect($result->id)->toBe($menuElement->id);
    expect($result->handle)->toBe($nav->handle);
});

it('returns navigationMenuBreadcrumbs for the current request', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/parent/child', function() use ($nav) {
        $schema = CraftGql::createFullAccessSchema();
        $query = <<<'GQL'
        query($handle: String!) {
          navigationMenuBreadcrumbs(menuHandle: $handle) {
            title
            current
            url
          }
        }
        GQL;

        $result = Craft::$app->getGql()->executeQuery($schema, $query, [
            'handle' => $nav->handle,
        ]);

        if (!empty($result['errors'])) {
            throw new RuntimeException('GraphQL query failed: ' . json_encode($result['errors']));
        }

        $breadcrumbs = $result['data']['navigationMenuBreadcrumbs'] ?? [];
        $titles = array_column($breadcrumbs, 'title');

        expect($titles)->toBe(['Parent', 'Child']);
        expect($breadcrumbs[1]['current'] ?? null)->toBeTrue();
    });
});

it('returns sibling nodes from navigationContext', function() {
    $nav = NavigationFixtureFactory::menu();
    $root1 = NavigationFixtureFactory::customNode($nav, 'One', '/one');
    NavigationFixtureFactory::customNode($nav, 'One Child', '/one/child', $root1);
    NavigationFixtureFactory::customNode($nav, 'Two', '/two');

    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/one/child', function() use ($nav) {
        $schema = CraftGql::createFullAccessSchema();
        $query = <<<'GQL'
        query($handle: String!) {
          navigationContext(menuHandle: $handle) {
            current {
              title
            }
            siblings {
              title
            }
          }
        }
        GQL;

        $result = Craft::$app->getGql()->executeQuery($schema, $query, [
            'handle' => $nav->handle,
        ]);

        if (!empty($result['errors'])) {
            throw new RuntimeException('GraphQL query failed: ' . json_encode($result['errors']));
        }

        $context = $result['data']['navigationContext'] ?? [];
        $siblingTitles = array_column($context['siblings'] ?? [], 'title');

        expect($context['current']['title'] ?? null)->toBe('One Child');
        expect($siblingTitles)->toContain('One Child');
        expect($siblingTitles)->not->toContain('Two');
    });
});
