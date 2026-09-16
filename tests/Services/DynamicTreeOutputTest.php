<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\variables\NavigationVariable;

it('applies the tree linked-element output option to stored and dynamic children', function(array $options) {
    $menu = F::menu();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $dynamic = F::dynamicSectionNode($menu, $section);
    F::entryNode($menu, $entry, $dynamic);

    W::withAbsoluteUrl($entry->getUrl(), function() use ($menu, $entry, $options) {
        $variable = new NavigationVariable();
        foreach ([1, 2] as $read) {
            $tree = $variable->tree($menu->handle, $options);
            expect($tree[0]['children'])->toHaveCount(2);
            foreach ($tree[0]['children'] as $child) {
                expect($child['title'])->toBe($entry->title);
                expect($child['url'])->toBe($entry->getUrl());
                if ($options['withLinkedElements'] ?? false) {
                    expect($child['element']['id'])->toBe($entry->id);
                } else {
                    expect($child['element'])->toBeNull();
                }
            }
        }
    });
})->with(['default' => [[]], 'disabled' => [['withLinkedElements' => false]], 'enabled' => [['withLinkedElements' => true]]]);
