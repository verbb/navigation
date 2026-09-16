<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;
use verbb\navigation\variables\NavigationVariable;

it('reuses a configured node query without executing it as configuration', function() {
    $menu = F::menu();
    $node = F::customNode($menu, 'Reusable query', '/reusable-query');
    F::customNode($menu, 'Excluded node', '/excluded');
    $query = Node::find()->menuId($menu->id)->id($node->id)->withProjectedChildren(false);
    $variable = new NavigationVariable();
    expect($variable->nodes($query))->toBe($query);
    expect($query->ids())->toBe([$node->id]);
    expect($variable->tree(clone $query)[0]['title'])->toBe('Reusable query');
    $html = (string)$variable->render(clone $query);
    expect($html)->toContain('Reusable query')->not->toContain('Excluded node');
    expect($variable->getActiveNode(clone $query))->toBeNull();
    expect($variable->getActiveNodes(clone $query))->toBe([]);
    expect($variable->getCurrentNodes(clone $query))->toBe([]);
});
