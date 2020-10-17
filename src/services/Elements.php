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
        $elements = [
            'entries' => [
                'label' => Craft::t('navigation', 'Entries'),
                'button' => Craft::t('navigation', 'Add an entry'),
                'type' => Entry::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Entry::class, 'modal'),
            ],
            'categories' => [
                'label' => Craft::t('navigation', 'Categories'),
                'button' => Craft::t('navigation', 'Add a category'),
                'type' => Category::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Category::class, 'modal'),
            ],
            'assets' => [
                'label' => Craft::t('navigation', 'Assets'),
                'button' => Craft::t('navigation', 'Add an asset'),
                'type' => Asset::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Asset::class, 'modal'),
            ],
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce')) {
            $elements['product'] = [
                'label' => Craft::t('navigation', 'Products'),
                'button' => Craft::t('navigation', 'Add a product'),
                'type' => Product::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Product::class, 'modal'),
            ];
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