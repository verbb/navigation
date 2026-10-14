<?php

declare(strict_types=1);

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use Tests\Support\WebRequestSimulator as W;
use verbb\navigation\elements\Node;
use verbb\navigation\helpers\DynamicProjectionSettings;
use verbb\navigation\variables\NavigationVariable;

it('positions projected children consistently across navigation output', function(string $position, bool $projectedFirst) {
    $menu = F::menu();
    $section = F::entrySection();
    $entry = F::entries(1, $section)[0];
    $dynamic = F::dynamicSectionNode($menu, $section, null, [
        DynamicProjectionSettings::POSITION_DATA_KEY => $position,
    ]);
    $manual = F::customNode($menu, 'Manual child', '/manual', $dynamic);
    $expectedTitles = $projectedFirst
        ? [$entry->title, $manual->title]
        : [$manual->title, $entry->title];

    W::withAbsoluteUrl('https://example.test/', function() use ($menu, $dynamic, $expectedTitles) {
        $variable = new NavigationVariable();

        foreach ([1, 2] as $read) {
            $roots = $variable->nodes(['handle' => $menu->handle])->level(1)->all();
            expect($roots)->toHaveCount(1);
            expect(array_column($roots[0]->children->all(), 'title'))->toBe($expectedTitles);

            $flat = $variable->nodes(['handle' => $menu->handle])->all();
            expect(array_column($flat, 'title'))->toBe(array_merge([$dynamic->title], $expectedTitles));

            $tree = $variable->tree($menu->handle);
            expect(array_column($tree[0]['children'], 'title'))->toBe($expectedTitles);

            $html = (string)$variable->render($menu->handle);
            expect($html)->toContain($expectedTitles[0], $expectedTitles[1]);
            expect(strpos($html, $expectedTitles[0]))->toBeLessThan(strpos($html, $expectedTitles[1]));
        }
    });
})->with([
    'after manual children' => [DynamicProjectionSettings::POSITION_AFTER, false],
    'before manual children' => [DynamicProjectionSettings::POSITION_BEFORE, true],
]);

it('defaults missing and invalid projected child positions to after manual children', function(mixed $position) {
    $node = new Node(['data' => []]);

    if ($position !== null) {
        $node->data[DynamicProjectionSettings::POSITION_DATA_KEY] = $position;
    }

    expect(DynamicProjectionSettings::getPosition($node))->toBe(DynamicProjectionSettings::POSITION_AFTER);
})->with([
    'missing' => [null],
    'unknown' => ['somewhere'],
    'non-string' => [true],
]);

it('normalizes an invalid projected child position when saving a Dynamic node', function() {
    $menu = F::menu();
    $section = F::entrySection();
    $dynamic = F::dynamicSectionNode($menu, $section, null, [
        DynamicProjectionSettings::POSITION_DATA_KEY => 'somewhere',
    ]);

    $reloaded = Node::find()->id($dynamic->id)->status(null)->one();

    expect($reloaded->data[DynamicProjectionSettings::POSITION_DATA_KEY])
        ->toBe(DynamicProjectionSettings::POSITION_AFTER);
});

it('renders the projected child position in the Dynamic node editor', function() {
    $menu = F::menu();
    $section = F::entrySection();
    $dynamic = F::dynamicSectionNode($menu, $section, null, [
        DynamicProjectionSettings::POSITION_DATA_KEY => DynamicProjectionSettings::POSITION_BEFORE,
    ]);

    $html = W::withAbsoluteUrl(
        'https://example.test/admin/navigation',
        fn(): ?string => $dynamic->nodeType()->getEditorHtml(),
    );

    expect($html)
        ->toContain('Projected items position')
        ->toContain('data[projectedChildrenPosition]')
        ->toContain('value="before" selected');
});
