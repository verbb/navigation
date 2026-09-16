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
