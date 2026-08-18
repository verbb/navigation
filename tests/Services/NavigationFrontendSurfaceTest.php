<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use Tests\Support\WebRequestSimulator;
use verbb\navigation\nodetypes\Passive;
use verbb\navigation\variables\NavigationVariable;

function withFrontendUrl(string $path, callable $callback): mixed
{
    $siteUrl = rtrim(Craft::$app->getSites()->getPrimarySite()->getBaseUrl(), '/');
    $absoluteUrl = str_starts_with($path, 'http') ? $path : $siteUrl . $path;

    return WebRequestSimulator::withAbsoluteUrl($absoluteUrl, $callback);
}

it('applies render class and attribute options to the nested list', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    NavigationFixtureFactory::customNode($nav, 'Child', '/parent/child', $parent);

    $html = (string)(new NavigationVariable())->render(['handle' => $nav->handle], [
        'ulClass' => 'nav-items',
        'liClass' => 'nav-item',
        'aClass' => 'nav-link',
        'activeClass' => 'nav-active',
        'currentClass' => 'nav-current',
        'hasChildrenClass' => 'nav-has-children',
        'ulAttributes' => ['data-menu' => 'main'],
        'aAttributes' => ['data-track' => 'header'],
    ]);

    expect($html)->toContain('class="nav-items"');
    expect($html)->toContain('nav-item');
    expect($html)->toContain('nav-link');
    expect($html)->toContain('nav-has-children');
    expect($html)->toContain('data-menu="main"');
    expect($html)->toContain('data-track="header"');
});

it('renders passive nodes as spans instead of empty anchors', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::passiveNode($nav, 'Shop');

    $html = (string)(new NavigationVariable())->render(['handle' => $nav->handle]);

    expect($html)->toContain('<span');
    expect($html)->toContain('Shop');
    expect($html)->not->toMatch('/<a[^>]*>Shop<\/a>/');
});

it('uses getTag for passive node link helpers', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::passiveNode($nav, 'Shop');

    expect($node->getTag())->toBe(Passive::getTag());
    expect((string)$node->getLink())->toStartWith('<span');
    expect((string)$node->getLinkAttributes())->not->toContain('href=');
});

it('renders newWindow as target and rel on custom nodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Docs', 'https://example.com/docs');
    $node->newWindow = true;
    Craft::$app->getElements()->saveElement($node);

    $html = (string)(new NavigationVariable())->render(['handle' => $nav->handle]);

    expect($html)->toContain('target="_blank"');
    expect($html)->toContain('rel="noopener"');
});

it('merges customAttributes into render markup and linkAttributes', function() {
    $nav = NavigationFixtureFactory::menu();
    $node = NavigationFixtureFactory::customNode($nav, 'Tracked', '/tracked');
    $node->classes = 'nav-featured';
    $node->customAttributes = [
        ['attribute' => 'data-track', 'value' => 'cta'],
    ];
    Craft::$app->getElements()->saveElement($node);

    $html = (string)(new NavigationVariable())->render(['handle' => $nav->handle]);
    $attrs = (string)$node->getLinkAttributes(['class' => 'extra']);

    expect($html)->toContain('data-track="cta"');
    expect($html)->toContain('nav-featured');
    expect($attrs)->toContain('data-track="cta"');
    expect($attrs)->toContain('nav-featured');
    expect($attrs)->toContain('extra');
});

it('opts into linked elements on tree payloads', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    NavigationFixtureFactory::entryNode($nav, $entry);

    $variable = new NavigationVariable();
    $without = $variable->tree(['handle' => $nav->handle]);
    $with = $variable->tree(['handle' => $nav->handle], ['withLinkedElements' => true]);

    expect($without[0]['element'])->toBeNull();
    expect($with[0]['element']['id'] ?? $with[0]['element']['title'] ?? null)->not->toBeNull();
});

it('returns a stored-node sibling and ancestor context for the current page', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Services', '/services');
    NavigationFixtureFactory::customNode($nav, 'Consulting', '/services/consulting', $parent);
    NavigationFixtureFactory::customNode($nav, 'Workshops', '/services/workshops', $parent);

    withFrontendUrl('/services/consulting', function() use ($nav) {
        $ctx = (new NavigationVariable())->context($nav->handle);

        expect($ctx->current()?->title)->toBe('Consulting');
        expect($ctx->parent()?->title)->toBe('Services');
        expect(array_map(static fn($node) => $node->title, $ctx->ancestors()))->toBe(['Services']);
        expect(array_map(static fn($node) => $node->title, $ctx->siblings()))->toBe(['Consulting', 'Workshops']);
        expect(array_map(static fn($node) => $node->title, $ctx->branch()))->toBe(['Services', 'Consulting', 'Workshops']);
    });
});

it('builds menu breadcrumbs through stored ancestors', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'About', '/about');
    NavigationFixtureFactory::customNode($nav, 'Team', '/about/team', $parent);

    withFrontendUrl('/about/team', function() use ($nav) {
        $crumbs = (new NavigationVariable())->menuBreadcrumbs($nav->handle);

        expect(array_column($crumbs, 'title'))->toBe(['About', 'Team']);
        expect($crumbs[1]['current'])->toBeTrue();
        expect($crumbs[1]['isProjected'])->toBeFalse();
    });
});

it('includes projected dynamic children in menu breadcrumbs', function() {
    $nav = NavigationFixtureFactory::menu();
    $section = NavigationFixtureFactory::entrySection();
    $entries = NavigationFixtureFactory::entries(2, $section);
    NavigationFixtureFactory::dynamicSectionNode($nav, $section);

    $entry = \craft\elements\Entry::find()->id($entries[1]->id)->status(null)->one();
    $entryPath = '/' . ltrim((string)$entry->uri, '/');

    withFrontendUrl($entryPath, function() use ($nav, $entry, $section) {
        $crumbs = (new NavigationVariable())->menuBreadcrumbs($nav->handle);

        expect($crumbs)->toHaveCount(2);
        expect($crumbs[0]['title'])->toBe($section->name);
        expect($crumbs[1]['isProjected'])->toBeTrue();
        expect($crumbs[1]['current'])->toBeTrue();
        expect($crumbs[1]['node']->elementId)->toBe($entry->id);
        expect($crumbs[1]['title'])->not->toBe('');
    });
});

it('walks url breadcrumbs from request segments and matching entries', function() {
    $entries = NavigationFixtureFactory::entries(1);
    $entry = \craft\elements\Entry::find()->id($entries[0]->id)->status(null)->one();
    $entryPath = '/' . ltrim((string)$entry->uri, '/');

    expect($entry->uri)->not->toBeNull();

    withFrontendUrl($entryPath, function() use ($entry) {
        $crumbs = (new NavigationVariable())->urlBreadcrumbs();
        $ids = array_values(array_filter(array_column($crumbs, 'elementId')));

        expect($ids)->toContain($entry->id);
        expect(end($crumbs)['isElement'])->toBeTrue();
        expect(end($crumbs)['title'])->not->toBe('');
    });
});
