<?php

use craft\controllers\ElementIndexesController;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\conditions\entries\SectionConditionRule;
use craft\elements\conditions\IdConditionRule;
use craft\elements\conditions\UriConditionRule;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use craft\services\ElementSources;
use Tests\Support\Fixtures\NavigationFixtureFactory as Fixtures;
use verbb\navigation\helpers\ElementPickerHelper;
use verbb\navigation\fieldlayoutelements\NodeTypeElements;
use verbb\navigation\Navigation;
use verbb\navigation\nodetypes\Entry as EntryNodeType;

test('hidden singles preserve entry picker source filters', function(string $sourceType, bool $hideSingle) {
    $section = Fixtures::entrySection();
    $entries = Fixtures::entries(2, $section);
    $other = Fixtures::entries(1)[0];
    $single = Fixtures::homepageEntry();

    boundaryRequest(function() use ($sourceType, $hideSingle, $section, $entries, $other, $single) {
        $rules = match ($sourceType) {
            'custom section' => [[
                'class' => SectionConditionRule::class,
                'operator' => 'in',
                'values' => [$section->uid],
            ]],
            'custom sections' => [[
                'class' => SectionConditionRule::class,
                'operator' => 'in',
                'values' => [$section->uid, $single->getSection()->uid],
            ]],
            'custom ID' => [[
                'class' => IdConditionRule::class,
                'operator' => '=',
                'value' => $entries[0]->id,
            ]],
            default => [],
        };
        $source = [
            'type' => str_starts_with($sourceType, 'custom') ? ElementSources::TYPE_CUSTOM : ElementSources::TYPE_NATIVE,
            'condition' => ['class' => EntryCondition::class, 'elementType' => Entry::class, 'conditionRules' => $rules],
        ];
        $picker = ElementPickerHelper::getPickerConfig([
            EntryNodeType::class => [
                'enabled' => true,
                'permissions' => '*',
                'hideSingleSectionUids' => $hideSingle ? [$single->getSection()->uid] : [],
                'selectionCondition' => [
                    'class' => EntryCondition::class,
                    'conditionRules' => [[
                        'class' => UriConditionRule::class,
                        'operator' => '!=',
                        'value' => $entries[1]->uri,
                    ]],
                ],
            ],
        ], EntryNodeType::class, Entry::class);

        // Native source criteria and picker criteria are merged by Craft's element index.
        $criteria = $sourceType === 'native section' ? ['sectionId' => $section->id] : [];
        Craft::$app->getRequest()->setBodyParams([
            'baseCriteria' => array_merge($criteria, $picker['criteria'] ?? []),
            'condition' => $picker['condition'] ?? null,
        ]);
        $controller = new class('element-indexes', Craft::$app) extends ElementIndexesController {
            public function pickerQuery(array $source): ElementQueryInterface
            {
                $this->elementType = Entry::class;
                $this->source = $source;
                $this->condition = $this->condition();
                return $this->elementQuery();
            }
        };
        $query = $controller->pickerQuery($source);
        $query->andWhere(['elements.id' => [$entries[0]->id, $entries[1]->id, $other->id, $single->id]]);
        $expected = [$entries[0]->id];
        if ($sourceType === 'all') {
            $expected[] = $other->id;
        }
        if (!$hideSingle && in_array($sourceType, ['all', 'custom sections'], true)) {
            $expected[] = $single->id;
        }
        expect(array_map('intval', $query->ids()))->toEqualCanonicalizing($expected);
    });
})->with(['custom section', 'custom sections', 'custom ID', 'native section', 'all'])->with([true, false]);

test('linked entry editor preserves hidden single conditions', function() {
    $menu = Fixtures::menu();
    $entry = Fixtures::entries(1)[0];
    $single = Fixtures::homepageEntry();
    $menu->permissions = [EntryNodeType::class => [
        'enabled' => true,
        'permissions' => '*',
        'hideSingleSectionUids' => [$single->getSection()->uid],
    ]];
    expect(Navigation::$plugin->getMenus()->saveMenu($menu))->toBeTrue();
    $node = Fixtures::entryNode($menu, $entry);

    boundaryRequest(function() use ($node, $entry, $single) {
        $view = Craft::$app->getView();
        $view->startJsBuffer();
        try {
            (new NodeTypeElements())->formHtml($node);
        } finally {
            $js = $view->clearJsBuffer();
        }
        expect(preg_match('/new Craft\\.BaseElementSelectInput\\((\{[^\n]+\})\\);/', $js, $matches))->toBe(1);
        $settings = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        $query = Entry::find()->andWhere(['elements.id' => [$entry->id, $single->id]]);
        if ($settings['condition']) {
            $condition = Craft::$app->getConditions()->createCondition($settings['condition']);
            $condition->modifyQuery($query);
            expect($condition->matchElement($entry))->toBeTrue()
                ->and($condition->matchElement($single))->toBeFalse();
        }
        Craft::configure($query, $settings['criteria'] ?? []);
        expect(array_map('intval', $query->ids()))->toBe([$entry->id]);
    });
});
