<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;

use verbb\navigation\Navigation;
use verbb\navigation\events\RegisterElementEvent;

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

    public function getRegisteredElements($includeSources = true): array
    {
        // Add default element support
        $elements = [
            [
                'label' => Craft::t('site', Entry::pluralDisplayName()),
                'button' => Craft::t('navigation', 'Add an Entry'),
                'type' => Entry::class,
                'sources' => [],
                'default' => true,
                'color' => '#5e5378',
            ],
            [
                'label' => Craft::t('site', Category::pluralDisplayName()),
                'button' => Craft::t('navigation', 'Add a Category'),
                'type' => Category::class,
                'sources' => [],
                'default' => true,
                'color' => '#1BB311',
            ],
            [
                'label' => Craft::t('site', Asset::pluralDisplayName()),
                'button' => Craft::t('navigation', 'Add an Asset'),
                'type' => Asset::class,
                'sources' => [],
                'default' => true,
                'color' => '#e12d39',
            ],
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce') && class_exists(Product::class)) {
            $elements[] = [
                'label' => Craft::t('site', Product::pluralDisplayName()),
                'button' => Craft::t('navigation', 'Add a Product'),
                'type' => Product::class,
                'sources' => [],
                'default' => true,
            ];
        }

        // Add all other elements that support URIs
        $addedElementTypes = ArrayHelper::getColumn($elements, 'type');

        foreach (Craft::$app->getElements()->getAllElementTypes() as $elementType) {
            if ($elementType::hasUris() && !in_array($elementType, $addedElementTypes)) {
                $elements[] = [
                    'label' => Craft::t('site', $elementType::pluralDisplayName()),
                    'button' => Craft::t('navigation', 'Add a {name}', ['name' => $elementType::displayName()]),
                    'type' => $elementType,
                    'sources' => [],
                ];
            }
        }

        $event = new RegisterElementEvent([
            'elements' => $elements,
        ]);

        $this->trigger(self::EVENT_REGISTER_NAVIGATION_ELEMENT, $event);

        $elementIndexes = Craft::$app->getElementSources();

        // For performance, only include element sources if we require them. They also do unexpected things
        // as they're element indexes (like for assets, creating user upload directories)
        if ($includeSources) {
            foreach ($event->elements as $key => $element) {
                $event->elements[$key]['sources'] = $elementIndexes->getSources($element['type'], 'modal');
            }
        }

        return $event->elements;
    }

}