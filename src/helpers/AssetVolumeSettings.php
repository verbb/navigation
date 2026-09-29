<?php
namespace verbb\navigation\helpers;

use verbb\navigation\elements\Node;

use Craft;
use craft\elements\Asset as AssetElement;
use craft\elements\conditions\assets\AssetCondition;
use craft\elements\db\AssetQuery;
use craft\helpers\Component;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\models\Volume;

class AssetVolumeSettings
{
    // Static Methods
    // =========================================================================

    public static function volumeOptions(): array
    {
        $options = [[
            'label' => Craft::t('navigation', 'Select a volume'),
            'value' => '',
        ]];

        foreach (Craft::$app->getVolumes()->getViewableVolumes() as $volume) {
            $options[] = [
                'label' => $volume->name,
                'value' => (string)$volume->id,
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
            ['label' => Craft::t('navigation', 'Date modified (newest first)'), 'value' => self::ORDER_DATE_MODIFIED_DESC],
            ['label' => Craft::t('navigation', 'Date modified (oldest first)'), 'value' => self::ORDER_DATE_MODIFIED_ASC],
        ];
    }

    public static function getVolumeFromNode(Node $node): ?Volume
    {
        $volumeId = (int)($node->data['volumeId'] ?? 0);

        if (!$volumeId) {
            return null;
        }

        return Craft::$app->getVolumes()->getVolumeById($volumeId);
    }

    public static function getAssetCondition(Node $node): AssetCondition
    {
        $volume = self::getVolumeFromNode($node);

        if (!empty($node->data['assetCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                $node->data['assetCondition'],
                ['class' => AssetCondition::class, 'elementType' => AssetElement::class],
            ));
        } else {
            $condition = AssetElement::createCondition();
        }

        if ($volume) {
            $condition->sourceKey = 'volume:' . $volume->uid;
        }

        return $condition;
    }

    public static function renderBasicFieldsHtml(Node $node): string
    {
        $html = Cp::selectFieldHtml([
            'label' => Craft::t('app', 'Volume'),
            'instructions' => Craft::t('navigation', 'Choose an asset volume. Its assets will appear as children when the menu is rendered.'),
            'id' => 'volumeId',
            'name' => 'volumeId',
            'value' => $node->data['volumeId'] ?? '',
            'options' => self::volumeOptions(),
            'required' => true,
        ]);

        $html .= Cp::selectFieldHtml([
            'label' => Craft::t('navigation', 'Sort Order'),
            'instructions' => Craft::t('navigation', 'How projected assets should be ordered under this node.'),
            'id' => 'orderBy',
            'name' => 'orderBy',
            'value' => $node->data['orderBy'] ?? self::ORDER_DEFAULT,
            'options' => self::orderByOptions(),
        ]);

        $html .= DynamicProjectionSettings::limitFieldHtml($node);

        return $html;
    }

    public static function renderAssetConditionHtml(Node $node): string
    {
        if (!self::getVolumeFromNode($node)) {
            return Html::tag('p', Craft::t('navigation', 'Select a volume first to configure asset conditions.'), [
                'class' => ['light'],
            ]);
        }

        $assetCondition = self::getAssetCondition($node);
        $assetCondition->mainTag = 'div';
        $assetCondition->id = 'asset-condition';
        $assetCondition->name = 'assetCondition';
        $assetCondition->forProjectConfig = true;

        return Cp::fieldHtml($assetCondition->getBuilderHtml(), [
            'label' => Craft::t('navigation', 'Asset Conditions'),
            'instructions' => Craft::t('navigation', 'Only include assets that match the following rules:'),
        ]);
    }

    public static function renderSlideoutHtml(Node $node): string
    {
        $view = Craft::$app->getView();

        return $view->namespaceInputs(
            fn() => self::renderBasicFieldsHtml($node),
            'data',
        ) . self::renderAssetConditionHtml($node);
    }

    public static function applyPostData(Node $node): void
    {
        $request = Craft::$app->getRequest();
        $data = is_array($node->data) ? $node->data : [];
        $data['orderBy'] = self::normalizeOrderBy($data['orderBy'] ?? self::ORDER_DEFAULT);

        if ($request instanceof \yii\web\Request && $request->getIsPost() && $request->getBodyParam('assetCondition') !== null) {
            $conditionConfig = Component::cleanseConfig($request->getBodyParam('assetCondition'));

            if (empty($conditionConfig['conditionRules'])) {
                unset($data['assetCondition']);
            } else {
                $condition = Craft::$app->getConditions()->createCondition(array_merge(
                    $conditionConfig,
                    ['class' => AssetCondition::class, 'elementType' => AssetElement::class],
                ));
                $data['assetCondition'] = $condition->getConfig();
            }
        }

        $node->data = $data;
    }

    public static function applyToQuery(AssetQuery $query, Node $parent): void
    {
        $settings = is_array($parent->data) ? $parent->data : [];

        if (!empty($settings['assetCondition'])) {
            $condition = Craft::$app->getConditions()->createCondition(array_merge(
                $settings['assetCondition'],
                ['class' => AssetCondition::class, 'elementType' => AssetElement::class],
            ));
            $condition->modifyQuery($query);
        }

        self::applyOrderBy($query, $settings);
        DynamicProjectionSettings::applyLimit($query, $settings);
    }

    public static function applyOrderBy(AssetQuery $query, array $settings): void
    {
        $orderBy = self::normalizeOrderBy($settings['orderBy'] ?? self::ORDER_DEFAULT);

        if ($orderBy === self::ORDER_DEFAULT) {
            $query->orderBy(['title' => SORT_ASC]);

            return;
        }

        $query->orderBy(match ($orderBy) {
            self::ORDER_TITLE_DESC => ['title' => SORT_DESC],
            self::ORDER_DATE_MODIFIED_ASC => ['dateModified' => SORT_ASC],
            self::ORDER_DATE_MODIFIED_DESC => ['dateModified' => SORT_DESC],
            default => ['title' => SORT_ASC],
        });
    }

    public static function normalizeOrderBy(mixed $orderBy): string
    {
        return is_string($orderBy) && in_array($orderBy, [
            self::ORDER_DEFAULT,
            self::ORDER_TITLE_ASC,
            self::ORDER_TITLE_DESC,
            self::ORDER_DATE_MODIFIED_ASC,
            self::ORDER_DATE_MODIFIED_DESC,
        ], true) ? $orderBy : self::ORDER_DEFAULT;
    }


    // Constants
    // =========================================================================

    public const ORDER_DEFAULT = 'default';
    public const ORDER_TITLE_ASC = 'title asc';
    public const ORDER_TITLE_DESC = 'title desc';
    public const ORDER_DATE_MODIFIED_DESC = 'dateModified desc';
    public const ORDER_DATE_MODIFIED_ASC = 'dateModified asc';
}
