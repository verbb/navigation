<?php

declare(strict_types=1);

use craft\helpers\Gql as CraftGql;
use craft\models\GqlSchema;
use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\elements\Menu;
use verbb\navigation\gql\queries\MenuQuery;
use verbb\navigation\helpers\Gql as NavigationGql;

afterEach(function (): void {
    Craft::$app->getGql()->setActiveSchema(null);
});

it('does not register GraphQL menu fields outside the schema scope', function() {
    $allowed = NavigationFixtureFactory::menu();
    $denied = NavigationFixtureFactory::menu();

    $schema = new GqlSchema([
        'name' => 'Restricted menus',
        'scope' => [
            'navigationMenus.' . $allowed->uid . ':read',
        ],
    ]);

    Craft::$app->getGql()->setActiveSchema($schema);

    expect(NavigationGql::canQueryMenu($allowed))->toBeTrue();
    expect(NavigationGql::canQueryMenu($denied))->toBeFalse();

    $queries = MenuQuery::getQueries(true);

    expect($queries)->toHaveKey($allowed->handle . '_Menu');
    expect($queries)->not->toHaveKey($denied->handle . '_Menu');
});

it('resolver returns null for menus outside an active restricted schema', function() {
    $allowed = NavigationFixtureFactory::menu();
    $denied = NavigationFixtureFactory::menu();

    $schema = new GqlSchema([
        'name' => 'Restricted resolver',
        'scope' => [
            'navigationMenus.' . $allowed->uid . ':read',
        ],
    ]);

    Craft::$app->getGql()->setActiveSchema($schema);

    // Register without token filter so both resolvers exist, then enforce at resolve time.
    $queries = MenuQuery::getQueries(false);
    $resolveDenied = $queries[$denied->handle . '_Menu']['resolve'];
    $resolveAllowed = $queries[$allowed->handle . '_Menu']['resolve'];

    expect($resolveDenied(null, []))->toBeNull();
    expect($resolveAllowed(null, []))->toBeInstanceOf(Menu::class);
});

it('rejects navigationContext for menus outside the schema scope', function() {
    $allowed = NavigationFixtureFactory::menu();
    $denied = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($denied, 'Denied', '/denied');

    $schema = new GqlSchema([
        'name' => 'Restricted context',
        'scope' => [
            'navigationMenus.' . $allowed->uid . ':read',
        ],
    ]);

    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/denied', function() use ($schema, $denied) {
        $query = <<<'GQL'
        query($handle: String!) {
          navigationContext(menuHandle: $handle) {
            current {
              title
            }
          }
        }
        GQL;

        $result = Craft::$app->getGql()->executeQuery($schema, $query, [
            'handle' => $denied->handle,
        ]);

        expect($result['data']['navigationContext'] ?? null)->toBeNull();
        expect($result['errors'] ?? [])->not->toBeEmpty();
    });
});

it('still resolves navigationContext under a full-access schema', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Home', '/');

    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');

    WebRequestSimulator::withAbsoluteUrl($siteUrl . '/', function() use ($nav) {
        $schema = CraftGql::createFullAccessSchema();
        $query = <<<'GQL'
        query($handle: String!) {
          navigationContext(menuHandle: $handle) {
            current {
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

        expect($result['data']['navigationContext']['current']['title'] ?? null)->toBe('Home');
    });
});

it('hides linked entry elements outside the schema section scope', function() {
    $nav = NavigationFixtureFactory::menu();
    $allowedSection = NavigationFixtureFactory::entrySection();
    $deniedSection = NavigationFixtureFactory::entrySection();
    $allowedEntry = NavigationFixtureFactory::entries(1, $allowedSection)[0];
    $deniedEntry = NavigationFixtureFactory::entries(1, $deniedSection)[0];
    $allowedNode = NavigationFixtureFactory::entryNode($nav, $allowedEntry);
    $deniedNode = NavigationFixtureFactory::entryNode($nav, $deniedEntry);

    $schema = new GqlSchema([
        'name' => 'Restricted linked elements',
        'scope' => [
            'navigationMenus.' . $nav->uid . ':read',
            'sections.' . $allowedSection->uid . ':read',
        ],
    ]);

    Craft::$app->getGql()->setActiveSchema($schema);

    expect(NavigationGql::canQueryNodeElement($allowedNode))->toBeTrue();
    expect(NavigationGql::canQueryNodeElement($deniedNode))->toBeFalse();
});
