<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use craft\models\GqlSchema;

it('executes legacy and canonical per-menu node grants without leaking another menu', function(string $namespace) {
    $allowed = F::menu(); $denied = F::menu();
    $node = F::customNode($allowed, 'Allowed', '/allowed');
    F::customNode($denied, 'Denied', '/denied');
    $schema = new GqlSchema(['name' => 'Restricted nodes', 'scope' => [$namespace.'.'.$allowed->uid.':read']]);
    $query = 'query($a: String!, $b: String!) { allowed: navigationNodes(menuHandle: $a) { id title } denied: navigationNodes(menuHandle: $b) { id title } }';
    $result = Craft::$app->gql->executeQuery($schema, $query, ['a' => $allowed->handle, 'b' => $denied->handle]);
    expect($result)->not->toHaveKey('errors');
    expect($result['data']['allowed'])->toBe([['id' => (string)$node->id, 'title' => 'Allowed']]);
    expect($result['data']['denied'])->toBe([]);
})->with(['navigationMenus', 'navigationNavs']);

it('enforces linked section grants in the actual GraphQL response', function() {
    $menu = F::menu();
    $allowed = F::entrySection(); $denied = F::entrySection();
    $a = F::entries(1, $allowed)[0]; $b = F::entries(1, $denied)[0];
    $na = F::entryNode($menu, $a); $nb = F::entryNode($menu, $b);
    $schema = new GqlSchema(['name' => 'Restricted links', 'scope' => ['navigationMenus.'.$menu->uid.':read', 'sections.'.$allowed->uid.':read']]);
    $result = Craft::$app->gql->executeQuery($schema, 'query($h: String!) { navigationNodes(menuHandle: $h) { id element { id } } }', ['h' => $menu->handle]);
    expect($result)->not->toHaveKey('errors');
    expect($result['data']['navigationNodes'])->toBe([
        ['id' => (string)$na->id, 'element' => ['id' => (string)$a->id]],
        ['id' => (string)$nb->id, 'element' => null],
    ]);
});

it('enforces linked section grants on projected GraphQL children', function() {
    $menu = F::menu(); $allowed = F::entrySection(); $denied = F::entrySection();
    $a = F::entries(1, $allowed)[0]; $b = F::entries(1, $denied)[0];
    F::dynamicSectionNode($menu, $allowed); F::dynamicSectionNode($menu, $denied);
    $schema = new GqlSchema(['name' => 'Restricted projections', 'scope' => ['navigationMenus.'.$menu->uid.':read', 'sections.'.$allowed->uid.':read']]);
    $result = Craft::$app->gql->executeQuery($schema, 'query($h: String!) { navigationNodes(menuHandle: $h, level: 1, withNodeHierarchy: true) { children { element { id } } } }', ['h' => $menu->handle]);
    expect($result)->not->toHaveKey('errors');
    expect($result['data']['navigationNodes'])->toBe([
        ['children' => [['element' => ['id' => (string)$a->id]]]],
        ['children' => [['element' => null]]],
    ]);
});
