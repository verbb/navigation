<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;

class Breadcrumbs extends Component
{
    // Public Methods
    // =========================================================================

    public function getBreadcrumbs(): array
    {
        $elements = [];
        $segments = Craft::$app->request->getSegments();

        $element = Craft::$app->elements->getElementByUri('__home__');

        if ($element) {
            $elements[] = $element;
        }

        if (count($segments)) {
            $count = 0;
            $segmentString = $segments[0];

            while ($count < count($segments)) {
                $element = Craft::$app->elements->getElementByUri($segmentString);

                if ($element) {
                    $elements[] = $element;
                }

                $count++;

                if (isset($segments[$count])) {
                    $segmentString .= '/' . $segments[$count];
                }
            }
        }

        return $elements;
    }
}