<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\variables\NavigationVariable;

it('renders dynamic children with the default menu helper', function() {
    $menu = F::menu();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $root = F::customNode($menu, 'Root', '/root');
    $dynamic = F::dynamicSectionNode($menu, $section, $root);
    $stored = F::customNode($menu, 'Manual child', '/manual', $dynamic);
    $stored->newWindow = true;
    $stored->customAttributes = [['attribute' => 'data-track', 'value' => 'stored']];
    expect(Craft::$app->elements->saveElement($stored))->toBeTrue();

    $url = rtrim(Craft::$app->sites->primarySite->getBaseUrl(), '/') . '/' . $entry->uri;
    W::withAbsoluteUrl($url, function() use ($menu, $entry) {
        $html = (string)(new NavigationVariable())->render($menu->handle);
        expect($html)->toContain($entry->title, 'href="' . $entry->getUrl() . '"', 'aria-current="page"');
        expect($html)->toContain('Manual child', 'data-track="stored"');
        expect(substr_count($html, 'target="_blank"'))->toBe(1);
        expect(substr_count($html, '<li'))->toBe(4);
    });
});

it('renders every projected child without changing stored structure bounds', function(string $position, bool $manualChildren, bool $nested) {
    $menu = F::menu();
    $section = F::entrySection();
    $entries = F::entries(5, $section);
    $root = $nested ? F::customNode($menu, 'Outer root', '/outer') : null;
    $dynamic = F::dynamicSectionNode($menu, $section, $root, [
        'projectedChildrenPosition' => $position,
        'orderBy' => 'title asc',
    ]);
    $manual = $manualChildren ? F::customNode($menu, 'Manual child', '/manual', $dynamic) : null;
    if ($manual) {
        F::customNode($menu, 'Manual grandchild', '/grandchild', $manual);
    }
    F::customNode($menu, 'Following sibling', '/following', $root);
    $bounds = fn() => (new craft\db\Query())->from('{{%structureelements}}')->where(['structureId' => $menu->structureId])->orderBy('elementId')->all();
    $originalBounds = $bounds();

    W::withAbsoluteUrl('https://example.test/', function() use ($menu, $entries, $position, $manualChildren, $nested, $originalBounds) {
        $variable = new NavigationVariable();
        foreach ([1, 2] as $read) {
            $flat = $variable->nodes($menu->handle)->all();
            foreach ($flat as $node) {
                if ($node instanceof \verbb\navigation\elements\Node) {
                    $stored = array_values(array_filter($originalBounds, fn($row) => (int)$row['elementId'] === $node->id))[0];
                    expect([$node->lft, $node->rgt])->toBe([(int)$stored['lft'], (int)$stored['rgt']]);
                }
            }
            $html = (string)$variable->render($menu->handle);
            $dom = new DOMDocument();
            $dom->loadHTML($html);
            $xpath = new DOMXPath($dom);
            $dynamicPath = $nested ? '//body/ul/li/ul/li[1]' : '//body/ul/li[1]';
            $titles = array_map(fn($element) => trim($element->textContent), iterator_to_array($xpath->query($dynamicPath . '/ul/li/*[self::a or self::span]')));
            $expected = array_column($entries, 'title');
            sort($expected);
            if ($manualChildren) {
                $expected = $position === 'before' ? [...$expected, 'Manual child'] : ['Manual child', ...$expected];
            }
            expect($titles)->toBe($expected);
            expect($xpath->query($dynamicPath . '/../li[2]/a')->item(0)->textContent)->toBe('Following sibling');
            expect(substr_count($html, '<li'))->toBe(7 + ($manualChildren ? 2 : 0) + ($nested ? 1 : 0));
        }
    });
    expect($bounds())->toBe($originalBounds);
})->with(['before', 'after'])->with([false, true])->with([false, true]);
