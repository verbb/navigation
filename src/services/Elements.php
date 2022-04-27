<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;

use verbb\navigation\Navigation;
use verbb\navigation\events\RegisterElementEvent;
use verbb\navigation\models\Settings;

use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\Category;
use craft\helpers\ArrayHelper;

use craft\commerce\elements\Product;

class Elements extends Component
{
    // Constants
    // =========================================================================

    public const EVENT_REGISTER_NAVIGATION_ELEMENT = 'registerNavigationElement';


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        $this->getRegisteredElements();
    }

    public function getRegisteredElements(): array
    {
        // Add default element support
        $elements = [
            [
                'label' => Craft::t('site', Entry::pluralDisplayName()),
                'button' => Craft::t('site', 'Add an Entry'),
                'type' => Entry::class,
                'sources' => Craft::$app->getElementSources()->getSources(Entry::class, 'modal'),
                'default' => true,
            ],
            [
                'label' => Craft::t('site', Category::pluralDisplayName()),
                'button' => Craft::t('site', 'Add a Category'),
                'type' => Category::class,
                'sources' => Craft::$app->getElementSources()->getSources(Category::class, 'modal'),
                'default' => true,
            ],
            [
                'label' => Craft::t('site', Asset::pluralDisplayName()),
                'button' => Craft::t('site', 'Add an Asset'),
                'type' => Asset::class,
                'sources' => Craft::$app->getElementSources()->getSources(Asset::class, 'modal'),
                'default' => true,
            ],
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce') && class_exists(Product::class)) {
            $elements[] = [
                'label' => Craft::t('site', Product::pluralDisplayName()),
                'button' => Craft::t('site', 'Add a Product'),
                'type' => Product::class,
                'sources' => Craft::$app->getElementSources()->getSources(Product::class, 'modal'),
                'default' => true,
            ];
        }

        // Add all other elements that support URIs
        $addedElementTypes = ArrayHelper::getColumn($elements, 'type');

        foreach (Craft::$app->getElements()->getAllElementTypes() as $elementType) {
            if ($elementType::hasUris() && !in_array($elementType, $addedElementTypes)) {
                $elements[] = [
                    'label' => Craft::t('site', $elementType::pluralDisplayName()),
                    'button' => Craft::t('site', 'Add a {name}', ['name' => $elementType::displayName()]),
                    'type' => $elementType,
                    'sources' => Craft::$app->getElementSources()->getSources($elementType, 'modal'),
                ];
            }
        }

        $event = new RegisterElementEvent([
            'elements' => $elements,
        ]);

        $this->trigger(self::EVENT_REGISTER_NAVIGATION_ELEMENT, $event);

        return $event->elements;
    }

}