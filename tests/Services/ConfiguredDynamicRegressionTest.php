<?php

use Tests\Support\Fixtures\NavigationFixtureFactory as F;
use verbb\navigation\elements\Node;

it('retains a dynamic condition sort and limit after save and reload', function() {
    $menu = F::menu(); $section = F::entrySection();
    $entries = F::entries(4, $section);
    foreach (['Keep Z', 'Skip', 'Keep A', 'Keep M'] as $i => $title) {
        $entries[$i]->title = $title;
        expect(Craft::$app->elements->saveElement($entries[$i]))->toBeTrue();
    }
    $node = F::dynamicSectionNode($menu, $section, null, [
        'orderBy' => 'title asc', 'limit' => 2,
        'entryCondition' => ['class' => craft\elements\conditions\entries\EntryCondition::class,
            'conditionRules' => [['class' => craft\elements\conditions\TitleConditionRule::class, 'operator' => '**', 'value' => 'Keep']]],
    ]);
    $reloaded = Node::find()->id($node->id)->withProjectedChildren(false)->one();
    $children = verbb\navigation\Navigation::$plugin->getDynamicSources()->getProjectedChildren($reloaded, $reloaded->siteId);
    expect(array_map(fn($child) => $child->getElement()->id, $children))->toBe([$entries[2]->id, $entries[3]->id]);
});

it('keeps dynamic conditions within the authorized source and public status', function() {
    $menu = F::menu();
    $allowedSection = F::entrySection();
    $deniedSection = F::entrySection();
    $allowedEntry = F::entries(1, $allowedSection)[0];
    $deniedEntry = F::entries(1, $deniedSection)[0];
    $deniedEntry->enabled = false;
    expect(Craft::$app->getElements()->saveElement($deniedEntry))->toBeTrue();

    $node = F::dynamicSectionNode($menu, $allowedSection, null, [
        'entryCondition' => [
            'class' => craft\elements\conditions\entries\EntryCondition::class,
            'conditionRules' => [
                [
                    'class' => craft\elements\conditions\entries\SectionConditionRule::class,
                    'operator' => 'in',
                    'values' => [$deniedSection->uid],
                ],
                [
                    'class' => craft\elements\conditions\StatusConditionRule::class,
                    'operator' => 'in',
                    'values' => [craft\base\Element::STATUS_DISABLED],
                ],
            ],
        ],
    ]);

    $children = verbb\navigation\Navigation::$plugin->getDynamicSources()->getProjectedChildren($node, $node->siteId);

    expect(array_map(fn($child) => $child->getElement()->id, $children))->toBe([$allowedEntry->id]);
});
