<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

use Craft;
use craft\commerce\elements\conditions\products\ProductCondition;
use craft\commerce\elements\Product as ProductElement;
use craft\commerce\models\ProductType;
use craft\commerce\Plugin as Commerce;
use craft\elements\db\ElementQuery;
use craft\helpers\Component;
use craft\helpers\Cp;
use craft\helpers\Html;

class ProductTypeSettings
{
    // Static Methods
    // =========================================================================

    public static function productTypeOptions(): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Select a product type'),
            'value' => '',
        ]];

        foreach (Commerce::getInstance()->getProductTypes()->getEditableProductTypes() as $productType) {
            $options[] = [
                'label' => $productType->name,
                'value' => (string)$productType->id,
            ];
        }

        return $options;
    }

    public static function orderByOptions(): array
    {
        return [
            ['label' => Craft::t('navigation', 'Default'), 'value' => self::ORDER_DEFAULT],
            ['label' => Craft::t('navigation', 'Title (ascending)'), 'value' => self::ORDER_TITLE_ASC],
            ['label' => Craft::t('navigation', 'Title (descending)'), 'value' => self::ORDER_TITLE_DESC],
            ['label' => Craft::t('navigation', 'Date created (newest first)'), 'value' => self::ORDER_DATE_CREATED_DESC],
            ['label' => Craft::t('navigation', 'Date created (oldest first)'), 'value' => self::ORDER_DATE_CREATED_ASC],
        ];
    }

    public static function getProductTypeFromNode(Node $node): ?ProductType
    {
        $productTypeId = (int)($node->data['productTypeId'] ?? 0);

        if (!$productTypeId) {
            return null;
        }

        return Commerce::getInstance()->getProductTypes()->getProductTypeById($productTypeId);
    }

    public static function getProductCondition(Node $node): ProductCondition
    {
        $productType = self::getProductTypeFromNode($node);

        if (!empty($node->data['productCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                ['class' => ProductCondition::class, 'elementType' => ProductElement::class],
                $node->data['productCondition'],
            ));
        } else {
            $condition = ProductElement::createCondition();
        }

        if ($productType) {
            $condition->sourceKey = 'productType:' . $productType->uid;
        }

        return $condition;
    }

    public static function renderBasicFieldsHtml(Node $node): string
    {
        $html = Cp::selectFieldHtml([
            'label' => Craft::t('commerce', 'Product Type'),
            'instructions' => Craft::t('navigation', 'Choose a product type. Its products will appear as children when the menu is rendered.'),
            'id' => 'productTypeId',
            'name' => 'productTypeId',
            'value' => $node->data['productTypeId'] ?? '',
            'options' => self::productTypeOptions(),
            'required' => true,
        ]);

        $html .= Cp::selectFieldHtml([
            'label' => Craft::t('navigation', 'Sort Order'),
            'instructions' => Craft::t('navigation', 'How projected products should be ordered under this node.'),
            'id' => 'orderBy',
            'name' => 'orderBy',
            'value' => $node->data['orderBy'] ?? self::ORDER_DEFAULT,
            'options' => self::orderByOptions(),
        ]);

        $html .= DynamicProjectionSettings::limitFieldHtml($node);

        return $html;
    }

    public static function renderProductConditionHtml(Node $node): string
    {
        if (!self::getProductTypeFromNode($node)) {
            return Html::tag('p', Craft::t('navigation', 'Select a product type first to configure product conditions.'), [
                'class' => ['light'],
            ]);
        }

        $productCondition = self::getProductCondition($node);
        $productCondition->mainTag = 'div';
        $productCondition->id = 'product-condition';
        $productCondition->name = 'productCondition';
        $productCondition->forProjectConfig = true;

        return Cp::fieldHtml($productCondition->getBuilderHtml(), [
            'label' => Craft::t('navigation', 'Product Conditions'),
            'instructions' => Craft::t('navigation', 'Only include products that match the following rules:'),
        ]);
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        $view = Craft::$app->getView();

        return $view->namespaceInputs(
            fn() => self::renderBasicFieldsHtml($node),
            'data',
        ) . self::renderProductConditionHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        $request = Craft::$app->getRequest();

        if (!$request instanceof \yii\web\Request || !$request->getIsPost()) {
            return;
        }

        $data = is_array($node->data) ? $node->data : [];

        if ($request->getBodyParam('productCondition') !== null) {
            $conditionConfig = Component::cleanseConfig($request->getBodyParam('productCondition'));

            if (empty($conditionConfig['conditionRules'])) {
                unset($data['productCondition']);
            } else {
                $condition = Craft::$app->getConditions()->createCondition(array_merge(
                    ['class' => ProductCondition::class, 'elementType' => ProductElement::class],
                    $conditionConfig,
                ));
                $data['productCondition'] = $condition->getConfig();
            }
        }

        $node->data = $data;
    }

    public static function applyToQuery(ElementQuery $query, Node $parent): void
    {
        $settings = is_array($parent->data) ? $parent->data : [];

        if (!empty($settings['productCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                ['class' => ProductCondition::class, 'elementType' => ProductElement::class],
                $settings['productCondition'],
            ));
            $condition->modifyQuery($query);
        }

        self::applyOrderBy($query, $settings);
        DynamicProjectionSettings::applyLimit($query, $settings);
    }

    public static function applyOrderBy(ElementQuery $query, array $settings): void
    {
        $orderBy = $settings['orderBy'] ?? self::ORDER_DEFAULT;

        if ($orderBy === self::ORDER_DEFAULT) {
            $query->orderBy('title asc');

            return;
        }

        $query->orderBy($orderBy);
    }


    // Constants
    // =========================================================================

    public const ORDER_DEFAULT = 'default';
    public const ORDER_TITLE_ASC = 'title asc';
    public const ORDER_TITLE_DESC = 'title desc';
    public const ORDER_DATE_CREATED_DESC = 'dateCreated desc';
    public const ORDER_DATE_CREATED_ASC = 'dateCreated asc';
}
