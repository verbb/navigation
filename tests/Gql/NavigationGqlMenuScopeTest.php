<?php

declare(strict_types=1);

use craft\helpers\Gql as CraftGql;
use craft\helpers\StringHelper;
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
            'sites.' . Craft::$app->getSites()->getPrimarySite()->uid . ':read',
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

it('enforces active schema site scope on per-menu queries', function() {
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $menu = NavigationFixtureFactory::menu();
    $query = sprintf(
        'query($site: String!) { %s_Menu(site: $site) { ... on ElementInterface { siteId } } }',
        $menu->handle,
    );

    $restrictedSchema = new GqlSchema([
        'name' => 'Primary-site menu access',
        'uid' => StringHelper::UUID(),
        'scope' => [
            'sites.' . $primarySite->uid . ':read',
            'navigationMenus.' . $menu->uid . ':read',
        ],
    ]);

    $deniedResult = Craft::$app->getGql()->executeQuery($restrictedSchema, $query, [
        'site' => $secondarySite->handle,
    ]);
    $allowedResult = Craft::$app->getGql()->executeQuery($restrictedSchema, $query, [
        'site' => $primarySite->handle,
    ]);
    $allAllowedResult = Craft::$app->getGql()->executeQuery($restrictedSchema, $query, [
        'site' => '*',
    ]);

    expect($deniedResult['errors'] ?? [])->toBe([])
        ->and($deniedResult['data'][$menu->handle . '_Menu'])->toBeNull()
        ->and($allowedResult['errors'] ?? [])->toBe([])
        ->and((int)$allowedResult['data'][$menu->handle . '_Menu']['siteId'])->toBe((int)$primarySite->id)
        ->and($allAllowedResult['errors'] ?? [])->toBe([])
        ->and((int)$allAllowedResult['data'][$menu->handle . '_Menu']['siteId'])->toBe((int)$primarySite->id);

    $multiSiteSchema = new GqlSchema([
        'name' => 'Multi-site menu access',
        'uid' => StringHelper::UUID(),
        'scope' => [
            'sites.' . $primarySite->uid . ':read',
            'sites.' . $secondarySite->uid . ':read',
            'navigationMenus.' . $menu->uid . ':read',
        ],
    ]);
    $multiSiteResult = Craft::$app->getGql()->executeQuery($multiSiteSchema, $query, [
        'site' => $secondarySite->handle,
    ]);

    expect($multiSiteResult['errors'] ?? [])->toBe([])
        ->and((int)$multiSiteResult['data'][$menu->handle . '_Menu']['siteId'])->toBe((int)$secondarySite->id);
});

it('enforces active schema site scope when the menu site is omitted or empty', function() {
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $menu = NavigationFixtureFactory::menu();
    $schema = new GqlSchema([
        'name' => 'Secondary-only menu access',
        'uid' => StringHelper::UUID(),
        'scope' => [
            'sites.' . $secondarySite->uid . ':read',
            'navigationMenus.' . $menu->uid . ':read',
        ],
    ]);
    $query = sprintf(
        'query($site: String) { %s_Menu(site: $site) { ... on ElementInterface { siteId } } }',
        $menu->handle,
    );

    expect(Craft::$app->getSites()->getCurrentSite()->id)->toBe($primarySite->id);

    foreach ([[], ['site' => '']] as $variables) {
        $result = Craft::$app->getGql()->executeQuery($schema, $query, $variables);

        expect($result['errors'] ?? [])->toBe([])
            ->and($result['data'][$menu->handle . '_Menu'])->toBeNull();
    }
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
            'sites.' . Craft::$app->getSites()->getPrimarySite()->uid . ':read',
            'navigationMenus.' . $nav->uid . ':read',
            'sections.' . $allowedSection->uid . ':read',
        ],
    ]);

    Craft::$app->getGql()->setActiveSchema($schema);

    expect(NavigationGql::canQueryNodeElement($allowedNode))->toBeTrue();
    expect(NavigationGql::canQueryNodeElement($deniedNode))->toBeFalse();
});

it('enforces active schema site scope on linked node elements', function(bool $withLinkedElements) {
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $menu = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entry = NavigationFixtureFactory::entries(1, $section)[0];
    $node = NavigationFixtureFactory::entryNode($menu, $entry);
    $node->setElementSiteId($secondarySite->id);

    expect(Craft::$app->getElements()->saveElement($node, true, false))->toBeTrue();

    $query = sprintf(
        'query($handle: String!) { navigationNodes(menuHandle: $handle, withLinkedElements: %s) { id element { id } } }',
        $withLinkedElements ? 'true' : 'false',
    );
    $variables = ['handle' => $menu->handle];
    $restrictedSchema = new GqlSchema([
        'name' => 'Primary-site linked element access',
        'uid' => StringHelper::UUID(),
        'scope' => [
            'sites.' . $primarySite->uid . ':read',
            'navigationMenus.' . $menu->uid . ':read',
            'sections.' . $section->uid . ':read',
        ],
    ]);
    $restrictedResult = Craft::$app->getGql()->executeQuery($restrictedSchema, $query, $variables);

    expect($restrictedResult['errors'] ?? [])->toBe([])
        ->and($restrictedResult['data']['navigationNodes'])->toBe([[
            'id' => (string)$node->id,
            'element' => null,
        ]]);

    $multiSiteSchema = new GqlSchema([
        'name' => 'Multi-site linked element access',
        'uid' => StringHelper::UUID(),
        'scope' => [
            'sites.' . $primarySite->uid . ':read',
            'sites.' . $secondarySite->uid . ':read',
            'navigationMenus.' . $menu->uid . ':read',
            'sections.' . $section->uid . ':read',
        ],
    ]);
    $multiSiteResult = Craft::$app->getGql()->executeQuery($multiSiteSchema, $query, $variables);

    expect($multiSiteResult['errors'] ?? [])->toBe([])
        ->and($multiSiteResult['data']['navigationNodes'])->toBe([[
            'id' => (string)$node->id,
            'element' => ['id' => (string)$entry->id],
        ]]);
})->with([false, true]);
