<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory;
use verbb\navigation\elements\Node;
use verbb\navigation\models\MenuSettings;
use verbb\navigation\variables\NavigationVariable;

it('builds a correct flat tree array', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::flatCustomNodes($nav, 5);

    $tree = (new NavigationVariable())->tree(['handle' => $nav->handle]);

    expect($tree)->toHaveCount(5);
    expect(array_column($tree, 'title'))->toBe(['Flat 1', 'Flat 2', 'Flat 3', 'Flat 4', 'Flat 5']);
    expect($tree[0])->toHaveKeys(['title', 'url', 'active', 'target', 'element']);
});

it('builds a correct deep nested tree array', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::deepCustomNodes($nav, 4);

    $tree = (new NavigationVariable())->tree(['handle' => $nav->handle]);

    expect($tree)->toHaveCount(1);
    expect($tree[0]['title'])->toBe('Level 1');
    expect($tree[0]['children'][0]['title'])->toBe('Level 2');
    expect($tree[0]['children'][0]['children'][0]['title'])->toBe('Level 3');
    expect($tree[0]['children'][0]['children'][0]['children'][0]['title'])->toBe('Level 4');
});

it('builds a correct wide branching tree array', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 3, 2);

    $tree = (new NavigationVariable())->tree(['handle' => $nav->handle]);

    expect($tree)->toHaveCount(3);
    expect(array_column($tree, 'title'))->toBe(['Root 1', 'Root 2', 'Root 3']);

    foreach ($tree as $root) {
        expect($root['children'])->toHaveCount(2);
    }
});

it('wires children through withNodeHierarchy on level-scoped queries', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 2, 3);

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $roots = $variable->nodes($criteria)->level(1)->withNodeHierarchy()->all();

    expect($roots)->toHaveCount(2);

    foreach ($roots as $root) {
        expect($root->getChildren())->toHaveCount(3);
        expect($root->getChildren()[0]->title)->toContain('Child');
    }
});

it('batch-hydrates linked elements with withLinkedElements', function() {
    $nav = NavigationFixtureFactory::menu();
    $entries = NavigationFixtureFactory::entries(3);

    foreach ($entries as $entry) {
        NavigationFixtureFactory::entryNode($nav, $entry);
    }

    $nodes = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->withLinkedElements()
        ->all();

    expect($nodes)->toHaveCount(3);

    foreach ($nodes as $index => $node) {
        expect($node->getElement()?->id)->toBe($entries[$index]->id);
    }
});

it('hydrates multiple linked element types in one batched pass', function() {
    $nav = NavigationFixtureFactory::menu();
    $entry = NavigationFixtureFactory::entries(1)[0];
    $category = NavigationFixtureFactory::categories(1)[0];

    NavigationFixtureFactory::entryNode($nav, $entry);
    NavigationFixtureFactory::categoryNode($nav, $category);

    $nodes = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->withLinkedElements()
        ->all();

    expect($nodes)->toHaveCount(2);

    $elementIds = array_map(static fn(Node $node) => $node->getElement()?->id, $nodes);
    sort($elementIds);

    expect($elementIds)->toBe([$entry->id, $category->id]);
});

it('combines withNodeHierarchy and withLinkedElements on mixed trees', function() {
    $nav = NavigationFixtureFactory::menu();
    $parent = NavigationFixtureFactory::customNode($nav, 'Parent', '/parent');
    $entry = NavigationFixtureFactory::entries(1)[0];
    NavigationFixtureFactory::entryNode($nav, $entry, $parent);
    NavigationFixtureFactory::passiveNode($nav, 'Passive', $parent);

    $roots = (new NavigationVariable())
        ->nodes(['handle' => $nav->handle])
        ->level(1)
        ->withNodeHierarchy()
        ->withLinkedElements()
        ->all();

    expect($roots)->toHaveCount(1);
    expect($roots[0]->getChildren())->toHaveCount(2);

    $entryChild = null;

    foreach ($roots[0]->getChildren() as $child) {
        if ($child->isElement()) {
            $entryChild = $child;
            break;
        }
    }

    expect($entryChild?->getElement()?->id)->toBe($entry->id);
});

it('excludes disabled nodes from default queries', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Enabled', '/enabled');
    NavigationFixtureFactory::disabledCustomNode($nav, 'Disabled', '/disabled');

    $titles = array_map(
        static fn(Node $node): string => $node->title,
        (new NavigationVariable())->nodes(['handle' => $nav->handle])->all(),
    );

    expect($titles)->toBe(['Enabled']);
});

it('includes disabled nodes when status is null', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::customNode($nav, 'Enabled', '/enabled');
    NavigationFixtureFactory::disabledCustomNode($nav, 'Disabled', '/disabled');

    $titles = array_map(
        static fn(Node $node): string => $node->title,
        (new NavigationVariable())->nodes(['handle' => $nav->handle])->status(null)->all(),
    );

    expect($titles)->toContain('Enabled', 'Disabled');
});

it('renders nested markup for wide trees', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 2, 2);

    $html = (string)(new NavigationVariable())->render(['handle' => $nav->handle]);

    expect($html)->toContain('Root 1');
    expect($html)->toContain('Root 1 Child 1');
    expect($html)->toContain('Root 2 Child 2');
    expect($html)->toContain('<ul');
    expect($html)->toContain('has-children');
});

it('returns passive nodes without urls in tree output', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::passiveNode($nav, 'Passive Group');

    $tree = (new NavigationVariable())->tree(['handle' => $nav->handle]);

    expect($tree)->toHaveCount(1);
    expect($tree[0]['title'])->toBe('Passive Group');
    expect($tree[0]['url'])->toBeNull();
});

it('scopes multisite nodes correctly', function() {
    $secondarySite = NavigationFixtureFactory::existingSecondarySite();
    $primarySite = Craft::$app->getSites()->getPrimarySite();
    $nav = NavigationFixtureFactory::menu(null, MenuSettings::PROPAGATION_METHOD_NONE);

    NavigationFixtureFactory::customNode($nav, 'Primary', '/primary', null, $primarySite->id);
    NavigationFixtureFactory::customNode($nav, 'Secondary', '/secondary', null, $secondarySite->id);

    $primaryTitles = array_map(
        static fn(Node $node): string => $node->title,
        (new NavigationVariable())->nodes(['handle' => $nav->handle, 'siteId' => $primarySite->id])->all(),
    );
    $secondaryTitles = array_map(
        static fn(Node $node): string => $node->title,
        (new NavigationVariable())->nodes(['handle' => $nav->handle, 'siteId' => $secondarySite->id])->all(),
    );

    expect($primaryTitles)->toBe(['Primary']);
    expect($secondaryTitles)->toBe(['Secondary']);
});

it('preserves custom field values on tree nodes', function() {
    $nav = NavigationFixtureFactory::menu();
    $field = NavigationFixtureFactory::addPlainTextFieldToMenu($nav);
    NavigationFixtureFactory::customNodeWithField($nav, $field, 'Promo Label');

    $nodes = (new NavigationVariable())->nodes(['handle' => $nav->handle])->all();

    expect($nodes)->toHaveCount(1);
    expect($nodes[0]->getFieldValue($field->handle))->toBe('Promo Label');
});

it('matches tree and render node counts for the same nav', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::wideCustomNodes($nav, 4, 2);

    $variable = new NavigationVariable();
    $criteria = ['handle' => $nav->handle, 'siteId' => Craft::$app->getSites()->getPrimarySite()->id];

    $tree = $variable->tree($criteria);
    $flatNodes = $variable->nodes($criteria)->all();
    $html = (string)$variable->render($criteria);

    expect($tree)->toHaveCount(4);
    expect($flatNodes)->toHaveCount(12);
    expect(substr_count($html, '<li'))->toBe(12);
});

it('accepts a menu handle string shorthand for nodes()', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::flatCustomNodes($nav, 2);

    $nodes = (new NavigationVariable())->nodes($nav->handle)->all();

    expect($nodes)->toHaveCount(2);
});

it('normalizes deprecated node query criteria keys to handle', function() {
    $nav = NavigationFixtureFactory::menu();
    NavigationFixtureFactory::flatCustomNodes($nav, 2);

    $variable = new NavigationVariable();

    expect($variable->nodes(['navHandle' => $nav->handle])->all())->toHaveCount(2);
    expect($variable->nodes(['nav' => $nav->handle])->all())->toHaveCount(2);
    expect($variable->nodes(['menuHandle' => $nav->handle])->all())->toHaveCount(2);
});
