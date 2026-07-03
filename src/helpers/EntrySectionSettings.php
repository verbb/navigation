<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

use Craft;
use craft\elements\conditions\entries\EntryCondition;
use craft\elements\db\EntryQuery;
use craft\elements\Entry as EntryElement;
use craft\helpers\Component;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\models\Section;

class EntrySectionSettings
{
    // Static Methods
    // =========================================================================

    public static function sectionOptions(): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Select a section'),
            'value' => '',
        ]];

        foreach (Craft::$app->getEntries()->getEditableSections() as $section) {
            if (!in_array($section->type, [Section::TYPE_CHANNEL, Section::TYPE_STRUCTURE], true)) {
                continue;
            }

            $typeLabel = $section->type === Section::TYPE_STRUCTURE
                ? Craft::t('app', 'Structure')
                : Craft::t('app', 'Channel');

            $options[] = [
                'label' => sprintf('%s (%s)', $section->name, $typeLabel),
                'value' => (string)$section->id,
            ];
        }

        return $options;
    }

    public static function orderByOptions(?Section $section = null): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Default'),
            'value' => self::ORDER_DEFAULT,
        ]];

        if ($section?->type === Section::TYPE_STRUCTURE) {
            $options[] = [
                'label' => Craft::t('navigation', 'Structure order'),
                'value' => self::ORDER_STRUCTURE,
            ];
        }

        return array_merge($options, [
            ['label' => Craft::t('navigation', 'Title (ascending)'), 'value' => self::ORDER_TITLE_ASC],
            ['label' => Craft::t('navigation', 'Title (descending)'), 'value' => self::ORDER_TITLE_DESC],
            ['label' => Craft::t('navigation', 'Post date (newest first)'), 'value' => self::ORDER_POST_DATE_DESC],
            ['label' => Craft::t('navigation', 'Post date (oldest first)'), 'value' => self::ORDER_POST_DATE_ASC],
        ]);
    }

    public static function getSectionFromNode(Node $node): ?Section
    {
        $sectionId = (int)($node->data['sectionId'] ?? 0);

        if (!$sectionId) {
            return null;
        }

        return Craft::$app->getEntries()->getSectionById($sectionId);
    }

    public static function getEntryCondition(Node $node): EntryCondition
    {
        $section = self::getSectionFromNode($node);

        if (!empty($node->data['entryCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                ['class' => EntryCondition::class, 'elementType' => EntryElement::class],
                $node->data['entryCondition'],
            ));
        } else {
            $condition = EntryElement::createCondition();
        }

        if ($section) {
            $condition->sourceKey = 'section:' . $section->uid;
        }

        return $condition;
    }

    public static function renderBasicFieldsHtml(Node $node): string
    {
        $section = self::getSectionFromNode($node);

        $html = Cp::selectFieldHtml([
            'label' => Craft::t('app', 'Section'),
            'instructions' => Craft::t('navigation', 'Choose a channel or structure section. Its entries will appear as children when the menu is rendered.'),
            'id' => 'sectionId',
            'name' => 'sectionId',
            'value' => $node->data['sectionId'] ?? '',
            'options' => self::sectionOptions(),
            'required' => true,
        ]);

        $html .= Cp::selectFieldHtml([
            'label' => Craft::t('navigation', 'Sort Order'),
            'instructions' => Craft::t('navigation', 'How projected entries should be ordered under this node.'),
            'id' => 'orderBy',
            'name' => 'orderBy',
            'value' => $node->data['orderBy'] ?? self::ORDER_DEFAULT,
            'options' => self::orderByOptions($section),
        ]);

        return $html;
    }

    public static function renderEntryConditionHtml(Node $node): string
    {
        if (!self::getSectionFromNode($node)) {
            return Html::tag('p', Craft::t('navigation', 'Select a section first to configure entry conditions.'), [
                'class' => ['light'],
            ]);
        }

        $entryCondition = self::getEntryCondition($node);
        $entryCondition->mainTag = 'div';
        $entryCondition->id = 'entry-condition';
        $entryCondition->name = 'entryCondition';
        $entryCondition->forProjectConfig = true;

        return Cp::fieldHtml($entryCondition->getBuilderHtml(), [
            'label' => Craft::t('navigation', 'Entry Conditions'),
            'instructions' => Craft::t('navigation', 'Only include entries that match the following rules:'),
        ]);
    }

    public static function renderSettingsHtml(Node $node): string
    {
        return self::renderBasicFieldsHtml($node) . self::renderEntryConditionHtml($node);
    }

    /**
     * Slideout editor fields: basic settings namespaced under `data`, entry condition at root.
     */
    public static function renderSlideoutHtml(Node $node): string
    {
        $view = Craft::$app->getView();

        return $view->namespaceInputs(
            fn() => self::renderBasicFieldsHtml($node),
            'data',
        ) . self::renderEntryConditionHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        $request = Craft::$app->getRequest();

        if (!$request instanceof \yii\web\Request || !$request->getIsPost()) {
            return;
        }

        $data = is_array($node->data) ? $node->data : [];

        if ($request->getBodyParam('entryCondition') !== null) {
            $conditionConfig = Component::cleanseConfig($request->getBodyParam('entryCondition'));

            if (empty($conditionConfig['conditionRules'])) {
                unset($data['entryCondition']);
            } else {
                $condition = Craft::$app->getConditions()->createCondition(array_merge(
                    ['class' => EntryCondition::class, 'elementType' => EntryElement::class],
                    $conditionConfig,
                ));
                $data['entryCondition'] = $condition->getConfig();
            }
        }

        $node->data = $data;
    }

    public static function applyToQuery(EntryQuery $query, Node $parent): void
    {
        $settings = is_array($parent->data) ? $parent->data : [];
        $section = self::getSectionFromNode($parent);

        if (!empty($settings['entryCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                ['class' => EntryCondition::class, 'elementType' => EntryElement::class],
                $settings['entryCondition'],
            ));
            $condition->modifyQuery($query);
        }

        self::applyOrderBy($query, $settings, $section);
    }

    public static function applyOrderBy(EntryQuery $query, array $settings, ?Section $section): void
    {
        $orderBy = $settings['orderBy'] ?? self::ORDER_DEFAULT;

        if ($orderBy === self::ORDER_DEFAULT) {
            if ($section?->type === Section::TYPE_STRUCTURE) {
                $query->orderBy('structure');
            } else {
                $query->orderBy('postDate desc');
            }

            return;
        }

        if ($orderBy === self::ORDER_STRUCTURE && $section?->type === Section::TYPE_STRUCTURE) {
            $query->orderBy('structure');

            return;
        }

        $query->orderBy($orderBy);
    }


    // Constants
    // =========================================================================

    public const ORDER_DEFAULT = 'default';
    public const ORDER_STRUCTURE = 'structure';
    public const ORDER_TITLE_ASC = 'title asc';
    public const ORDER_TITLE_DESC = 'title desc';
    public const ORDER_POST_DATE_ASC = 'postDate asc';
    public const ORDER_POST_DATE_DESC = 'postDate desc';
}
