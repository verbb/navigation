<?php
namespace verbb\navigation\services;

use verbb\navigation\Navigation;
use verbb\navigation\events\RegisterElementEvent;

use Craft;
use craft\base\Component;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\ArrayHelper;

use craft\commerce\elements\Product;

/**
 * @deprecated in 4.0.0. Use {@see NodeTypes} and {@see RegisterNodeTypeEvent} instead.
 */
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
                'button' => Craft::t('navigation', 'Add {name}', ['name' => mb_strtolower(Craft::t('site', Entry::pluralDisplayName()))]),
                'type' => Entry::class,
                'sources' => [],
                'default' => true,
                'color' => '#5e5378',
            ],
            [
                'label' => Craft::t('site', Category::pluralDisplayName()),
                'button' => Craft::t('navigation', 'Add {name}', ['name' => mb_strtolower(Craft::t('site', Category::pluralDisplayName()))]),
                'type' => Category::class,
                'sources' => [],
                'default' => true,
                'color' => '#1BB311',
            ],
            [
                'label' => Craft::t('site', Asset::pluralDisplayName()),
                'button' => Craft::t('navigation', 'Add {name}', ['name' => mb_strtolower(Craft::t('site', Asset::pluralDisplayName()))]),
                'type' => Asset::class,
                'sources' => [],
                'default' => true,
                'color' => '#e12d39',
            ],
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce') && class_exists(Product::class)) {
            $elements[] = [
                'label' => Craft::t('site', Product::pluralDisplayName()),
                'button' => Craft::t('navigation', 'Add {name}', ['name' => mb_strtolower(Craft::t('site', Product::pluralDisplayName()))]),
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
                    'button' => Craft::t('navigation', 'Add {name}', [
                        'name' => mb_strtolower($elementType::pluralDisplayName()),
                    ]),
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
