<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;

use verbb\navigation\Navigation;
use verbb\navigation\events\RegisterElementEvent;

use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\Category;
use craft\commerce\elements\Product;

class Elements extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_NAVIGATION_ELEMENT = 'registerNavigationElement';


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->getRegisteredElements();
    }

    public function getRegisteredElements()
    {
        // Add default element support
        $elements = [
            'entries' => [
                'label' => Craft::t('navigation', 'Entries'),
                'button' => Craft::t('navigation', 'Add an entry'),
                'type' => Entry::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Entry::class, 'modal'),
                'default' => true,
            ],
            'categories' => [
                'label' => Craft::t('navigation', 'Categories'),
                'button' => Craft::t('navigation', 'Add a category'),
                'type' => Category::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Category::class, 'modal'),
                'default' => true,
            ],
            'assets' => [
                'label' => Craft::t('navigation', 'Assets'),
                'button' => Craft::t('navigation', 'Add an asset'),
                'type' => Asset::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Asset::class, 'modal'),
                'default' => true,
            ],
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce')) {
            $elements['products'] = [
                'label' => Craft::t('navigation', 'Products'),
                'button' => Craft::t('navigation', 'Add a product'),
                'type' => Product::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Product::class, 'modal'),
                'default' => true,
            ];
        }

        // Add all other elements that suport URIs
        foreach (Craft::$app->getElements()->getAllElementTypes() as $elementType) {
            if ($elementType::hasUris() && !isset($elements[$elementType::pluralLowerDisplayName()])) {
                $elements[$elementType::pluralLowerDisplayName()] = [
                    'label' => Craft::t('navigation', $elementType::pluralDisplayName()),
                    'button' => Craft::t('navigation', 'Add a {name}', ['name' => $elementType::lowerDisplayName()]),
                    'type' => $elementType,
                    'sources' => Craft::$app->getElementIndexes()->getSources($elementType, 'modal'),
                ];
            }
        }

        // Remove any defined in our config
        $settings = Navigation::$plugin->getSettings();

        $event = new RegisterElementEvent([
            'elements' => $elements,
        ]);

        $this->trigger(self::EVENT_REGISTER_NAVIGATION_ELEMENT, $event);

        return $event->elements;
    }

}