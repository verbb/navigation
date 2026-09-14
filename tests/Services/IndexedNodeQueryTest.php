<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\elements\Node;

it('preserves indexed query keys and wired children', function(bool $callback, bool $scoped) {
    $menu = F::menu();
    $parent = F::customNode($menu, 'Parent', '/parent');
    $child = F::customNode($menu, 'Child', '/child', $parent);
    W::withAbsoluteUrl('https://example.test/', function() use ($menu, $parent, $child, $callback, $scoped) {
        $query = Node::find()->menuId($menu->id)->withNodeHierarchy();
        if ($scoped) $query->id($parent->id);
        $query->indexBy($callback ? fn($node) => 'node-'.$node->id : 'id');
        foreach ([1,2] as $attempt) {
            $nodes = $query->all();
            $key = $callback ? 'node-'.$parent->id : $parent->id;
            expect(array_keys($nodes))->toBe($scoped ? [$key] : [$key, $callback ? 'node-'.$child->id : $child->id]);
            expect($nodes[$key]->getChildren()->one()->id)->toBe($child->id);
        }
    });
})->with([false,true])->with([false,true]);

it('indexes projected children after preserving their stored parent hierarchy', function() {
    $menu = F::menu();
    $section = F::entrySection();
    F::entries(2, $section);
    $parent = F::dynamicSectionNode($menu, $section);
    W::withAbsoluteUrl('https://example.test/', function() use ($menu, $parent) {
        $nodes = Node::find()->menuId($menu->id)->indexBy('id')->all();
        expect(count($nodes))->toBe(3);
        expect(isset($nodes[$parent->id]))->toBeTrue();
        foreach ($nodes as $key => $node) expect((string)$key)->toBe((string)$node->id);
        expect(count($nodes[$parent->id]->getProjectedChildren()))->toBe(2);
    });
});
