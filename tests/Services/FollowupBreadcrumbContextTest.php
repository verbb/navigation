<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\Navigation as N;
use verbb\navigation\elements\Node;

function expectBreadcrumbText(string $html, string $title): void
{
    $document = new DOMDocument();
    $document->loadHTML('<?xml encoding="UTF-8">' . $html);
    $element = $document->getElementsByTagName('a')->item(0) ?? $document->getElementsByTagName('span')->item(0);
    expect($element)->not->toBeNull();
    expect($element->textContent)->toBe($title);
    expect($element->childNodes->length)->toBe(1);
    expect($element->firstChild->nodeType)->toBe(XML_TEXT_NODE);
}

it('encodes stored menu breadcrumb labels as text inside the generated link', function() {
    $menu = F::menu();
    $title = '<img src=x onerror="window.breadcrumbExecuted=true">';
    F::customNode($menu, $title, '/breadcrumb-encoding');

    W::withAbsoluteUrl('https://breadcrumb.test/breadcrumb-encoding', function() use ($menu, $title) {
        $crumb = N::$plugin->getMenuBreadcrumbs()->getBreadcrumbs($menu->handle)[0];
        expect($crumb['title'])->toBe($title);
        expectBreadcrumbText($crumb['link'], $title);
    });
});

it('encodes linked entry titles in generated URL breadcrumb links', function() {
    $entry = F::entries(1)[0];
    $entry->title = '<img src=x onerror="window.breadcrumbExecuted=true">';
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();

    W::withAbsoluteUrl('https://breadcrumb.test/' . $entry->uri, function() use ($entry) {
        $crumbs = N::$plugin->getBreadcrumbs()->getBreadcrumbs();
        $crumb = end($crumbs);
        expect($crumb['elementId'])->toBe($entry->id);
        expect($crumb['title'])->toBe($entry->title);
        expectBreadcrumbText($crumb['link'], $entry->title);
    });
});

it('encodes Dynamic source names in native element index labels', function() {
    $section = F::entrySection();
    $section->name = '<img src=x onerror="window.breadcrumbExecuted=true">';
    expect(Craft::$app->entries->saveSection($section))->toBeTrue();
    $menu = F::menu();
    $node = F::dynamicSectionNode($menu, $section);
    expectBreadcrumbText($node->getTypeLabelHtml(), $section->name);
});

it('orders and encodes projected breadcrumbs below deep stored ancestors', function() {
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $entry->title = '<img src=x onerror="window.breadcrumbExecuted=true">';
    expect(Craft::$app->elements->saveElement($entry))->toBeTrue();
    $menu = F::menu();
    $root = F::passiveNode($menu, '<b>Root & text</b>');
    $middle = F::customNode($menu, 'Middle', '/middle', $root);
    F::dynamicSectionNode($menu, $section, $middle);
    $url = rtrim(Craft::$app->sites->primarySite->getBaseUrl(), '/') . '/' . $entry->uri;

    W::withAbsoluteUrl($url, function() use ($menu, $entry, $section) {
        $crumbs = N::$plugin->getMenuBreadcrumbs()->getBreadcrumbs($menu->handle);
        expect(array_column($crumbs, 'title'))->toBe(['<b>Root & text</b>', 'Middle', $section->name, $entry->title]);
        expect(end($crumbs)['isProjected'])->toBeTrue();
        foreach ($crumbs as $crumb) {
            expectBreadcrumbText($crumb['link'], $crumb['title']);
        }
    });
});

it('orders deep stored ancestors and menu breadcrumbs from root to current', function() {
    $menu = F::menu();
    $root = F::customNode($menu, 'Root', '/root');
    $middle = F::customNode($menu, 'Middle', '/middle', $root);
    F::customNode($menu, 'Current', '/deep-current', $middle);

    W::withAbsoluteUrl('https://breadcrumb.test/deep-current', function() use ($menu) {
        $context = N::$plugin->getContextResolver()->resolve($menu->handle);
        expect(array_column($context->ancestors(), 'title'))->toBe(['Root', 'Middle']);
        expect(array_column(N::$plugin->getMenuBreadcrumbs()->getBreadcrumbs($menu->handle), 'title'))->toBe(['Root', 'Middle', 'Current']);
    });
});

it('keeps root siblings on the explicitly selected context site', function() {
    $site = F::secondarySite();
    $menu = F::menu();
    $root = F::customNode($menu, 'Primary root', '/site-root');
    $sibling = F::customNode($menu, 'Primary sibling', '/site-sibling');
    foreach ([$root, $sibling] as $node) {
        $localized = Node::find()->id($node->id)->siteId($site->id)->one();
        $localized->title = 'Localized ' . $node->title;
        $localized->url = $node->getRawUrl();
        expect(Craft::$app->elements->saveElement($localized, true, false))->toBeTrue();
    }

    W::withAbsoluteUrl('https://breadcrumb.test/site-root', function() use ($menu, $site) {
        $context = N::$plugin->getContextResolver()->resolve($menu->handle, ['siteId' => $site->id]);
        expect($context->current()?->siteId)->toBe($site->id);
        expect(array_column($context->siblings(), 'siteId'))->toBe([$site->id, $site->id]);
        expect(array_column($context->siblings(), 'title'))->toBe(['Localized Primary root', 'Localized Primary sibling']);
    });
});
