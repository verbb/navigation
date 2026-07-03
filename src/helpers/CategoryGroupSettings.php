<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

use Craft;
use craft\elements\Category as CategoryElement;
use craft\elements\conditions\categories\CategoryCondition;
use craft\elements\db\CategoryQuery;
use craft\helpers\Component;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\models\CategoryGroup;

class CategoryGroupSettings
{
    // Static Methods
    // =========================================================================

    public static function groupOptions(): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Select a category group'),
            'value' => '',
        ]];

        foreach (Craft::$app->getCategories()->getEditableGroups() as $group) {
            $options[] = [
                'label' => $group->name,
                'value' => (string)$group->id,
            ];
        }

        return $options;
    }

    public static function orderByOptions(?CategoryGroup $group = null): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Default'),
            'value' => self::ORDER_DEFAULT,
        ]];

        if ($group && (int)$group->maxLevels !== 1) {
            $options[] = [
                'label' => Craft::t('navigation', 'Structure order'),
                'value' => self::ORDER_STRUCTURE,
            ];
        }

        return array_merge($options, [
            ['label' => Craft::t('navigation', 'Title (ascending)'), 'value' => self::ORDER_TITLE_ASC],
            ['label' => Craft::t('navigation', 'Title (descending)'), 'value' => self::ORDER_TITLE_DESC],
            ['label' => Craft::t('navigation', 'Date created (newest first)'), 'value' => self::ORDER_DATE_CREATED_DESC],
            ['label' => Craft::t('navigation', 'Date created (oldest first)'), 'value' => self::ORDER_DATE_CREATED_ASC],
        ]);
    }

    public static function getGroupFromNode(Node $node): ?CategoryGroup
    {
        $groupId = (int)($node->data['groupId'] ?? 0);

        if (!$groupId) {
            return null;
        }

        return Craft::$app->getCategories()->getGroupById($groupId);
    }

    public static function getCategoryCondition(Node $node): CategoryCondition
    {
        $group = self::getGroupFromNode($node);

        if (!empty($node->data['categoryCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                ['class' => CategoryCondition::class, 'elementType' => CategoryElement::class],
                $node->data['categoryCondition'],
            ));
        } else {
            $condition = CategoryElement::createCondition();
        }

        if ($group) {
            $condition->sourceKey = 'group:' . $group->uid;
        }

        return $condition;
    }

    public static function renderBasicFieldsHtml(Node $node): string
    {
        $group = self::getGroupFromNode($node);

        $html = Cp::selectFieldHtml([
            'label' => Craft::t('navigation', 'Category Group'),
            'instructions' => Craft::t('navigation', 'Choose a category group. Its categories will appear as children when the menu is rendered.'),
            'id' => 'groupId',
            'name' => 'groupId',
            'value' => $node->data['groupId'] ?? '',
            'options' => self::groupOptions(),
            'required' => true,
        ]);

        $html .= Cp::selectFieldHtml([
            'label' => Craft::t('navigation', 'Sort Order'),
            'instructions' => Craft::t('navigation', 'How projected categories should be ordered under this node.'),
            'id' => 'orderBy',
            'name' => 'orderBy',
            'value' => $node->data['orderBy'] ?? self::ORDER_DEFAULT,
            'options' => self::orderByOptions($group),
        ]);

        return $html;
    }

    public static function renderCategoryConditionHtml(Node $node): string
    {
        if (!self::getGroupFromNode($node)) {
            return Html::tag('p', Craft::t('navigation', 'Select a category group first to configure category conditions.'), [
                'class' => ['light'],
            ]);
        }

        $categoryCondition = self::getCategoryCondition($node);
        $categoryCondition->mainTag = 'div';
        $categoryCondition->id = 'category-condition';
        $categoryCondition->name = 'categoryCondition';
        $categoryCondition->forProjectConfig = true;

        return Cp::fieldHtml($categoryCondition->getBuilderHtml(), [
            'label' => Craft::t('navigation', 'Category Conditions'),
            'instructions' => Craft::t('navigation', 'Only include categories that match the following rules:'),
        ]);
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        $view = Craft::$app->getView();

        return $view->namespaceInputs(
            fn() => self::renderBasicFieldsHtml($node),
            'data',
        ) . self::renderCategoryConditionHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        $request = Craft::$app->getRequest();

        if (!$request instanceof \yii\web\Request || !$request->getIsPost()) {
            return;
        }

        $data = is_array($node->data) ? $node->data : [];

        if ($request->getBodyParam('categoryCondition') !== null) {
            $conditionConfig = Component::cleanseConfig($request->getBodyParam('categoryCondition'));

            if (empty($conditionConfig['conditionRules'])) {
                unset($data['categoryCondition']);
            } else {
                $condition = Craft::$app->getConditions()->createCondition(array_merge(
                    ['class' => CategoryCondition::class, 'elementType' => CategoryElement::class],
                    $conditionConfig,
                ));
                $data['categoryCondition'] = $condition->getConfig();
            }
        }

        $node->data = $data;
    }

    public static function applyToQuery(CategoryQuery $query, Node $parent): void
    {
        $settings = is_array($parent->data) ? $parent->data : [];
        $group = self::getGroupFromNode($parent);

        if (!empty($settings['categoryCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                ['class' => CategoryCondition::class, 'elementType' => CategoryElement::class],
                $settings['categoryCondition'],
            ));
            $condition->modifyQuery($query);
        }

        self::applyOrderBy($query, $settings, $group);
    }

    public static function applyOrderBy(CategoryQuery $query, array $settings, ?CategoryGroup $group): void
    {
        $orderBy = $settings['orderBy'] ?? self::ORDER_DEFAULT;

        if ($orderBy === self::ORDER_DEFAULT) {
            if ($group && (int)$group->maxLevels !== 1) {
                $query->orderBy(['lft' => SORT_ASC]);
            } else {
                $query->orderBy('title asc');
            }

            return;
        }

        if ($orderBy === self::ORDER_STRUCTURE && $group && (int)$group->maxLevels !== 1) {
            $query->orderBy(['lft' => SORT_ASC]);

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
    public const ORDER_DATE_CREATED_ASC = 'dateCreated asc';
    public const ORDER_DATE_CREATED_DESC = 'dateCreated desc';
}
